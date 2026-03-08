<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\HomePageProduct;
use Illuminate\Support\Facades\DB;

echo "--- DATABASE STATISTICS ---\n";
try {
    $stats = HomePageProduct::select('provider', 'type', 'is_deleted', DB::raw('count(*) as total'))
        ->groupBy('provider', 'type', 'is_deleted')
        ->get();

    if ($stats->isEmpty()) {
        echo "Table is EMPTY!\n";
    } else {
        foreach ($stats as $stat) {
            echo "Provider: " . str_pad($stat->provider ?? 'NULL', 10) .
                " | Type: " . str_pad($stat->type ?? 'NULL', 10) .
                " | Deleted: " . ($stat->is_deleted ? 'YES' : 'NO ') .
                " | Count: " . $stat->total . "\n";
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
echo "---------------------------\n";
