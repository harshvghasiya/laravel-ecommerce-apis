<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;

class AuthController extends Controller
{
    public function register(Request $request)
    {
    	$validate = \Validator::make( $request->all(),[

    		'name' => 'required',
    		'email' => 'required|unique:users',
    		'password' => 'required'

    	],[

    		'name.required' => 'Name is required',
    		'email.required' => 'Email is required',
    		'password.required' => 'Password is required'
    	]);

    	if ( $validate->fails() ) 
		{
		    return response()->json([ 'msg' => $validate->errors(), 'status' => false ], 422);
		}


    	$res = new \App\Models\User;
    	$res->name = $request->name;
    	$res->email = $request->email;
    	$res->password = \Hash::make($request->password);
    	$res->save();

    	return response()->json(['msg' => 'User register successfully', 'status' => true ], 200);
    }

    public function login(Request $request)
    {
		$validate = \Validator::make( $request->all(),[

    		'email' => 'required',
    		'password' => 'required'

    	],[

    		'email.required' => 'Email is required',
    		'password.required' => 'Password is required'
    	]);

    	if ( $validate->fails() ) 
		{
		    return response()->json([ 'msg' => $validate->errors(), 'status' => false ], 422);
		}

		if ( !\Auth::attempt(['email' => $request->email, 'password' => $request->password]) ) {
			
			return response()->json([ 'msg' => 'Login credentials does not match with our record.', 'status' => false], 401);
		}

		$user = \App\Models\User::where('email', $request->email)->first();

        Cart::where('ip', $request->ip())->update([ 'user_id' => $user->id ]);        

		return response()->json([ 'status' => true, 'msg' => 'User Logged In Successfully', 'token' => $user->createToken("Login Token")->plainTextToken ], 200);

    }

    public function getUsers()
    {
    	try{

    		$user = \Auth::user();

    		return response()->json($user, 200);

    	}catch( \Exception $e){

    		return response()->json([ 'msg' => 'Something Went Wrong', 'status' => false], 500);
    	}
    }

    public function logout(Request $request)
    {
        try{

            \Auth::user()->tokens()->delete();

            return response()->json([ 'msg' => 'Logout Successfully Done', 'status' => true], 200);

        }catch( \Exception $e){

            return response()->json([ 'msg' => 'Something Went Wrong', 'status' => false], 500);
        }
    }

    public function myOrders(Request $request)
    {
        $orders = Order::with(['product'])->where('user_id', \Auth::user()->id)->orderBy('created_at', 'DESC')->get();

        $total = $orders->count();

        $data = [];

        try{

            if ( !$orders->isEmpty() ) {
                
                foreach ($orders as $ke => $order) {

                    $item = [];
                    $item['name'] = $order->product_name;
                    $item['image'] = $order->getProductImageUrl();
                    $item['price'] = $order->price;
                    $item['total_product'] = $order->total_product;
                    $item['address'] = $order->address;
                    $item['user_name'] = $order->name;
                    $item['city'] = $order->city;
                    $item['pincode'] = $order->pincode;
                    $item['phone'] = $order->phone;

                    if ( $order->status == 1) {
                        
                        $item['status'] = 'Placed';

                    }elseif ( $order->status == 2) {
                        
                        $item['status'] = 'Delivered';

                    }elseif ( $order->status == 3) {
                        
                        $item['status'] = 'Dispatch';

                    }else{

                        $item['status'] = 'Failed';

                    }

                    $item['created_at'] = date('d-m-Y',strtotime($order->created_at));

                    $data[] = $item;
                }
            }

            return response()->json([ 'data' => $data, 'total' => $total ,'status' => true], 200);


        }catch(\Exception $e){

            return response()->json([ 'msg' => 'Something went wrong', 'total' => $total ,'status' => false ], 500);
        }
    }

