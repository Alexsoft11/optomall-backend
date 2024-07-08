<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $url = "http://otapi.net/service-json/GetRootCategoryInfoList?instanceKey=2071d7f9-2e5d-41ca-b21a-b22c8482d4db&language=uz&signature=71c80f0af3ef204bfac0ea7479c0e80f5bd27328d8a6a3c9b417eb488e9dac85&timestamp=20240526160409";

        $response = Http::get($url);
        $categories = $response->json();

        foreach ($categories['CategoryInfoList']['Content'] as $item) {
            Category::create([
                'catalog_id' => $item['Id'],
                'name' => $item['Name'],
            ]);
        }

        // Category::factory()->count(20)->create();
    }
}
