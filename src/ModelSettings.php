<?php

namespace Dkvhin\LaravelModelSettings;

use Dkvhin\LaravelModelSettings\Exceptions\MissingModelException;

abstract class ModelSettings
{
    /**
     * @var \Dkvhin\LaravelModelSettings\HasSettings
     */
    private mixed $model = null;

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
        if ($this->model != null) {
            $this->model->saveSettings($this);
        }

        throw new MissingModelException();
    }
}
