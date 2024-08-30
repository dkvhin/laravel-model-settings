<?php

namespace Dkvhin\LaravelModelSettings;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface HasSettings
{
    /**
     * @template TClass of \Dkvhin\LaravelModelSettings\ModelSettings
     * @param  class-string<TClass>|\Dkvhin\LaravelModelSettings\ModelSettings $abstract
     * @return TClass
     */
    public function settings($abstract): mixed;

    /**
     * @template TClass of \Dkvhin\LaravelModelSettings\ModelSettings
     * @param  TClass $settings
     */
    public function saveSettings(ModelSettings $settings): void;


    public function _settings(): MorphMany;

    /**
     * @return array<class-string>
     */
    public function getAutoLoadSettingClasses(): array;
}
