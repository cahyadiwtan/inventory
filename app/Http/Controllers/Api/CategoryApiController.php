<?php

namespace App\Http\Controllers\Api;

use App\Models\ProductCategory;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Eloquent\GenericRepository;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryApiController extends MasterApiController
{
    public function __construct(ActivityLogService $activityLog)
    {
        $repository = new GenericRepository(new ProductCategory());
        parent::__construct($repository, $activityLog);
    }

    protected function rules(): array
    {
        $id = $this->route('id');

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('product_categories', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function label(): string
    {
        return 'category';
    }
}
