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
        $url = "http://otapi.net/service-json/GetRootCategoryInfoList?instanceKey=06e38b86-e4a7-451e-91b3-2b40d2dd3e24&language=uz&signature=71c80f0af3ef204bfac0ea7479c0e80f5bd27328d8a6a3c9b417eb488e9dac85&timestamp=20240526160409";

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
