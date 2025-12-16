<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseJson;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\User;
use App\Models\UserBalance;
use App\Services\WhatsappService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class WhatsAppChatbotController extends Controller
{

    public function webhookHandle(Request $request)
    {

        try {
            // Validasi payload webhook baru
            $validated = $request->validate([
                'event' => 'required|string',
                'timestamp' => 'required',
                'data' => 'required|array',
            ]);
        } catch (ValidationException $e) {
            Log::error('Webhook validation error: ' . $e->getMessage());
            return ResponseJson::failedResponse('Payload tidak valid', []);
        }

        $event = $validated['event'];
        $data  = $validated['data'];


        // Hanya proses event message.received
        if ($event !== 'message.received') {
            return ResponseJson::successResponse('Event diterima', []);
        }

        // Normalisasi ke bentuk yang dikenali FinjiService
        $normalized = [
            'from'         => $data['from'] ?? null,
            'message'      => $data['message']['text'] ?? null,
            'message_type' => $data['messageType'] ?? null,
            'message_id'   => $data['messageId'] ?? null,
            'contact_name' => $data['contact']['name'] ?? null,
        ];


        [$proceed, $message, $data] = $this->processMessage($normalized);

        return [
            'success' => false,
            'message' => 'Nomor atau pesan tidak valid.',
            'data' => $result ?? []
        ];
        if (!$proceed) {
            Log::error('Validation problem: ' . $message);
            return ResponseJson::failedResponse($message, $data);
        }
        return ResponseJson::successResponse($message, $data);
    }

    public function processMessage($payload)
    {
        // Validate input
        $from = $payload['from'] ?? null;
        $rawMessage = isset($payload['message']) ? trim($payload['message']) : '';
        $whatsappId = explode('@', $from)[0];

        // Validasi dasar
        if (empty($from) || empty($rawMessage)) {
            Log::warning('Payload tidak valid: nomor atau pesan kosong', [
                'from' => $from,
                'message' => $rawMessage,
            ]);

            return [false, 'Nomor atau pesan tidak valid.', []];
        }

        //> check user whatsapp
        $user = User::where('whatsapp', $whatsappId)->first();

        if (!$user) {
            $link = "https://finji-dev.my.id";
            $message = "Hai, sepertinya nomor WhatsApp ini belum terdaftar di Finji.
Mohon pastikan nomor yang kamu gunakan untuk berinteraksi dengan Finji sudah benar dan terverifikasi.
Kalau belum pernah daftar, silakan daftar di sini ya:
\n" . $link;

            WhatsappService::sendMessage($whatsappId, $message);
            return [false, 'Nomor belum terdaftar', []];
        }


        try {
            // Analyze message with AI
            $parsed = $this->analyzeWithAI($rawMessage);

            // Handle different actions
            switch ($parsed['action']) {
                case 'record':
                    $this->handleRecordAction($user, $parsed);
                    return [true, 'Pesan berhasil dikirim.', []];

                case 'record_multiple':
                    $this->handleMultipleRecordAction($user, $parsed);
                    return [true, 'Pesan berhasil dikirim.', []];

                case 'help':
                case 'not_transaction':
                case 'dashboard_redirect':
                case 'chat':
                    $response = $parsed['response'];
                    WhatsappService::sendMessage($user->whatsapp, $response);
                    return [true, 'Pesan berhasil dikirim.', []];

                default:
                    Log::info($parsed);
                    return [false, 'Maaf, terjadi kesalahan. Silakan coba lagi.', []];
            }
        } catch (\Throwable $e) {
            Log::error('Message processing failed: ' . $e->getMessage());
            return [false, 'Maaf, terjadi kesalahan. Silakan coba lagi.', []];
        }
    }

    public function analyzeWithAI($message)
    {

        $currentDate        = Carbon::now()->format('Y-m-d');
        $yesterday          = Carbon::now()->subDay()->format('Y-m-d');
        $tomorrow           = Carbon::now()->addDay()->format('Y-m-d');

        $promptTemplate = <<<EOT
You are Finji, an intelligent personal finance assistant for finji.app. Your PRIMARY function is to record financial transactions. If the user's message is NOT a financial transaction, guide them on how to record one.

TASK:
1. Analyze if the message is a financial transaction
2. If YES: Extract details in JSON format
3. If NO: Guide user to record transactions with correct format

OUTPUT FORMAT (JSON ONLY):
{
  "type": "income" or "expense" or null,
  "amount": number or null,
  "category": string or null,
  "description": string or null,
  "date": "YYYY-MM-DD" or null,
  "period": string or null,
  "action": "record", "record_multiple", "summary", "help", "chat", or "not_transaction",
  "response": string,
  "transactions": array or null,
  "query": "total_income", "total_expense", or null
}

ACTION DEFINITIONS:
- "record": Single transaction recording
- "record_multiple": Multiple transactions
- "summary": Financial summary request
- "help": User needs guidance
- "chat": Casual conversation
- "not_transaction": Message is NOT a transaction → guide user

CRITICAL RULE:
IF user message is NOT a financial transaction → action MUST BE "not_transaction"

TRANSACTION RECOGNITION:
A message IS a financial transaction if it contains:
1. Amount/money + purpose (e.g., "50rb buat makan", "gaji 5jt")
2. Spending/income verbs + amount (e.g., "beli kopi 20rb", "dapat bonus 1jt")
3. Explicit recording intent (e.g., "catat pengeluaran 100rb")

A message is NOT a transaction if:
- Greetings/small talk (halo, apa kabar, etc.)
- Questions about features/capabilities
- General financial advice requests
- Emotional expressions without transaction data
- Asking how to use the app

FORMAT INSTRUCTION FOR USERS:
When action = "not_transaction", your response MUST:
1. Politely explain you're a transaction recorder
2. Provide clear format examples:
   • "Contoh format: [jumlah] [untuk/tujuan]"
   • "50rb untuk makan siang"
   • "Gaji 5jt bulan ini"
   • "Bayar listrik 500rb"
3. Keep it friendly but focused

JSON FIELD RULES:
TYPE:
- "expense": beli, bayar, jajan, keluar uang, pengeluaran, etc.
- "income": gaji, terima, dapat, jual, bonus, pemasukan, etc.
- null if not transaction

AMOUNT:
- Convert to number: "50rb" → 50000, "1jt" → 1000000
- If just number: "20" → 20000 (assume thousands)
- If no amount: null AND action = "not_transaction"

CATEGORY:
- Use Bahasa Indonesia: makan, transportasi, belanja, gaji, tagihan, hiburan, etc.
- Extract from context: "beli nasi" → "makan"
- Default: "lainnya"

DESCRIPTION:
- Brief, no amounts: "Makan siang di warung" not "Makan 50rb"

DATE:
- Relative dates: "hari ini" = "$currentDate"
- "kemarin" = "$yesterday"
- "besok" = "$tomorrow"
- Default: "$currentDate"

PERIOD for SUMMARY:
- "hari ini" → "today"
- "minggu ini" → "this_week"
- "bulan ini" → "this_month"
- Specific: "specific_date" or "specific_month"

RESPONSE GUIDELINES:
- Bahasa Indonesia, friendly tone
- For "not_transaction": Focus on guiding to record
- Emojis optional
- No markdown

SUMMARY :
1. User asks for ringkasan/laporan/grafik:
   - Action: "dashboard_redirect"
   - Response must include dashboard mention

Now process this message:
"$message"
EOT;
        // API call remains the same
        $apiKey = config('services.google.gemini_api_key');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

        $response = Http::post($url, [
            'contents' => [
                ['parts' => [['text' => $promptTemplate]]]
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

        // Validate required fields
        if (!$parsed['response']) {
            throw new \Exception('Incomplete AI response');
        }

        return $parsed;
    }

    protected function handleRecordAction($user, $parsed)
    {
        // Validate transaction data
        if (empty($parsed['amount']) || !is_numeric($parsed['amount']) || !$parsed['description']) {
            return [
                'success' => false,
                'message' => 'Jumlah tidak valid',
                'data' => $result ?? []
            ];
        }

        // Create transaction
        Transaction::create([
            'user_id' => $user->id,
            'transaction_category_id' => $this->mapCategory($parsed['category'], $parsed['type']),
            'amount' => $parsed['amount'],
            'description' => $parsed['description'] ?? '',
            'type' => $parsed['type'] === 'income' ? 'income' : 'expense',
            'transaction_date' => $parsed['date'],
        ]);

        $userBalance = UserBalance::where('user_id', $user->id)->first();

        if ($parsed['type'] === 'income') {
            $userBalance->increment('balance', $parsed['amount']);
        } elseif ($parsed['type'] === 'expense') {
            $userBalance->decrement('balance', $parsed['amount']);
        }

        $formatted = 'Rp' . number_format($parsed['amount'], 0, ',', '.');
        $response = str_replace('{amount}', $formatted, $parsed['response']);

        WhatsappService::sendMessage($user->whatsapp, $response);

        return [
            'success' => true,
            'message' => $parsed['response'],
            'data' => $result ?? []
        ];
    }

    protected function handleMultipleRecordAction($user, $parsed)
    {
        if (empty($parsed['transactions']) || !is_array($parsed['transactions'])) {
            return [
                'success' => false,
                'message' => 'Jumlah tidak valid',
                'data' => $result ?? []
            ];
        }

        $userBalance = UserBalance::where('user_id', $user->id)->first();

        foreach ($parsed['transactions'] as $trx) {
            // Validasi nominal
            if (empty($trx['amount']) || !is_numeric($trx['amount'])) {
                continue; // skip transaksi yang tidak valid
            }

            // Insert transaksi
            Transaction::create([
                'user_id' => $user->id,
                'transaction_category_id' => $this->mapCategory($trx['category'], $trx['type']),
                'amount' => $trx['amount'],
                'description' => $trx['description'] ?? '',
                'type' => $trx['type'] === 'income' ? 'income' : 'expense',
                'transaction_date' => $trx['date'],
            ]);

            // Update saldo
            if ($trx['type'] === 'income') {
                $userBalance->increment('balance', $trx['amount']);
            } elseif ($trx['type'] === 'expense') {
                $userBalance->decrement('balance', $trx['amount']);
            }
        }

        // Kirim pesan hanya dari response final
        $response = $parsed['response'] ?? 'Semua transaksi sudah dicatat.';

        WhatsappService::sendMessage($user->whatsapp, $response);

        return [
            'success' => true,
            'message' => $response,
            'data' => $result ?? []
        ];
    }

    public function mapCategory($categoryName, $type)
    {
        $slug = str()->slug($categoryName);

        // Implement your category mapping logic here
        return TransactionCategory::firstOrCreate(['name' => str()->title($categoryName), 'slug' => $slug, 'type' => $type])->id;
    }
}
