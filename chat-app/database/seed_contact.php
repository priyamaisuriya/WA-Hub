<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$phone = '919016409449';

$contact = App\Models\Contact::firstOrCreate(
    ['wa_id' => $phone],
    ['name' => 'My Test Phone', 'phone_number' => $phone]
);

echo "Contact created: " . $contact->id . "\n";
