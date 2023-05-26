<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $table = 'orders_detail';
    protected $fillable  = [
        'order_id', 'product_id', 'name', 'slug', 'code',
        'quantity', 'sale', 'price', 'price_sale', 'status'
    ];
}
