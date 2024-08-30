<?php

namespace Dkvhin\LaravelModelSettings;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface HasSettings
{
    public function settings($abstract): mixed;
    public function saveSettings(ModelSettings $settings): void;
    public function _settings(): MorphMany;
    public function getAutoLoadSettingClasses(): array;
}
