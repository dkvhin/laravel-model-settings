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
     * @return array<class-string>
     */
    public function getAutoLoadSettingClasses(): array
    {
        if (isset($this->autoLoadSettingClasses)) {
            return $this->autoLoadSettingClasses;
        }

        return [];
    }

    /**
     * @template TClass of \Dkvhin\LaravelModelSettings\ModelSettings
     * @param  TClass $settings
     * @return TClass
     */
    private function getCacheKey($settings): string
    {
        return 'model_settings_' .  md5(self::class . '_' . $this->id . '_' . $settings::group());
    }

    /**
     * @var array<mixed>
     */
    private array $loadedSettings = [];

    /**
     * @template TClass of \Dkvhin\LaravelModelSettings\ModelSettings
     * @param  class-string<TClass>
     * @return TClass
     */
    public function settings($abstract): mixed
    {
        // check if the settings is already loaded in memory
        if (isset($this->loadedSettings[$abstract])) {
            return $this->loadedSettings[$abstract];
        }

        $cacheKey = $this->getCacheKey($abstract);
        if (config('model_settings.cache.enabled') &&  Cache::has($cacheKey)) {
            /**
             * @var \Dkvhin\LaravelModelSettings\ModelSettings
             */
            $result = unserialize(Cache::get($cacheKey));

            if (!$result instanceof ModelSettings) {
                throw new CouldNotUnserializeModelSettings();
            }

            $result->setModel($this);
            $this->loadedSettings[$abstract] = $result;
            return $result;
        }

        $setting = $this->_settings()->where([
            'name'  => $abstract::group()
        ])->first();

        $new = new $abstract();
        // add null values to the properies via reflection
        $new = $this->populateFields($new);

        if ($setting != null && $setting->payload != null) {
            $payload = json_decode($setting->payload);
            foreach ($payload as $key => $value) {
                $new->{$key} = $value;
            }
        }

        $this->loadedSettings[$abstract] = $new;
        $this->saveCache($new);

        $new->setModel($this);
        return $new;
    }

    /**
     * @template TClass of \Dkvhin\LaravelModelSettings\ModelSettings
     * @param  TClass $settings
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

        $this->saveCache($settings);
    }

    /**
     * @template TClass of \Dkvhin\LaravelModelSettings\ModelSettings
     * @param  TClass $settings
     * @return TClass
     */
    private function saveCache($settings): void
    {
        if (config('model_settings.cache.enabled')) {
            $cacheKey = $this->getCacheKey($settings);
            Cache::forever($cacheKey, serialize($settings));
        }
    }

    /**
     * @template TClass of \Dkvhin\LaravelModelSettings\ModelSettings
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


    public function _settings(): MorphMany
    {
        return $this->morphMany(ModelHasSetting::class, 'model');
    }
}
