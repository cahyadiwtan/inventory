<?php

namespace App\Models;

class Approval extends BaseModel
{
    protected $fillable = ['approvable_type', 'approvable_id', 'approver_id', 'action', 'notes'];

    public function approvable()
    {
        return $this->morphTo();
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
