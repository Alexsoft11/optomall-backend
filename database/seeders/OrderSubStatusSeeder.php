<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderSubStatusSeeder extends Seeder
{
    public function run()
    {
        $statuses = [
            [
                'name_uz' => "Buyurtma uchun to'lov kutilmoqda",
                'name_en' => 'Payment for the order is pending',
                'name_ru' => 'Оплата за заказа ожидается',
                'color' => '#ED8204', // Blue
            ],
            [
                'name_uz' => 'Yetkazib berish hisoblab chiqilgan',
                'name_en' => 'Delivery is calculated',
                'name_ru' => 'Доставк рассчитывается',
                'color' => '#0000FF', // Gray
            ],
            [
                'name_uz' => "Yetkazib berish to'lovi kutilmoqda",
                'name_en' => 'Payment for delivery is pending',
                'name_ru' => 'Оплата за доставку ожидается',
                'color' => '#ED8204', // Green
            ],
            [
                'name_uz' => "Yetkazib berish to'lovi to'langa",
                'name_en' => 'Shipping fee paid',
                'name_ru' => 'Оплата за доставку оплачен',
                'color' => '#00AC26', // Cyan
            ],
        ];

        foreach ($statuses as $status) {
            DB::table('order_sub_statuses')->insert([
                'name_uz' => $status['name_uz'],
                'name_en' => $status['name_en'],
                'name_ru' => $status['name_ru'],
                'color' => $status['color'],
                'slug' => Str::slug($status['name_en'], '-'),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
