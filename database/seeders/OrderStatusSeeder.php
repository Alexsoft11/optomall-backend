<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderStatusSeeder extends Seeder
{
    public function run()
    {
        $statuses = [
            [
                'name_uz' => 'V proverke',
                'name_en' => 'V proverke',
                'name_ru' => 'V proverke',
                'message_uz' => 'Buyurtmangizning holati "V proverke" sifatida yangilandi.',
                'message_en' => 'Your order status has been updated to "V proverke".',
                'message_ru' => 'Статус вашего заказа был обновлен на "V proverke".',
                'color' => '#FFA500', // Orange
                'type' => 'in_verification'
            ],
            [
                'name_uz' => 'Zakaz prinyat',
                'name_en' => 'Zakaz prinyat',
                'name_ru' => 'Zakaz prinyat',
                'message_uz' => 'Buyurtmangizning holati "Zakaz prinyat" sifatida yangilandi.',
                'message_en' => 'Your order status has been updated to "Zakaz prinyat".',
                'message_ru' => 'Статус вашего заказа был обновлен на "Zakaz prinyat".',
                'color' => '#0000FF', // Blue
                'type' => 'active'
            ],
            [
                'name_uz' => 'Dostavlyaetsa',
                'name_en' => 'Dostavlyaetsa',
                'name_ru' => 'Dostavlyaetsa',
                'message_uz' => 'Buyurtmangizning holati "Dostavlyaetsa" sifatida yangilandi.',
                'message_en' => 'Your order status has been updated to "Dostavlyaetsa".',
                'message_ru' => 'Статус вашего заказа был обновлен на "Dostavlyaetsa".',
                'color' => '#FFA500', // Orange
                'type' => 'active'
            ],
            [
                'name_uz' => 'Zakaz pribil',
                'name_en' => 'Zakaz pribil',
                'name_ru' => 'Zakaz pribil',
                'message_uz' => 'Buyurtmangizning holati "Zakaz pribil" sifatida yangilandi.',
                'message_en' => 'Your order status has been updated to "Zakaz pribil".',
                'message_ru' => 'Статус вашего заказа был обновлен на "Zakaz pribil".',
                'color' => '#008000', // Green
                'type' => 'active'
            ],
            [
                'name_uz' => 'Zakaz dostavlen',
                'name_en' => 'Zakaz dostavlen',
                'name_ru' => 'Zakaz dostavlen',
                'message_uz' => 'Buyurtmangizning holati "Zakaz dostavlen" sifatida yangilandi.',
                'message_en' => 'Your order status has been updated to "Zakaz dostavlen".',
                'message_ru' => 'Статус вашего заказа был обновлен на "Zakaz dostavlen".',
                'color' => '#00FFFF', // Cyan
                'type' => 'active'
            ],
            [
                'name_uz' => 'Zakaz otmenen',
                'name_en' => 'Zakaz otmenen',
                'name_ru' => 'Zakaz otmenen',
                'message_uz' => 'Buyurtmangizning holati "Zakaz otmenen" sifatida yangilandi.',
                'message_en' => 'Your order status has been updated to "Zakaz otmenen".',
                'message_ru' => 'Статус вашего заказа был обновлен на "Zakaz otmenen".',
                'color' => '#FF0000', // Red
                'type' => 'in_verification'
            ],
            [
                'name_uz' => 'Ne ostalos v sklade',
                'name_en' => 'Ne ostalos v sklade',
                'name_ru' => 'Ne ostalos v sklade',
                'message_uz' => 'Buyurtmangizning holati "Ne ostalos v sklade" sifatida yangilandi.',
                'message_en' => 'Your order status has been updated to "Ne ostalos v sklade".',
                'message_ru' => 'Статус вашего заказа был обновлен на "Ne ostalos v sklade".',
                'color' => '#FF0000', // Red
                'type' => 'in_verification'
            ]
        ];

        foreach ($statuses as $status) {
            DB::table('order_statuses')->insert([
                'name_uz' => $status['name_uz'],
                'name_en' => $status['name_en'],
                'name_ru' => $status['name_ru'],
                'message_uz' => $status['message_uz'],
                'message_en' => $status['message_en'],
                'message_ru' => $status['message_ru'],
                'color' => $status['color'],
                'type' => $status['type'],
                'slug' => Str::slug($status['name_uz'], '-'),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
