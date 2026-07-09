<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$token = env('WHATSAPP_TOKEN');
$phoneId = env('WHATSAPP_PHONE_ID');
$phone = '919016409449';

$url = "https://graph.facebook.com/v19.0/{$phoneId}/messages";

$response = Illuminate\Support\Facades\Http::withToken($token)->post($url, [
    'messaging_product' => 'whatsapp',
    'recipient_type' => 'individual',
    'to' => $phone,
    'type' => 'text',
    'text' => [
        'preview_url' => false,
        'body' => 'Hello from WA-Hub! The integration is working.'
    ]
]);

if ($response->successful()) {
    echo "Message sent successfully!\n";
    echo $response->body();
} else {
    echo "Failed to send message.\n";
    echo $response->body();
}
