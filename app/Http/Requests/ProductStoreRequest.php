<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ProductStoreRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()
            ->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'          => 'required|string|max:255',
            'category_id'   => 'required|integer|exists:product_categories,id',
            'custom_unit'   => 'required|boolean',
            'description'   => 'string',
            'image'         => 'image',
            'price'         => 'required|numeric|min:0|max:999999',
            'quantity'      => 'required|integer|min:0|max:999999',
            'type_id'       => 'required|integer|exists:product_types,id',
            'unit_id'       => 'exists:units,id',
            'unit_label'    => 'required_without:unit_id',
            'unit_name'     => 'required_without:unit_id',
            'vendor_id'     => 'required|integer|exists:vendors,id'
        ];
    }
}
