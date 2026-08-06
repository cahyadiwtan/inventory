<?php

namespace App\Http\Controllers\Api;

use App\Models\ProductBrand;
use App\Repositories\Eloquent\GenericRepository;
use App\Services\ActivityLogService;
use Illuminate\Validation\Rule;

class BrandApiController extends MasterApiController
{
    public function __construct(ActivityLogService $activityLog)
    {
        parent::__construct(new GenericRepository(new ProductBrand()), $activityLog);
    }

    protected function rules(): array
    {
        $id = $this->route('id');

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('product_brands', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function label(): string
    {
        return 'brand';
    }
}
