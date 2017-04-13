<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
     * Override parent save method so that order's cost, discount, and total attributes are updated when it is saved
     *
     * @param array $options
     *
     * @return mixed
     */
    public function save(array $options = [])
    {
        $this->load('products');

        $this->updateCost();
        $this->updateDiscount();
        $this->updateTotal();

        return parent::save($options);
    }

    /**
     * Updates the cost of the order before shipping and coupons
     */
    private function updateCost()
    {
        $subtotal = 0;

        foreach($this->products as $product) {
            $subtotal += $product->pivot->quantity * $product->price;
        }

        $this->cost = $subtotal;
    }

    /**
     * Updates the discount from coupon
     */
    private function updateDiscount()
    {
        $discount = 0;

        if ($this->coupon) {
            $expiry = Carbon::parse($this->coupon->expires_at);

            if (!$expiry->isPast()) {
                $discount = $this->coupon->type === 'percent'
                    ? $this->coupon->discount * .01 * $this->cost
                    : $this->coupon->discount;
            }
        }

        $this->discount = $discount;
    }

    /**
     * Updates the total cost after shipping and coupon discount
     */
    private function updateTotal()
    {
        $total = $this->cost + self::SHIPPING_DOMESTIC;
        $total -= $this->discount;

        $this->total = $total;
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

