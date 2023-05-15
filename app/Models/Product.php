<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tag;
use App\Models\Category;
use App\Models\ProductDetails;
use App\Models\ProductImages;

class Product extends Model
{
    use HasFactory;

    public function tag()
    {
    	return $this->belongsToMany(Tag::class, 'product_tags');
    }

    public function category()
    {
    	return $this->belongsToMany(Category::class, 'product_categories');
    }

    public function details()
    {
        return $this->hasMany(ProductDetails::class, 'product_id', 'id');
    }

    public function images()
    {
        return $this->hasMany(ProductImages::class, 'product_id', 'id');
    }

    public function getProductImageUrl()
    {
        $imageUrl_u = noImageUrl();
        $imagePath = productUploadPath() . $this->image;
        $imageUrl = productUploadUrl() . $this->image;

        if (isset($this->image) && !empty($this->image) && file_exists($imagePath)) {

            return $imageUrl;

        } else {

            $imageUrl = $imageUrl_u;
        }
        
        return $imageUrl;
    }
}
