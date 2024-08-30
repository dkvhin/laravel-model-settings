<?php

use HasSettings;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\Authenticated;
use Dkvhin\LaravelModelSettings\Exceptions\CouldNotUnserializeModelSettings;

class AuthenticatedUserListener
{

    /**
     * Handle the event.
     */
    public function handle(Authenticated $event): void
    {
        // check if the current authenticated user has a ModelSettingTrait
        // if logged-in user doesn't implement the has setting trait, then no need to auto-load the settings classes
        if (!$event->user instanceof HasSettings) {
            return;
        }

        /**
         * @var HasSettings
         */
        $user = $event->user;

        collect($user->getAutoLoadSettingClasses())
            ->unique()
            ->each(function (string $settingClass) use ($user) {
                App::scoped($settingClass, function () use ($user, $settingClass) {
                    try {
                        return $user->settings($settingClass);
                    } catch (CouldNotUnserializeModelSettings $exception) {
                        Log::error("Could not unserialize model settings class: `{$settingClass}` from cache");
                    }
                });
            });
    }
}
