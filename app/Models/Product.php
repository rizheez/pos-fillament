<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'purchase_price', 'sale_price', 'stock', 'category_id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }
    public function getFormattedPurchasePriceAttribute()
    {
        return number_format($this->purchase_price, 2);
    }
    public function getFormattedSalePriceAttribute()
    {
        return number_format($this->sale_price, 2);
    }
}
