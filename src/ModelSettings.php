<?php

namespace Dkvhin\LaravelModelSettings;

abstract class ModelSettings
{
    /**
     * @var \Dkvhin\LaravelModelSettings\HasSettings
     */
    private mixed $model;
    abstract public static function group(): string;

    /**
     * @param \Dkvhin\LaravelModelSettings\HasSettings $model
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
