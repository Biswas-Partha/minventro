<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $guarded = [];

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class)->orderByDesc('is_default');
    }

    public function deliveryOrders()
    {
        return $this->hasMany(DeliveryOrder::class)->orderByDesc('created_at');
    }
}
