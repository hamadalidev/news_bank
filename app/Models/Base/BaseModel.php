<?php

namespace App\Models\Base;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

abstract class BaseModel extends Model
{
    /**
     * Boot the model and set up event listeners.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (Auth::check()) {
                $userId = Auth::id();

                if (in_array('created_by', $model->getFillable())) {
                    $model->created_by = $userId;
                }

                if (in_array('updated_by', $model->getFillable())) {
                    $model->updated_by = $userId;
                }
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $userId = Auth::id();

                if (in_array('updated_by', $model->getFillable())) {
                    $model->updated_by = $userId;
                }
            }
        });
    }

    /**
     * Get the user who created this record.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this record.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
