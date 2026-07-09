<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$token = env('WHATSAPP_TOKEN');
$phoneId = env('WHATSAPP_PHONE_ID');

$res = Illuminate\Support\Facades\Http::withToken($token)->get("https://graph.facebook.com/v19.0/{$phoneId}");
echo $res->body();
