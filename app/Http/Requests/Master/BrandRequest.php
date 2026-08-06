<?php

namespace App\Http\Requests\Master;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class BrandRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $id = $this->route('brand');

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('product_brands', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
