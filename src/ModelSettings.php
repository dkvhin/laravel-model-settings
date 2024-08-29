<?php

namespace Dkvhin\LaravelModelSettings;

abstract class ModelSettings
{
    /**
     * @var \App\Traits\HasSettingsTrait
     */
    private mixed $model;
    abstract public static function group(): string;

    /**
     * @param \App\Traits\HasSettingsTrait $model
     */
    public function setModel($model): void
    {
        $this->model = $model;
    }

    public function save(): void
    {
        $this->model->saveSettings($this);
    }
}
