<?php

namespace App\Http\Requests\Master;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class WarehouseRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $id = $this->route('warehouse');

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('warehouses', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
