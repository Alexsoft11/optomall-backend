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
                'name_uz' => 'Tayyorlanmoqda',
                'name_en' => 'Preparing',
                'name_ru' => 'Готовится',
                'message_uz' => 'Buyurtmangizning holati "Tayyorlanmoqda" sifatida yangilandi. Buyurtmangiz uchun tovarlarni yig\'moqdamiz va u jo\'natishga tayyor bo\'lganda sizga xabar beramiz.',
                'message_en' => 'Your order status has been updated to "Preparing". We are gathering the items for your order and will notify you once it is ready for shipment.',
                'message_ru' => 'Статус вашего заказа был обновлен на "Готовится". Мы собираем товары для вашего заказа и уведомим вас, как только он будет готов к отправке.',
                'color' => '#0000FF', // Blue
                'type' => 'active'
            ],
            [
                'name_uz' => 'Kutilmoqda',
                'name_en' => 'Waiting',
                'name_ru' => 'Ожидание',
                'message_uz' => 'Buyurtmangizning holati "Kutilmoqda" sifatida yangilandi. Hozirda biz sizning buyurtmangizni qayta ishlayapmiz va u jo\'natishga tayyor bo\'lganda sizga xabar beramiz.',
                'message_en' => 'Your order status has been updated to "Waiting". We are currently processing your order and will notify you once it is ready for shipment.',
                'message_ru' => 'Статус вашего заказа был обновлен на "Ожидание". Мы обрабатываем ваш заказ и уведомим вас, как только он будет готов к отправке.',
                'color' => '#808080', // Gray
                'type' => 'active'
            ],
            [
                'name_uz' => 'Omborda',
                'name_en' => 'In Warehouse',
                'name_ru' => 'На складе',
                'message_uz' => 'Buyurtmangizning holati "Omborda" sifatida yangilandi. Buyurtmangiz hozirda omborimizda va tez orada jo\'natiladi.',
                'message_en' => 'Your order status has been updated to "In Warehouse". Your order is now in our warehouse and will be shipped soon.',
                'message_ru' => 'Статус вашего заказа был обновлен на "На складе". Ваш заказ сейчас на нашем складе и скоро будет отправлен.',
                'color' => '#008000', // Green
                'type' => 'active'
            ],
            [
                'name_uz' => 'Xaridorga topshirildi',
                'name_en' => 'Delivered to Customer',
                'name_ru' => 'Доставлено покупателю',
                'message_uz' => 'Buyurtmangizning holati "Xaridorga topshirildi" sifatida yangilandi. Buyurtmangiz muvaffaqiyatli ravishda sizga topshirildi.',
                'message_en' => 'Your order status has been updated to "Delivered to Customer". Your order has been successfully delivered to you.',
                'message_ru' => 'Статус вашего заказа был обновлен на "Доставлено покупателю". Ваш заказ был успешно доставлен вам.',
                'color' => '#00FFFF', // Cyan
                'type' => 'active'
            ],
            [
                'name_uz' => 'Tekshirildi',
                'name_en' => 'Checked',
                'name_ru' => 'Проверено',
                'message_uz' => 'Buyurtmangizning holati "Tekshirildi" sifatida yangilandi. Buyurtmangizni tekshirdik va hamma narsa joyida.',
                'message_en' => 'Your order status has been updated to "Checked". We have checked your order and everything is in order.',
                'message_ru' => 'Статус вашего заказа был обновлен на "Проверено". Мы проверили ваш заказ и все в порядке.',
                'color' => '#00FF00', // Green
                'type' => 'active'
            ],
            [
                'name_uz' => 'Tekshirilmoqda',
                'name_en' => 'Being Checked',
                'name_ru' => 'Проверяется',
                'message_uz' => 'Buyurtmangizning holati "Tekshirilmoqda" sifatida yangilandi. Hozirda biz sizning buyurtmangizning tafsilotlarini tekshirmoqdamiz va yaqinada yangilaymiz.',
                'message_en' => 'Your order status has been updated to "Being Checked". We are currently checking the details of your order and will update you shortly.',
                'message_ru' => 'Статус вашего заказа был обновлен на "Проверяется". Мы проверяем детали вашего заказа и скоро обновим информацию.',
                'color' => '#FFA500', // Orange
                'type' => 'in_verification'
            ],
            [
                'name_uz' => 'Tekshiruvdan o\'tmadi',
                'name_en' => 'Did not Pass Check',
                'name_ru' => 'Не прошло проверку',
                'message_uz' => 'Buyurtmangizning holati "Tekshiruvdan o\'tmadi" sifatida yangilandi. Afsuski, buyurtmangiz bizning tekshiruv jarayonimizdan o\'tmadi.',
                'message_en' => 'Your order status has been updated to "Did not Pass Check". Unfortunately, your order did not pass our verification process.',
                'message_ru' => 'Статус вашего заказа был обновлен на "Не прошло проверку". К сожалению, ваш заказ не прошел нашу проверку.',
                'color' => '#FF0000', // Red
                'type' => 'in_verification'
            ],
            [
                'name_uz' => 'Bekor qilindi',
                'name_en' => 'Cancelled',
                'name_ru' => 'Отменено',
                'message_uz' => 'Buyurtmangizning holati "Bekor qilindi" sifatida yangilandi. Buyurtmangiz sizning so\'rovingiz bo\'yicha yoki ba\'zi muammolar tufayli bekor qilindi.',
                'message_en' => 'Your order status has been updated to "Cancelled". Your order has been cancelled due to your request or some issues.',
                'message_ru' => 'Статус вашего заказа был обновлен на "Отменено". Ваш заказ был отменен по вашему запросу или из-за некоторых проблем.',
                'color' => '#FF0000', // Red
                'type' => 'in_verification'
            ],
            [
                'name_uz' => 'Yetkazib berilmoqda',
                'name_en' => 'Being Delivered',
                'name_ru' => 'Доставляется',
                'message_uz' => 'Buyurtmangizning holati "Yetkazib berilmoqda" sifatida yangilandi. Buyurtmangiz hozirda ko\'rsatilgan manzilga yetkazilmoqda.',
                'message_en' => 'Your order status has been updated to "Being Delivered". Your order is currently being delivered to the specified address.',
                'message_ru' => 'Статус вашего заказа был обновлен на "Доставляется". Ваш заказ в настоящее время доставляется по указанному адресу.',
                'color' => '#FFA500', // Orange
                'type' => 'active'
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
