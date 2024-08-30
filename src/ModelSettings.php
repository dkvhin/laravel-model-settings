<?php

namespace Dkvhin\LaravelModelSettings;

use Dkvhin\LaravelModelSettings\Exceptions\MissingModelException;

abstract class ModelSettings
{
    /**
     * @var \Dkvhin\LaravelModelSettings\HasSettings
     */
    private mixed $modelSetting = null;

    abstract public static function group(): string;

    /**
     * @param \Dkvhin\LaravelModelSettings\HasSettings $model
     */
    public function setModel($model): void
    {
        $this->modelSetting = $model;
    }

    public function save(): void
    {
        if ($this->modelSetting == null) {
            throw new MissingModelException();
        }
        $this->modelSetting->saveSettings($this);
    }
}
