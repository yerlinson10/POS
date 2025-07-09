<?php

namespace App\Services;

use App\Models\SystemSetting;
use App\Models\UserSystemSetting;
use Illuminate\Support\Facades\Auth;

class SystemSettingService
{
    public function get($key, $default = null, $userId = null)
    {
        $setting = SystemSetting::where('key', $key)->first();
        if (!$setting) return $default;

        // Si hay usuario, buscar valor personalizado
        if ($userId) {
            $userValue = $setting->userValues()->where('user_id', $userId)->first();
            if ($userValue) return $userValue->value;
        }

        // Si hay opciones, devolver la opción por defecto
        if ($setting->options()->count() > 0) {
            $defaultOption = $setting->options()->where('default', true)->first();
            if ($defaultOption) return $defaultOption->value;
        }

        // Si hay valor por defecto en la tabla
        return $setting->default ?? $default;
    }

    public function set($key, $value, $userId = null)
    {
        $setting = SystemSetting::where('key', $key)->first();
        if (!$setting) return null;

        if ($userId) {
            // Guardar valor personalizado para usuario
            return UserSystemSetting::updateOrCreate(
                [
                    'user_id' => $userId,
                    'system_setting_id' => $setting->id
                ],
                [
                    'value' => $value
                ]
            );
        } else {
            // Actualizar valor por defecto
            $setting->default = $value;
            $setting->save();
            return $setting;
        }
    }

    public function all()
    {
        return SystemSetting::with(['options'])->get();
    }
}
