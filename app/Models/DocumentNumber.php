<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class DocumentNumber extends Model
{
    use UsesUuid;

    protected $fillable = ['prefix', 'period', 'last_number'];

    protected function casts(): array
    {
        return [
            'last_number' => 'integer',
        ];
    }
}
