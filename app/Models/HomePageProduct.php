<?php

namespace App\Models;

use App\Http\Controllers\Api\Products\ProductController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class HomePageProduct extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'title_uz',
        'title_ru',
        'title_en',
        'price',
        'discounted_price',
        'image',
        'product_id',
        'product_url',
        'provider',
        'type'
    ];

    protected static function booted()
    {
        static::saving(function ($homePageProduct) {
            if ($homePageProduct->isDirty('product_id')) {
                // Inject the ProductController
                $productController = app(ProductController::class);

                // Call the method to get product details
                $productDetailResponseuz = $productController->productDetail(new Request([
                    'productId' => $homePageProduct->product_id,
                    'lang' => 'uz',
                    'provider' => $homePageProduct->provider,
                ]));
                $productDetailResponseen = $productController->productDetail(new Request([
                    'productId' => $homePageProduct->product_id,
                    'lang' => 'en',
                    'provider' => $homePageProduct->provider,
                ]));
                $productDetailResponseru = $productController->productDetail(new Request([
                    'productId' => $homePageProduct->product_id,
                    'lang' => 'ru',
                    'provider' => $homePageProduct->provider,
                ]));

                $productDetailuz = json_decode($productDetailResponseuz->getContent(), true)['detail'];
                $productDetailen = json_decode($productDetailResponseen->getContent(), true)['detail'];
                $productDetailru = json_decode($productDetailResponseru->getContent(), true)['detail'];

                // Update fields based on the product detail
                $homePageProduct->title_uz = $productDetailuz['title'] ?? $homePageProduct->title_uz;
                $homePageProduct->title_en = $productDetailen['title'] ?? $homePageProduct->title_en;
                $homePageProduct->title_ru = $productDetailru['title'] ?? $homePageProduct->title_ru;
                $homePageProduct->price = $productDetailuz['price'] ?? $homePageProduct->price;
                $homePageProduct->discounted_price = $productDetailuz['discounted_price'] ?? $homePageProduct->discounted_price;
                $homePageProduct->image = $productDetailuz['image'] ?? $homePageProduct->image;
                $homePageProduct->product_url = $productDetailuz['product_url'] ?? $homePageProduct->product_url;
                $homePageProduct->provider = $productDetailuz['provider'] ?? $homePageProduct->provider;
            }
        });
    }
}
