<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductDetails;
use App\Models\ProductImages;
use App\Models\Category;
use App\Models\Cart;
use App\Models\Tag;

class ProductController extends Controller
{

	public function productList(Request $request)
	{
		try{

			$filters = $request->all();

			$data = [];

			$products = Product::with(['tag', 'category']);

			if ( isset( $filters['categoryIds'] ) && !empty( $filters['categoryIds'] ) ) {
				
				$products = $products->whereHas('category', function($query) use( $filters )
				{
					$query->whereIn('categories.id', $filters['categoryIds'] );
				});
			}

			if ( isset( $filters['tagIds'] ) && !empty( $filters['tagIds'] ) ) {
				
				$products = $products->orwhereHas('tag', function($query) use( $filters )
				{
					$query->whereIn('tags.id', $filters['tagIds'] );
				});
			}

			if ( isset( $filters['search'] ) && $filters['search'] != "" ) {
				
				$products = $products->Where('name', 'like', '%' . $filters['search'] . '%');
			}

			if ( isset( $filters['sortBy'] ) ) {
					
				if ( $filters['sortBy'] == 'created-descending' ) {
					
					$products = $products->orderBy('created_at', 'DESC');
				}
				else if ( $filters['sortBy'] == 'title-ascending' ) {
					
					$products = $products->orderBy('name', 'ASC');
				}
				else if ( $filters['sortBy'] == 'title-descending' ) {
					
					$products = $products->orderBy('name', 'DESC');
				}
				else if ( $filters['sortBy'] == 'price-ascending' ) {
					
					$products = $products->orderBy('sell_price', 'ASC');
				}
				else if ( $filters['sortBy'] == 'price-descending' ) {
					
					$products = $products->orderBy('sell_price', 'DESC');
				}
				else if ( $filters['sortBy'] == 'created-ascending' ) {
					
					$products = $products->orderBy('created_at', 'ASC');
				}
			}

			$products = $products->where('status', 1)->paginate(2);

			$total_page = $products->lastPage();

			foreach ($products as $ke => $value) {
				
				$product = [];

				$product['name'] = $value->name;
				$product['slug'] = $value->slug;
				$product['id'] = $value->id;
				$product['description'] = $value->description;
				$product['created_at'] = $value->created_at;
				$product['orignal_price'] = $value->orignal_price;
				$product['sell_price'] = $value->sell_price;
				$product['discount'] = $value->discount;
				$product['image'] = $value->getProductImageUrl();

				if ( $value->tag != null ) {
					
					foreach ($value->tag as $key => $tag) {
						
						$product['tags'][] = $tag->name;

					}
				}

				if ( $value->category != null ) {
					
					foreach ($value->category as $key => $category) {
						
						$product['category'][] = $category->name;

					}
				}

				$data[] = $product;
			}

			return response()->json([ 'data' => $data, 'total_page' => $total_page ,'status' => true], 200);

		}catch( \Exception $e){

			$msg = 'Something Went Wrong';

			return response()->json([ 'msg' => $msg, 'status' => false], 500);
		}
	}

	public function categoryList()
	{
		try{

			$data = Category::with(['product'])->has('product')->where('status', 1)->get();

			$total = $data->count();

			$category = [];

			foreach ($data as $key => $value) {
				
				$data = [];
				$data['id'] = $value->id;
				$data['name'] = $value->name;
				$data['total_product'] = $value->product->count();

				$category[] = $data; 
			}

			return response()->json([ 'data' => $category, 'total' => $total ,'status' => true], 200);

		}catch( \Exception $e ){

			$msg = 'Something Went Wrong';

			return response()->json([ 'msg' => $msg, 'status' => false], 500);
		}
	}

	public function tagList()
	{
		try{

			$data = Tag::with(['product'])->has('product')->where('status', 1)->get();

			$total = $data->count();

			$tag = [];

			foreach ($data as $key => $value) {
				
				$data = [];
				$data['id'] = $value->id;
				$data['name'] = $value->name;
				$data['total_product'] = $value->product->count();

				$tag[] = $data; 
			}

			return response()->json([ 'data' => $tag, 'total' => $total ,'status' => true], 200);

		}catch( \Exception $e){

			$msg = 'Something Went Wrong';

			return response()->json([ 'msg' => $msg, 'status' => false], 500);
		}
	}

