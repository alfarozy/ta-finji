<?php

use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WhatsAppChatbotController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//> cllback whatsapp asisten
Route::post('/Finji-webhook/ai-response/message', [WhatsAppChatbotController::class, 'webhookHandle']);

//> callback moota API (sinc transaction)
Route::post('/callback/moota', [TransactionController::class, 'callbackMoota'])->name('callback.moota');
