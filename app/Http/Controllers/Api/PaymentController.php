<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use Stripe\Exception\CardException;
use Stripe\StripeClient;

class PaymentController extends Controller
{
    protected $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
        $this->cart = new Cart;
    }

    public function placeOrder(Request $request)
    {
    	$validate = \Validator::make( $request->all(),[

    		'name' => 'required',
            'address' => 'required',
            'city' => 'required',
            'pincode' => 'required',
            'phone' => 'required',
            'card_name' => 'required',
            'card_number' => 'required',
            'expiry_month' => 'required',
            'expiry_year' => 'required',
            'cvv' => 'required',

    	],[

    		'name.required' => 'Name is required',
            'address.required' => 'Address is required',
            'city.required' => 'City is required',
            'pincode.required' => 'Pincode is required',
            'phone.required' => 'Phone is required',
            'card_name.required' => 'Card Holder Name is required',
            'card_number.required' => 'Card Number is required',
            'expiry_month.required' => 'Expiry Month is required',
            'expiry_year.required' => 'Expiry Year is required',
            'cvv.required' => 'Cvv is required',
    	]);

    	if ( $validate->fails() ) 
		{
		    return response()->json([ 'msg' => $validate->errors(), 'status' => false ], 422);
		} 

        $payment_id = $this->generatePaymentMethod($request);

        if (!empty($payment_id['error']) ) {

            return response()->json([ 'msg' => $payment_id['error'], 'status' => false ], 500);
        }

        if ( empty($payment_id['id']) ) {

            return response()->json([ 'msg' => 'Something went wrong, Try again later', 'status' => false ], 500);
        }


        $customer = $this->generateCustomer($payment_id['id'], $request);

        if (!empty($customer['error']) ) {

            return response()->json([ 'msg' => $customer['error'], 'status' => false ], 500);
        }

        if ( empty($customer['id']) ) {

            return response()->json([ 'msg' => 'Something went wrong, Try again later', 'status' => false ], 500);
        }
        
        try{

            $amount = $this->cart->getCartTotalAmount();

            $charge = $this->makePayment($payment_id['id'], $customer['id'], $amount);

            if ( $charge['id'] ) {
                
                $carts = $this->cart->getCartItems();

                foreach ($carts as $ke => $item) {
                    
                    $order = new Order;
                    $order->name = $request->name;
                    $order->address = $request->address;
                    $order->city = $request->city;
                    $order->pincode = $request->pincode;
                    $order->phone = $request->phone;
                    $order->user_id = \Auth::user()->id;
                    $order->product_id = $item->product_id;
                    $order->total_product = $item->total_product;
                    $order->price = $item->product->sell_price;
                    $order->product_name = $item->product->name;
                    $order->image = $item->product->image;
                    $order->status = 1;
                    $order->save();

                    $item->delete();
                }

                return response()->json([ 'msg' => 'Your Order Placed successfully'], 200);
            }

        }catch(\Exception $e){

            return response()->json([ 'msg' => 'Oops! Payment Failed, Try again', 'status' => false ], 500);

        }

    	return response()->json(['msg' => 'Payment Faild!', 'status' => false ], 500);
    }

    private function generatePaymentMethod($request)
    {
        $token = null;

        try {

            $token = $this->stripe->paymentMethods->create([
                        'type' => 'card',
                        'card' => [
                            'number' => $request->card_number,
                            'exp_month' => $request->expiry_month,
                            'exp_year' => $request->expiry_year,
                            'cvc' => $request->cvv,
                        ],
                    ]);

        } catch (CardException $e) {

            $token['error'] = $e->getError()->message;

        } catch (\Exception $e) {

            $token['error'] = $e->getMessage();

        }

        return $token;
    }

    private function generateCustomer($payment_id, $request)
    {
        $customer = null;

        try {

           $customer = $this->stripe->customers->create([
                'description' => 'Customer Info',
                // "source" => $request->stripeToken,
                "payment_method" => $payment_id,
                "email" => \Auth::user()->email,
                "name" => $request->card_name,
                'address' => ['city' => $request->city, 'country' => 'US', 'line1' => $request->address, 'postal_code' => $request->pincode, "line2" => "", 'state' => 'New York']

            ]);

        } catch (CardException $e) {

            $customer['error'] = $e->getError()->message;

        } catch (\Exception $e) {

            $customer['error'] = $e->getMessage();

        }

        return $customer;
    }

    private function makePayment($payment_id, $customer_id , $amount)
    {
        $payment = null;

        try {

            $payment = $this->stripe->paymentIntents->create([
                'amount' => 100,
                'currency' => 'usd',
                'payment_method_types' => ['card'],
                'description' => 'Payment for export products',
                'customer' => $customer_id,
                'payment_method' => $payment_id,
                'off_session' => true,
                'confirm' => true
                
            ]);

        } catch (CardException $e) {

            $payment['error'] = $e->getError()->message;

        } catch (\Exception $e) {

            $payment['error'] = $e->getMessage();

        }

        return $payment;
    }

}
