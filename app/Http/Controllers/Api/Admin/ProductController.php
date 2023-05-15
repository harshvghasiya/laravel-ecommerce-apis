<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductTag;
use App\Models\ProductCategory;
use App\Models\Category;
use App\Models\ProductDetails;
use App\Models\ProductImages;
use App\Models\Tag;

class ProductController extends Controller
{
    
    public function productStore(Request $request)
    {
        $validate = \Validator::make( $request->all(),[

            'name' => 'required',
            // 'description' => 'required',
            'orignal_price' => 'required',
            'sell_price' => 'required',
            'category' => 'required',
            'tag' => 'required',
            'slug' => 'required',
            // 'discount' => 'required',

        ],[

            'name.required' => 'Product Name is required',
            'orignal_price.required' => 'Orignal Price is required',
            'sell_price.required' => 'Sell Price is required',
            'category.required' => 'Category is required',
            'tag.required' => 'Tag is required',
        ]);

        if ( $validate->fails() ) 
        {
            return response()->json([ 'msg' => $validate->errors(), 'status' => false ], 422);
        }

        $res = new Product;
        $res->name = $request->name;
        $res->status = $request->status;
        $res->description = $request->description;
        $res->orignal_price = $request->orignal_price;
        $res->sell_price = $request->sell_price;
        $res->slug = $request->slug;
        $res->discount = $request->discount;

            if (isset($request->image) && !empty($request->image)) {

                $imageName = uploadFile($request, 'image', productUploadPath());

                if ($imageName != "") {
                    $res->image = $imageName;
                }
            }

        $res->save();

        if ( $request->tag != null ) {
            
            ProductTag::where('product_id', $res->id)->delete();

            foreach ($request->tag as $tag_k => $tag_v) {
                
                $tag = new ProductTag;
                $tag->product_id = $res->id;
                $tag->tag_id = $tag_v;
                $tag->save();
            }
        }

        if ( $request->category != null ) {
            
            ProductCategory::where('product_id', $res->id)->delete();

            foreach ($request->category as $category_k => $category_v) {
                
                $category = new Productcategory;
                $category->product_id = $res->id;
                $category->category_id = $category_v;
                $category->save();
            }
        }

        if ( $request->extra_detail != null) {
            
            foreach ($request->extra_detail as $ke => $detail) {
                
                $edetail = new ProductDetails;
                $edetail->product_id = $res->id;
                $edetail->detail_key = $detail['detail_key'];
                $edetail->detail_value = $detail['detail_value'];
                $edetail->save();
            }
        }

        if ( $request->extra_images != null) {
            
            foreach ($request->extra_images as $ike => $ival) {
                
                $eimages = new ProductImages;
                $eimages->product_id = $res->id;

                if (isset($ival) && $ival != "" ) {


                    $name = time() . '' . $ival->getClientOriginalName();

                    $ival->move(productUploadPath(), time() . '' . $ival->getClientOriginalName());

                    $eimages->image = $name;

                }
                $eimages->save();
            }
        }

        $msg = 'Product Saved Successfully';

        return response()->json([ 'msg' => $msg, 'status' => true], 200);

    }

    public function categoryStore(Request $request)
    {
        
        $validate = \Validator::make( $request->all(),[

            'name' => 'required',

        ],[

            'name.required' => 'Category Name is required',
        
        ]);

        if ( $validate->fails() ) 
        {
            return response()->json([ 'msg' => $validate->errors(), 'status' => false ], 422);
        }

        $res = new Category;
        $res->name = $request->name;
        $res->status = $request->status;
        $res->save();

        $msg = 'Category saved successfully';
        return response()->json([ 'msg' => $msg, 'status' => true], 200);
    }

    public function tagStore(Request $request)
    {
        
        $validate = \Validator::make( $request->all(),[

            'name' => 'required',

        ],[

            'name.required' => 'Tag Name is required',
        
        ]);

        if ( $validate->fails() ) 
        {
            return response()->json([ 'msg' => $validate->errors(), 'status' => false ], 422);
        }

        $res = new Tag;
        $res->name = $request->name;
        $res->status = $request->status;
        $res->save();

        $msg = 'Tag saved successfully';
        return response()->json([ 'msg' => $msg, 'status' => true], 200);
    }
}
