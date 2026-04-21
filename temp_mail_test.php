<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    Illuminate\Support\Facades\Mail::raw('Test mail from Vocaseek', function ($message) {
        $message->to('ardikarendra50@gmail.com')
            ->subject('Vocaseek SMTP Test');
    });

    echo "MAIL_OK\n";
} catch (Throwable $e) {
    echo get_class($e)."\n";
    echo $e->getMessage()."\n";
}
