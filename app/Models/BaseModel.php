<?php

namespace App\Models;

use App\Models\Concerns\HasActivityLog;
use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class BaseModel extends Model
{
    use HasActivityLog, HasFactory, SoftDeletes, UsesUuid;
}
