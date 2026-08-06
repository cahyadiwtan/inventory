<?php

namespace App\Http\Requests\Master;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class UnitRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $id = $this->route('unit');

        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('units', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:50'],
            'symbol' => ['required', 'string', 'max:10'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
