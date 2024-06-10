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
        $url = "http://otapi.net/service-json/GetRootCategoryInfoList?instanceKey=8e6d9b54-88a7-4dcc-9f2b-add4d68dbbb8&language=uz&signature=71c80f0af3ef204bfac0ea7479c0e80f5bd27328d8a6a3c9b417eb488e9dac85&timestamp=20240526160409";

        $response = Http::get($url);
        $categories = $response->json();

        foreach ($categories['CategoryInfoList']['Content'] as $item) {
            Category::create([
                'name' => $item['Name'],
            ]);
        }
    }
}
