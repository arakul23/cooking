<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Translation extends Model
{
    protected $guarded = [
        'id'
    ];
    public function translatable(): MorphTo
    {
        return $this->morphTo();
    }
}
