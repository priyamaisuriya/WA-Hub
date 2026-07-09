<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\WhatsAppController;

Route::get('/', [WhatsAppController::class, 'chatIndex']);

// Webhook routes for Meta to verify and push messages to us
Route::get('/webhook', [WhatsAppController::class, 'verifyWebhook']);
Route::post('/webhook', [WhatsAppController::class, 'handleWebhook']);

// API routes for our frontend UI
Route::get('/api/contacts', [WhatsAppController::class, 'getContacts']);
Route::get('/api/messages/{contact}', [WhatsAppController::class, 'getMessages']);
Route::post('/api/send-message', [WhatsAppController::class, 'sendMessage']);
