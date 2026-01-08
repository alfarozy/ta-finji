<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BankAccountController extends Controller
{
    public function index()
    {
        $bankAccount = BankAccount::where(['user_id' => auth()->id()])->first();
        return view('backoffice.bank.index', compact('bankAccount'));
    }

    public function create()
    {
        $bankAccount = BankAccount::where(['user_id' => auth()->id()])->first();
        if ($bankAccount) {
            return back()->with('error', 'Akun bank sudah terhubung');
        }
        return view('backoffice.bank.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'bank_name'      => 'required|in:bca,bni,bri',
            'username'       => 'required|string',
            'password'       => 'required|string',
            'account_name'    => 'required|string',
            'account_number' => 'required|numeric',
        ]);

        // Request ke MOOTA
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.moota.api_key'),
            'Accept'        => 'application/json',
        ])->post(
            config('services.moota.base_url') . '/api/v2/bank/store',
            [
                'corporate_id'   => '',
                'bank_type'      => $request->bank_name,
                'username'       => $request->username,
                'password'       => $request->password,
                'name_holder'    => $request->account_name,
                'account_number' => $request->account_number,
                'is_active'      => true,
            ]
        );

        if (! $response->successful()) {
            return redirect()->route('bank-account.index')->with('error', 'Gagal membuat akun bank');
        }

        $data = $response->json();

        // Ambil data penting dari response Moota
        $bank = $data['bank'];

        // Simpan ke database lokal
        $bankAccount = BankAccount::create([
            'user_id'        => auth()->id(),
            'bank_name'      => strtoupper($bank['bank_type']), // BCA
            'moota_bank_id'  => $bank['bank_id'],                // token / bank_id
            'account_number' => $bank['account_number'],
            'account_name'   => $bank['atas_nama'],
        ]);

        return redirect()->route('bank-account.index')->with('success', 'Mutasi bank berhasil disambungkan');
    }

    public function edit($id)
    {
        $bankAccount = BankAccount::where(['user_id' => auth()->id()])->first();
        if (!$bankAccount) {
            return redirect()->route('bank-account.index')->with('error', 'Silahkan tambahkan bank terlebih dahulu');
        }
        return view('backoffice.bank.edit', compact('bankAccount'));
    }

    public function update(Request $request, $id)
    {

        $request->validate([
            'bank_name'      => 'required|in:bca,bni,bri',
            'username'       => 'required|string',
            'password'       => 'required|string',
            'account_name'   => 'required|string',
            'account_number' => 'required|numeric',
        ]);

        // Ambil akun bank lokal
        $bankAccount = BankAccount::where(['user_id' => auth()->id()])->firstOrFail();

        $bankId = $bankAccount->moota_bank_id;

        // Request UPDATE ke MOOTA
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.moota.api_key'),
            'Accept'        => 'application/json',
        ])->post(
            config('services.moota.base_url') . "/api/v2/bank/update/{$bankId}",
            [
                'bank_type'      => $request->bank_name,
                'username'       => $request->username,
                'password'       => $request->password,
                'name_holder'    => $request->account_name,
                'account_number' => $request->account_number,
                'is_active'      => true,
            ]
        );

        if (! $response->successful()) {
            return redirect()
                ->route('bank-account.index')
                ->with('error', 'Gagal memperbarui akun bank di Moota');
        }

        $data = $response->json();
        $bank = $data['bank'];

        // Update database lokal
        $bankAccount->update([
            'bank_name'      => strtoupper($bank['bank_type']),
            'account_number' => $bank['account_number'],
            'account_name'   => $bank['atas_nama'],
        ]);

        return redirect()
            ->route('bank-account.index')
            ->with('success', 'Akun bank berhasil diperbarui');
    }

    public function destroy($id)
    {
        // Ambil akun bank milik user
        $bankAccount = BankAccount::where(['user_id' => auth()->id()])->first();
        if (!$bankAccount) {
            return redirect()->route('bank-account.index')->with('error', 'Silahkan tambahkan bank terlebih dahulu');
        }

        $bankId = $bankAccount->moota_bank_id;

        // Request DELETE ke MOOTA
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.moota.api_key'),
            'Accept'        => 'application/json',
        ])->post(
            config('services.moota.base_url') . "/api/v2/bank/{$bankId}/destroy"
        );

        if (! $response->successful()) {
            return redirect()
                ->route('bank-account.index')
                ->with('error', 'Gagal menghapus akun bank di Moota');
        }

        // Hapus dari database lokal
        $bankAccount->delete();

        return redirect()
            ->route('bank-account.index')
            ->with('success', 'Akun bank berhasil dihapus');
    }

    public function syncTransactions()
    {

        $bankAccount = BankAccount::where('user_id', auth()->id())
            ->firstOrFail();

        // 1. Ambil mutasi dari MOOTA
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.moota.api_key'),
            'Accept'        => 'application/json',
        ])->get(
            config('services.moota.base_url') . '/api/v2/mutation',
            [
                'bank'     => $bankAccount->moota_bank_id,
                'page'     => 1,
                'per_page' => 10,
            ]
        );

        if (! $response->successful()) {
            return redirect()->back()->with('error', 'Gagal mengambil mutasi dari Moota');
        }

        $mutations = $response->json('data');

        if (empty($mutations)) {
            return redirect()->back()->with('error', 'Mutasi bank kosong');
        }

        /**
         * 2. NORMALIZE (MINIMAL PAYLOAD)
         */
        $normalized = collect($mutations)->map(fn($m) => [
            'external_id' => $m['mutation_id'],
            'date'        => Carbon::parse($m['date'])->toDateString(),
            'description' => $m['description'],
            'amount'      => (float) $m['amount'],
            'type'        => $m['type'] === 'CR'
                ? Transaction::TYPE_INCOME
                : Transaction::TYPE_EXPENSE,
        ])->keyBy('external_id');

        /**
         * 3. FILTER HANYA YANG BELUM ADA DI DB
         */
        $newTransactions = $normalized->reject(function ($trx, $externalId) {
            return Transaction::where('external_id', $externalId)->exists();
        });

        if ($newTransactions->isEmpty()) {
            return redirect()->back()->with('success', 'Belum ada mutasi baru');
        }

        /**
         * 4. KIRIM KE AI (BATCH)
         */
        $parsedResults = $this->parsingTransaction(
            $newTransactions->values()->toJson()
        );

        /**
         * 5. INSERT KE DATABASE
         */
        foreach ($parsedResults as $parsed) {

            // safety check
            if (!isset($parsed['external_id'], $parsed['category'])) {
                continue;
            }

            $original = $newTransactions[$parsed['external_id']] ?? null;
            if (! $original) continue;

            Transaction::create([
                'user_id'                 => auth()->id(),
                'external_id'             => $parsed['external_id'],
                'transaction_category_id' => $this->mapCategory(
                    $parsed['category'],
                    $original['type']
                ),
                'amount'           => (int) round($original['amount']),
                'description'      => $parsed['description'],
                'type'             => $original['type'],
                'transaction_date' => $original['date'],
                'source'           => Transaction::SOURCE_MUTATION,
            ]);
        }

        $bankAccount->update([
            'last_synced_at' => now(),
        ]);
        return redirect()->back()->with('success', 'Mutasi berhasil disinkronkan');
    }
    public function parsingTransaction(string $transactions)
    {
        $categoryName = TransactionCategory::pluck('name')->toArray();
        $categoryList = implode(', ', $categoryName);

        $prompt = <<<EOT
Kamu adalah sistem klasifikasi transaksi keuangan berbasis mutasi bank di Indonesia.

TUGAS:
- Tentukan kategori transaksi dan deskripsi yang PALING NETRAL dan AKURAT.
- Gunakan kategori berikut SAJA:
[$categoryList]

ATURAN WAJIB:
- JANGAN mengubah nilai "external_id".
- JANGAN menambah atau mengurangi field apa pun.
- Kategori HARUS salah satu dari daftar yang diberikan.
- Deskripsi WAJIB berupa ringkasan faktual (bukan asumsi tujuan).
- JANGAN menebak tujuan transaksi (misalnya makan, belanja) jika tidak eksplisit.
- Jika mutasi bank bersifat umum atau ambigu, gunakan kategori "Transfer".
- Jika sangat tidak yakin, gunakan kategori "Lainnya".
- JANGAN menjelaskan apa pun.
- Output HARUS berupa JSON array VALID.

PANDUAN DESKRIPSI:
- Gunakan format netral, contoh:
  - "Transfer E-Banking ke [Nama]"
  - "Mutasi bank masuk"
- Jangan gunakan kata yang bersifat asumtif seperti "makan", "belanja", "hiburan" kecuali eksplisit di teks.

FORMAT OUTPUT (WAJIB JSON TANPA TEKS TAMBAHAN):
[
  {
    "external_id": "string",
    "category": "string",
    "description": "string"
  }
]

INPUT DATA TRANSAKSI:
$transactions
EOT;

        // API call remains the same
        $apiKey = config('services.google.gemini_api_key');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

        $response = Http::post($url, [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ]
        ]);

        $data = $response->json();

        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!$text) {
            throw new \Exception('No response from AI');
        }

        // Clean and parse JSON
        $clean = preg_replace('/```(json)?|```/', '', $text);
        $clean = trim($clean);

        $parsed = json_decode($clean, true, 512, JSON_THROW_ON_ERROR);
        \Log::info($parsed);
        return $parsed;
    }



    public function mapCategory($categoryName, $type)
    {
        $slug = str()->slug($categoryName);

        // Implement your category mapping logic here
        return TransactionCategory::firstOrCreate(['name' => str()->title($categoryName), 'slug' => $slug, 'type' => $type])->id;
    }
}
