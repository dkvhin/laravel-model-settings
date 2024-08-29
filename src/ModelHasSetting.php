<?php

namespace Dkvhin\LaravelModelSettings;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ModelHasSetting extends Model
{
    protected $table = config('model_settings.table');

    protected $fillable = [
        'payload',
        'name',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
