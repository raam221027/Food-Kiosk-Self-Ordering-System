<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'payment_method',
        'order_type',
       
    ];
    
    /**
     * Orders whose money has actually been collected, i.e. the basis of every sales figure.
     * An order goes Unpaid -> Paid (cashier takes payment) -> Done (kitchen finishes it),
     * so revenue is earned at 'Paid' and must not wait for the kitchen.
     */
    public function scopeRevenue($query)
    {
        return $query->whereIn('status', ['Paid', 'Done']);
    }

    public function items()
    {
       return $this->belongsToMany(Product::class, 'order_items')->withPivot('quantity');
    }
    

    public function totalAmount()
    {
        return $this->items->sum(function ($item) {
            return $item->pivot->quantity * $item->price;
        });
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function addons()
    {
        return $this->hasMany(AddOns::class);
    }

}