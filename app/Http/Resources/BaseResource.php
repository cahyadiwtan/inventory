<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

abstract class BaseResource extends JsonResource
{
    /**
     * Wrap resource in "data" key.
     */
    public static $wrap = 'data';

    /**
     * Common metadata included in every resource.
     */
    protected function meta(): array
    {
        return [
            'created_at' => $this->when($this->resource->created_at ?? null, fn () => $this->resource->created_at?->toDateTimeString()),
            'updated_at' => $this->when($this->resource->updated_at ?? null, fn () => $this->resource->updated_at?->toDateTimeString()),
        ];
    }
}
