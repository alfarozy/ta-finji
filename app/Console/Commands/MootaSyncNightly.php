<?php

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\BankAccount;
use App\Models\Transaction;
use Carbon\Carbon;

class MootaSyncNightly extends Command
{
    protected $signature = 'moota:sync-nightly';
    protected $description = 'Sync mutasi bank dari Moota (scheduled nightly)';

    public function handle()
    {
        BankAccount::chunk(50, function ($accounts) {
            foreach ($accounts as $bankAccount) {
                $this->syncBankAccount($bankAccount);
            }
        });

        $this->info('Moota nightly sync selesai');
    }

    protected function syncBankAccount(BankAccount $bankAccount)
    {
        $startDate = optional($bankAccount->last_synced_at)
            ? Carbon::parse($bankAccount->last_synced_at)->subDay()->toDateString()
            : now()->subDays(1)->toDateString();

        $endDate = now()->toDateString();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.moota.api_key'),
            'Accept' => 'application/json',
        ])->get(
            config('services.moota.base_url') . '/api/v2/mutation',
            [
                'bank'       => $bankAccount->moota_bank_id,
                'type'       => 'CR|DB',
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'per_page'   => 100,
            ]
        );

        if (! $response->successful()) {
            return;
        }

        $mutations = $response->json('data') ?? [];

        foreach ($mutations as $m) {

            // DEDUP
            if (Transaction::where('external_id', $m['mutation_id'])->exists()) {
                continue;
            }

            $type = $m['type'] === 'CR'
                ? Transaction::TYPE_INCOME
                : Transaction::TYPE_EXPENSE;

            $categoryId = $this->autoCategory($m['description'], $type);

            Transaction::create([
                'user_id' => $bankAccount->user_id,
                'external_id' => $m['mutation_id'],
                'transaction_category_id' => $categoryId,
                'amount' => (float) $m['amount'],
                'description' => $m['description'],
                'type' => $type,
                'transaction_date' => Carbon::parse($m['date']),
                'source' => Transaction::SOURCE_MUTATION,
            ]);
        }

        $bankAccount->update([
            'last_synced_at' => now(),
        ]);
    }

    /**
     * RULE BASED (BISA DIGANTI AI)
     */
    protected function autoCategory(string $description, string $type): ?int
    {
        $desc = strtolower($description);

        if ($type === Transaction::TYPE_INCOME) {
            if (str_contains($desc, 'gaji')) return 1;
            if (str_contains($desc, 'transfer')) return 2;
        }

        if ($type === Transaction::TYPE_EXPENSE) {
            if (str_contains($desc, 'qris')) return 3;
            if (str_contains($desc, 'pln')) return 4;
            if (str_contains($desc, 'grab') || str_contains($desc, 'gojek')) return 5;
        }

        return null; // Lainnya
    }
}
