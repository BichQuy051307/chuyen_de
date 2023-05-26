<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
	protected $table = 'orders';
	protected $fillable  = [
		'order_id', 'user_id', 'name', 'phone', 'email', 'address',
		'customer_notes', 'notes',
		'amount', 'score_awards', 'status'
	];
}
