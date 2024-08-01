<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class TaobaoCategorySeeder extends Seeder
{
    public function run(): void
    {
        $response = Http::get('https://api.atphosting24.com/category/api.php?folder=1688');

        if ($response->successful()) {
            $categories = $response->json('categories');

            foreach ($categories as $category) {
                $categoryId = DB::table('taobao_categories')->insertGetId([
                    'name_en' => $category['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($category['subcategories'] as $subcategory) {
                    $subcategoryNameId = DB::table('taobao_subcategory_names')->insertGetId([
                        'taobao_category_id' => $categoryId,
                        'subcategory_name_en' => $subcategory['name'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    foreach ($subcategory['items'] as $item) {
                        DB::table('taobao_subcategories')->insert([
                            'taobao_subcategory_name_id' => $subcategoryNameId,
                            'subcategory_en' => $item,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }
}
