<?php

namespace Dkvhin\LaravelModelSettings;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ModelHasSetting extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table =  config('model_settings.table')  ?: parent::getTable();
    }

    protected $fillable = [
        'payload',
        'name',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