    public function myInfo(Request $request)
    {
        try{

            $user = \Auth::user();

            $data = [];
            $data['personal_details']['name'] = $user->name;
            $data['personal_details']['email'] = $user->email;
            $data['personal_details']['address'] = $user->address;
            $data['personal_details']['pincode'] = $user->pincode;
            $data['personal_details']['city'] = $user->city;

            if ( $user->card_number != null ) {
                
                $data['card_details']['card_number'] = $user->card_number;
                $data['card_details']['card_name'] = $user->card_name;
                $data['card_details']['expiry_month'] = $user->expiry_month;
                $data['card_details']['expiry_year'] = $user->expiry_year;

            }else{

                $data['card_details'] = null;
            }

            return response()->json([ 'data' => $data, 'status' => true ], 200);

        }catch(\Exception $e){

            return response()->json([ 'msg' => 'Something went wrong' ,'status' => false ], 500);   
        }
    }

    public function saveMyInfo(Request $request)
    {
        $validate = \Validator::make( $request->all(),[

            'name' => 'required',

        ],[

            'name.required' => 'Display Name is required',
        ]);

        if ( $validate->fails() ) 
        {
            return response()->json([ 'msg' => $validate->errors(), 'status' => false ], 422);
        }

        try{

            $user = \Auth::user();
            $user->name = $request->name;
            $user->address = $request->address;
            $user->city = $request->city;
            $user->pincode = $request->pincode;
            $user->save();


            $msg = 'User Info saved successfully';
            return response()->json([ 'msg' => $msg, 'status' => true ]);

        }catch( \Exception $e ){

            return response()->json([ 'msg' => 'Something Went Wrong', 'status' => false], 500);

        }
    }

    public function updatePassword(Request $request)
    {
        $validate = \Validator::make( $request->all(),[

            'current_password' => 'required|current_password',
            'new_password' => 'required|confirm_password:'.$request->confirm_password.'',
            'confirm_password' => 'required',

        ],[

            'current_password.required' => 'Current Password is required',
            'current_password.current_password' => 'Current Password is incorrect',
            'new_password.required' => 'New Password is required',
            'confirm_password.required' => 'Confirm Password is required',
            'new_password.confirm_password' => 'Password did not matched',
        ]);

        if ( $validate->fails() ) 
        {
            return response()->json([ 'msg' => $validate->errors(), 'status' => false ], 422);
        }

        try{

            $user = \Auth::user();
            $user->password = \Hash::make($request->new_password);
            $user->save();


            $msg = 'User Info saved successfully';
            return response()->json([ 'msg' => $msg, 'status' => true ]);

        }catch( \Exception $e ){

            return response()->json([ 'msg' => 'Something Went Wrong', 'status' => false], 500);

        }
    }

    public function deleteCard()
    {
        try{

            $user = \Auth::user();
            $user->card_name = null;
            $user->card_number = null;
            $user->expiry_year = null;
            $user->expiry_month = null;
            $user->cvv = null;
            $user->save();

            $msg = 'Card Removed successfully';
            return response()->json([ 'msg' => $msg, 'status' => true ]);

        }catch( \Exception $e ){

            return response()->json([ 'msg' => 'Something Went Wrong', 'status' => false ], 500);
        }
    }

    public function saveCardInfo(Request $request)
    {

        $validate = \Validator::make( $request->all(),[

            'card_name' => 'required',
            'card_number' => 'required',
            'expiry_year' => 'required',
            'expiry_month' => 'required',

        ],[

            'card_name.required' => 'Card Holder Name is required',
            'card_number.required' => 'Card Number is required',
            'expiry_month.required' => 'Expiry Month is required',
            'expiry_year.required' => 'Expiry Year is required',
        ]);

        if ( $validate->fails() ) 
        {
            return response()->json([ 'msg' => $validate->errors(), 'status' => false ], 422);
        }

        try{

            $user = \Auth::user();
            $user->card_name = $request->card_name;
            $user->card_number = $request->card_number;
            $user->expiry_year = $request->expiry_year;
            $user->expiry_month = $request->expiry_month;
            $user->save();

            $msg = 'Card Info saved successfully';
            return response()->json([ 'msg' => $msg, 'status' => true ]);

        }catch( \Exception $e ){

            return response()->json([ 'msg' => 'Something Went Wrong', 'status' => false ], 500);
        }
        
    }
}
