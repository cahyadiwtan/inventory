<?php

namespace App\Http\Requests\Master;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TaxRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $id = $this->route('tax');

        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('taxes', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:100'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
