<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public function product()
    {
    	return $this->belongsTo('\App\Models\Product', 'product_id', 'id');
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
