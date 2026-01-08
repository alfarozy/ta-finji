<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('moota:sync-nightly', function () {
    $this->comment('Singkronisasi transaksi Moota ke database');
})->schedule('daily')->timezone('Asia/Jakarta')->at('00:00');
