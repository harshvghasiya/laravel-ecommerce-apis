<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\User;

class Cart extends Model
{
	public function product()
	{
		return $this->belongsTo(Product::class, 'product_id', 'id');
	}

	public function getCartTotalAmount()
	{
		$cart = self::with(['product'])->where('user_id', \Auth::user()->id)->get();

		$amount = 0;

		foreach ($cart as $key => $value) {
			
			$amount = $amount + ( $value->product->sell_price*$value->total_product );
		}

		return $amount;
	}

	public function getCartItems()
	{
		$cart = self::with(['product'])->where('user_id', \Auth::user()->id)->get();

		return $cart;
	}
}