	public function addToCart(Request $request)
	{
		$input = $request->all();

		try{
			

			if (\Auth::user() != null) {
				
				$cart = Cart::where('user_id', \Auth::user()->id)->where('product_id', $request->id)->first();
				$user_id = \Auth::user()->id;

			}else{

				$cart = Cart::where('ip', $request->ip())->where('product_id', $request->id)->first();

				if ( $cart != null ) {
					
					$user_id = $cart->user_id;
					
				}else{

					$user_id = null;
				}
			}

			if ( $cart == null ) {
				
				$cart = new Cart;
				$total_product = 1;

			}else{

				$total_product = $cart->total_product + 1;
			}

			$cart->user_id = $user_id;
			$cart->product_id = $request->id;
			$cart->ip = $request->ip();
			$cart->total_product = $total_product;
			$cart->save();

			$msg = "Product added to cart";
			return response()->json([ 'msg' => $msg, 'status' => true ], 200);

		}catch( \Exception $e ){

			$msg = "Something went wrong";

			return response()->json([ 'msg' => $msg, 'status' => false], 500);
		}

	}

	public function cartItems(Request $request)
	{
		try{

			if (\Auth::check()) {
				
				$cart = Cart::with(['product'])->where('user_id', \Auth::user()->id)->get();
				$total = $cart->count();

			}else{

				$cart = Cart::with(['product'])->where('ip', $request->ip())->get();
				$total = $cart->count();
			}

			$items = [];

			foreach ($cart as $key => $value) {
				
				$data = [];
				$data['cart_id'] = $value->id;
				$data['product_id'] = $value->product->id;
				$data['image'] = $value->product->getProductImageUrl();
				$data['name'] = $value->product->name;
				$data['price'] = $value->product->sell_price;
				$data['total_product'] = $value->total_product;

				$items[] = $data; 
			}

			return response()->json([ 'data' => $items, 'total' => $total ,'status' => true], 200);

		}catch( \Exception $e ){

			$msg = "Something went wrong";

			return response()->json([ 'msg' => $msg, 'status' => false], 500);
		}
	}

	public function removeFromCart(Request $request)
	{
		try{

			if (\Auth::check()) {
				
				$cart = Cart::where('user_id', \Auth::user()->id)->where('id', $request->id)->delete();

			}else{

				$cart = Cart::where('ip', $request->ip())->where('id', $request->id)->delete();
			}

			return response()->json([ 'msg' => 'Product removed from cart' ,'status' => true], 200);


		}catch( \Exception $e ){

			$msg = "Something went wrong";

			return response()->json([ 'msg' => $msg, 'status' => false], 500);

		}
	}

	public function productDetails(Request $request)
	{

		try{

			$product = Product::with(['details', 'images'])->where('slug', $request->slug)->where('status', 1)->first();

			if ( $product == null ) {
				
				$msg = "Product Not Found";

				return response()->json([ 'msg' => $msg, 'status' => false], 404);
			}

			$data = [];
			$data['id'] = $product->id;
			$data['name'] = $product->name;
			$data['description'] = $product->description;
			$data['orignal_price'] = $product->orignal_price;
			$data['sell_price'] = $product->sell_price;
			$data['discount'] = $product->discount;
			$data['image'] = $product->getProductImageUrl();
			$data['long_description'] = $product->long_description;
			$data['slug'] = $product->slug;

			if ( !$product->details->isEmpty() ) {
				
				foreach ($product->details as $key => $pdetail) {
					
					$dt[$key] = [];
					$dt[$key]['detail_key'] = $pdetail->detail_key;
					$dt[$key]['detail_value'] = $pdetail->detail_value;

					$data['extra_details'] = $dt;
				}
			}
			
			if ( !$product->images->isEmpty() ) {
				
				foreach ($product->images as $k => $pimage) {
					
					$data['extra_images'][] = $pimage->getProductImageUrl();
				}
			}

			return response()->json([ 'data' => $data, 'status' => true ], 200);


		}catch(\Exception $e){

			$msg = "Something went wrong";

			return response()->json([ 'msg' => $msg, 'status' => false ], 500);
		}
	}

	public function updateCart(Request $request)
	{
		

		if ( !$request->cartId ) {
			
			return response()->json([ 'msg' => 'Cart Item not found', 'status' => false ], 500);
		}

		try{


			if (\Auth::check()) {
				
				$cart = Cart::where('user_id', \Auth::user()->id)->where('id', $request->cartId)->firstorfail();

			}else{

				$cart = Cart::where('ip', $request->ip())->where('id', $request->cartId)->firstorfail();
			}

			$msg = "Cart Updated successfully";

			if ( $request->type == 'inc') {
				
				$cart->total_product = $cart->total_product + 1;
			}
			else if ( $request->type == 'dec') {
				
				if ( $cart->total_product != 1 ) {
					
					$cart->total_product = $cart->total_product - 1;

				}else{

					$msg = 'Minimum Quantity is one';
				}
			}

			$cart->save();

			return response()->json([ 'msg' => $msg, 'status' => true ], 200);

		}catch(\Exception $e){
			
			$msg = "Something went wrong";

			return response()->json([ 'msg' => $msg, 'status' => false ], 500);
		}
	}
}
