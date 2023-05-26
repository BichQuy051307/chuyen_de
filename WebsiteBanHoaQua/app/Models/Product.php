<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
	protected $table = 'products';
	protected $fillable  = [
		'name', 'code', 'description', 'category_id', 'slug', 'image', 'price',  'price_sale', 'quantity', 'bought', 'view_count', 'status', 'unit'
	];
}
