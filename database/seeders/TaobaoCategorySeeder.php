<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use App\Models\TaobaoCategory;
use App\Models\TaobaoSubcategoryName;
use App\Models\TaobaoSubcategory;
use Illuminate\Support\Facades\DB;

class TaobaoCategorySeeder extends Seeder
{
    public function run(): void
    {

        $response = Http::get('https://api.atphosting24.com/category/api.php?folder=taobao');

        if ($response->successful()) {
            $categories = $response->json('categories');

            foreach ($categories as $category) {
                $taobaoCategory = TaobaoCategory::create([
                    'name_uz' => $category['name'],
                    'name_ru' => $category['name'],
                    'name_en' => $category['name'],
                    'unique' => $category['name'],
                    'is_active' => false,
                ]);

                foreach ($category['subcategories'] as $subcategory) {
                    $taobaoSubcategoryName = TaobaoSubcategoryName::create([
                        'taobao_category_id' => $taobaoCategory->unique_id,
                        'subcategory_name_en' => $subcategory['name'],
                        'subcategory_name_uz' => $subcategory['name'],
                        'subcategory_name_ru' => $subcategory['name'],
                        'unique' => $subcategory['name'],
                        'is_active' => true,
                    ]);

                    foreach ($subcategory['items'] as $item) {
                        TaobaoSubcategory::create([
                            'taobao_subcategory_name_id' => $taobaoSubcategoryName->unique_id,
                            'subcategory_en' => $item,
                            'subcategory_uz' => $item,
                            'subcategory_ru' => $item,
                            'unique' => $item,
                            'is_show' => true,
                        ]);
                    }
                }
            }
        }
    }
}
