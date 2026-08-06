<?php

namespace App\Http\Controllers\Api;

use App\Models\Warehouse;
use App\Repositories\Eloquent\GenericRepository;
use App\Services\ActivityLogService;
use Illuminate\Validation\Rule;

class WarehouseApiController extends MasterApiController
{
    public function __construct(ActivityLogService $activityLog)
    {
        parent::__construct(new GenericRepository(new Warehouse()), $activityLog);
    }

    protected function rules(): array
    {
        $id = $this->route('id');

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('warehouses', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function label(): string
    {
        return 'warehouse';
    }
}
