<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SmsProviderService;

class RefreshSmsToken extends Command
{
    protected $signature = 'sms:refresh-token';
    protected $description = 'Refresh Eskiz token';

    public function handle()
    {
        try {
            app(SmsProviderService::class)->refreshToken();
            $this->info('Eskiz token refreshed');
        } catch (\Throwable $e) {
            $this->error('Failed: ' . $e->getMessage());
        }
    }
}
