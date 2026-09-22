<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryOrder extends Model
{
    protected $guarded = [];

    protected $casts = [
        'items' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function deliveryAddress()
    {
        return $this->belongsTo(CustomerAddress::class, 'delivery_address_id');
    }
}
