<?php

namespace Dkvhin\LaravelModelSettings;

use ReflectionClass;
use ReflectionProperty;
use Illuminate\Support\Facades\Cache;
use Dkvhin\LaravelModelSettings\ModelSettings;
use Dkvhin\LaravelModelSettings\ModelHasSetting;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Dkvhin\LaravelModelSettings\Exceptions\CouldNotUnserializeModelSettings;

trait HasSettingsTrait
{
    /**
     * @var array<class-string>
     */
    protected array $autoLoadSettingClasses = [];

    /**
     * @return array<class-string>
     */
    public function getAutoLoadSettingClasses(): array
    {
        return $this->autoLoadSettingClasses;
    }

    /**
     * @var array<mixed>
     */
    private array $loadedSettings = [];

    /**
     * @template TClass of \App\Settings\Models\ModelSettings
     * @param  class-string<TClass> $abstract
     * @return TClass
     */
    public function settings($abstract): mixed
    {
        // check if the settings is already loaded in memory
        if (isset($this->loadedSettings[$abstract])) {
            return $this->loadedSettings[$abstract];
        }

        $cacheKey = 'model_settings_' .  md5(self::class . '_' . $this->id . '_' . $abstract::group());

        if (config('model_settings.cache.enabled') &&  Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $setting = $this->_settings()->where([
            'name'  => $abstract::group()
        ])->first();

        $new = new $abstract();
        // add null values to the properies via reflection
        $new = $this->populateFields($new);

        if ($setting != null) {
            $payload = unserialize($setting->payload);

            if (! $payload instanceof ModelSettings) {
                throw new CouldNotUnserializeModelSettings();
            }

            foreach ($payload as $key => $value) {
                $new->{$key} = $value;
            }
        }

        $this->loadedSettings[$abstract] = $new;
        if (config('model_settings.cache.enabled')) {
            Cache::forever($cacheKey, serialize($new));
        }

        $new->setModel($this);
        return $new;
    }

    /**
     * @template TClass of \App\Settings\Models\ModelSettings
     * @param  TClass $object
     * @return TClass
     */
    public function saveSettings(ModelSettings $settings): void
    {
        $setting = $this->_settings()->where([
            'name'  => $settings::group()
        ])->first();

        $payLoad = json_encode($settings);

        if ($setting == null) {
            $this->_settings()->create([
                'name'        => $settings::group(),
                'payload'     =>  $payLoad
            ]);
        } else {
            $setting->payload = $payLoad;
            $setting->save();
        }
    }

    /**
     * @template TClass of \App\Settings\Models\ModelSettings
     * @param  TClass $object
     * @return TClass
     */
    private function populateFields(mixed $object): mixed
    {
        $reflect = new ReflectionClass($object);
        $props   = $reflect->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);

        foreach ($props as $prop) {
            $value = null;
            switch ($prop->getType()->getName()) {
                case 'string':
                    $value = '';
                    break;
            }
            $prop->setValue($object, $value);
        }

        return $object;
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\App\Models\ModelHasSetting>
     */
    public function _settings(): MorphMany
    {
        return $this->morphMany(ModelHasSetting::class, 'model');
    }
}
