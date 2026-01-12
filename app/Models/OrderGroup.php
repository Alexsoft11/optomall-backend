<?php

namespace App\Models;

use App\Events\OrderStatusUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class OrderGroup extends Model
{
    use HasFactory;

    protected $fillable = ['status', 'status_color', 'total_price', 'user_id', 'is_paid', 'unique_id', 'delivery_china'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->unique_id = $model->generateUniqueId();
        });

        // static::updated(function ($order) {
        //     if ($order->isDirty('status')) {
        //         foreach ($order->orders as $orderr) {
        //             event(new OrderStatusUpdated($orderr));
        //         }
        //     }
        // });

        static::updated(function ($orderGroup) {
            // Sync orders is_paid
            if ($orderGroup->isDirty('is_paid')) {
                $orderGroup->orders()->update([
                    'is_paid' => $orderGroup->is_paid
                ]);
            }

            // Sync Income if needed
            if ($orderGroup->isDirty('is_paid') || $orderGroup->isDirty('total_price')) {
                $orderGroup->syncIncome();
            }
        });

        static::updating(function ($orderGroup) {
            if (!$orderGroup->isDirty('status'))
                return;

            $orderGroup->orders()
                ->whereHas('getStatus', function ($q) {
                    $q->where('is_cancel', false);
                })
                ->update(['status' => $orderGroup->status]);
        });
    }

    protected function generateUniqueId()
    {
        $letters = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 3));
        $numbers = substr(str_shuffle('0123456789'), 0, 4);
        return $letters . $numbers;
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function checks()
    {
        return $this->hasMany(OrderCheck::class);
    }

    public function getStatus()
    {
        return $this->belongsTo(OrderStatus::class, 'status');
    }

    protected static function booted(): void
    {
        // Delete all orders when orderGroup is deleted
        static::deleting(function (OrderGroup $orderGroup) {
            $orderGroup->orders()->delete();
        });
    }

    public function recalculateTotals()
    {
        $activeOrders = $this->orders()
            ->whereHas('getStatus', function ($q) {
                $q->where('is_cancel', false);
            })
            ->get();

        Log::info('Active orders for recalculation:', [
            'total_price' => $activeOrders->sum('total_price'),
            'delivery_china' => $activeOrders->sum('delivery_china'),
            'order_group_id' => $this->id,
        ], );
        $this->update([
            'total_price' => $activeOrders->sum('total_price'),
            'delivery_china' => $activeOrders->sum('delivery_china'),
        ]);
    }

    private function syncIncome(): void
    {
        // Only if the order group is paid
        if (!$this->is_paid) {
            // If unpaid → delete income if exists
            \App\Models\Income::where('order_group_id', $this->id)->delete();
            return;
        }

        // Calculate pricing fields from order items (only non-cancelled orders)
        $this->load(['orders.items', 'orders.getStatus']);

        $activeOrders = $this->orders->filter(fn($order) => $order->getStatus && !$order->getStatus->is_cancel);
        $allItems = $activeOrders->flatMap(fn($order) => $order->items);

        $originalPriceYuan = $allItems->sum(fn($item) => ($item->original_price_yuan ?? 0) * ($item->quantity ?? 1));
        $localDeliveryYuan = $allItems->sum(fn($item) => ($item->local_delivery_yuan ?? 0) * ($item->quantity ?? 1));
        $profitAmount = $allItems->sum(fn($item) => ($item->profit_amount ?? 0) * ($item->quantity ?? 1));

        // Update if exists, otherwise create
        \App\Models\Income::updateOrCreate(
            ['order_group_id' => $this->id],
            [
                'user_id' => $this->user_id,
                'amount' => $this->total_price,
                'type' => 'order',
                'order_group_id' => $this->id,
                'original_price_yuan' => $originalPriceYuan,
                'local_delivery_yuan' => $localDeliveryYuan,
                'profit_amount' => $profitAmount,
            ]
        );
    }
}
