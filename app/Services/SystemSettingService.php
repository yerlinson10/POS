<?php

namespace App\Services;

use App\Models\User;
use App\Models\SystemSetting;
use App\Models\UserSystemSetting;
use Illuminate\Support\Facades\Auth;

class SystemSettingService
{
    /** @var User|null */
    protected $user = null;

    /**
     * Constructor opcionalmente acepta un usuario o userId.
     * @param User|string|null $userId
     */
    public function __construct(User|string|null $userId = null)
    {
        if ($userId !== null) {
            $this->forUser($userId);
        } elseif (Auth::check()) {
            $this->user = Auth::user();
        }
    }

    /**
     * Sets the user context for the service.
     * @param User|string $userId
     *
     * @return $this
     */
    public function forUser(User|string $userId)
    {
        $user = null;

        if (is_string($userId) || is_numeric($userId)) {
            $user = User::find($userId);
            if (!$user) {
                throw new \InvalidArgumentException("User with ID {$userId} not found.");
            }
        } elseif ($userId instanceof User) {
            $user = $userId;
        } else {
            throw new \InvalidArgumentException("Invalid user identifier provided.");
        }

        $this->user = $user;

        return $this;
    }

    /**
     * Retrieves the value of a system setting by its key.
     *
     * @param string $key The key of the system setting to retrieve.
     * @param mixed $default The default value to return if the setting is not found.
     * @return mixed The value of the setting, a user-specific value, a default option, the table default, or the provided default.
     */
    public function get($key, $default = null)
    {
        $setting = SystemSetting::where('key', $key)->first();
        if (!$setting)
            return $default;

        // Si hay usuario definido, buscar valor personalizado
        if ($this->user) {
            $userValue = $setting->userValues()->where('user_id', $this->user->id)->first();
            if ($userValue)
                return $userValue->value;
        }

        // Si hay opciones, devolver la opción por defecto
        if ($setting->options()->count() > 0) {
            $defaultOption = $setting->options()->where('default', true)->first();
            if ($defaultOption)
                return $defaultOption->value;
        }

        // Si hay valor por defecto en la tabla
        return $setting->default ?? $default;
    }

    public function set($key, $value)
    {
        $setting = SystemSetting::where('key', $key)->first();
        if (!$setting)
            return null;

        if (!$this->user) {
            throw new \RuntimeException('No user defined for SystemSettingService.');
        }
        $userId = $this->user->id;

        return UserSystemSetting::updateOrCreate(
            [
                'user_id' => $userId,
                'system_setting_id' => $setting->id
            ],
            [
                'value' => $value
            ]
        );
    }

    public function all()
    {
        return SystemSetting::with(['options'])->get();
    }
}
