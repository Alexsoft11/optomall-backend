<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OrderGroup;
use App\Models\Income;

class GenerateOldIncomes extends Command
{
    protected $signature = 'income:generate-old';

    protected $description = 'Generate Income records for old paid OrderGroups (run once only)';

    public function handle()
    {
        $this->info("Starting to generate old incomes...");

        $orderGroups = OrderGroup::where('is_paid', true)->get();

        $created = 0;
        $skipped = 0;

        foreach ($orderGroups as $group) {

            // Skip if already has income
            $exists = Income::where('order_group_id', $group->id)->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            // Calculate amount from NON-cancelled orders
            $amount = $group->orders()
                ->whereHas('getStatus', fn($q) => $q->where('is_cancel', false))
                ->sum('total_price');

            // Create income
            Income::create([
                'user_id'        => $group->user_id,
                'order_group_id' => $group->id,
                'amount'         => $amount,
                'type'           => 'order_payment',
            ]);

            $created++;
        }

        $this->info("Done!");
        $this->info("Created: {$created} income records");
        $this->info("Skipped (already existed): {$skipped}");

        return Command::SUCCESS;
    }
}
