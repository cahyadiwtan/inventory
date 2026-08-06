<?php

namespace App\Http\Controllers\Api;

use App\Models\Unit;
use App\Repositories\Eloquent\GenericRepository;
use App\Services\ActivityLogService;
use Illuminate\Validation\Rule;

class UnitApiController extends MasterApiController
{
    public function __construct(ActivityLogService $activityLog)
    {
        parent::__construct(new GenericRepository(new Unit()), $activityLog);
    }

    protected function rules(): array
    {
        $id = $this->route('id');

        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('units', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:50'],
            'symbol' => ['required', 'string', 'max:10'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function label(): string
    {
        return 'unit';
    }
}
