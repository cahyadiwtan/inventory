<?php

namespace App\Http\Controllers\Api;

use App\Models\Tax;
use App\Repositories\Eloquent\GenericRepository;
use App\Services\ActivityLogService;
use Illuminate\Validation\Rule;

class TaxApiController extends MasterApiController
{
    public function __construct(ActivityLogService $activityLog)
    {
        parent::__construct(new GenericRepository(new Tax()), $activityLog);
    }

    protected function rules(): array
    {
        $id = $this->route('id');

        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('taxes', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:100'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function label(): string
    {
        return 'tax';
    }
}
