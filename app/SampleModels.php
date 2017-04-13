<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{

    /**
     * Define attributes that are mass-assignable
     *
     * @var array
     */
    public $fillable = ['path', 'imageable_id', 'imageable_type'];

    /**
     * Define polymorphic relationship
     *  - this allows us to associate multiple images with any model and store the all the urls
     *    in a single table
     *
     * @see \App\Product
     * @see \App\Package
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function imageable()
    {
        return $this->morphTo();
    }

    /**
     * Helper function to transform image path using base path of app's filesystem
     *
     * @see \config\filesystems.php
     *
     * @return string
     */
    public function getUrl()
    {
        return Storage::url($this->path);
    }
}

class Order extends Model
{
    const SHIPPING_DOMESTIC = 18.00;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'billing_first_name',
        'billing_last_name',
        'billing_email',
        'billing_address1',
        'billing_address2',
        'billing_phone',
        'billing_company',
        'billing_country',
        'billing_city',
        'billing_zip'
    ];

    /**
     * Define relationship between Order and Coupon
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_code', 'code');
    }

    /**
     * Mutate cost attribute to 2-digit floating point
     *
     * @param $value
     *
     * @return string
     */
    public function getCostAttribute($value)
    {
        return number_format($value, 2);
    }

    /**
     * Mutate discount attribute to 2-digit floating point
     *
     * @param $value
     *
     * @return string
     */
    public function getDiscountAttribute($value)
    {
        return number_format($value, 2);
    }

    /**
     * Mutate total attribute to 2-digit floating point
     *
     * @param $value
     *
     * @return string
     */
    public function getTotalAttribute($value)
    {
        return number_format($value, 2);
    }

    /**
     * Returns product in order with the specified id, if one exists
     *
     * @param $productId
     *
     * @return mixed
     */
    public function product($productId)
    {
        return $this->belongsToMany(Product::class)
            ->wherePivot('product_id', $productId)
            ->withPivot('quantity')
            ->first();
    }

    /**
     * Define relationship to Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('quantity');
    }

    /**
     * Defines relationship between User and Order
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

class Product extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'category_id',
        'type_id',
        'vendor_id'
    ];

    /**
     * Define relationship to ProductCategory
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    /**
     * Define polymorphic relationship to images
     *  - polymorphism allows us to store all of the images for various models in a single table
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    /**
     * Define relationship to Inventory
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Define relationship to Packages
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function packages()
    {
        return $this->hasMany(Package::class);
    }

    /**
     * Define relationship to ProductType
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {
        return $this->belongsTo(ProductType::class);
    }

    /**
     * Define relationship to Vendor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
