<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeExpiredCache extends Command
{
    protected $signature = 'db:purge-cache';
    protected $description = 'Delete expired cache entries from database cache table';

    public function handle()
    {
        $deleted = DB::table('cache')->where('expiration', '<', time())->delete();
        $this->info("Deleted {$deleted} expired cache entries.");
    }
}
