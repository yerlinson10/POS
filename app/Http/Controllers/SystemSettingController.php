<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\SystemSettingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class SystemSettingController extends Controller
{
    protected $service;

    public function __construct(SystemSettingService $service)
    {
        $this->service = $service;
        $this->service->forUser(Auth::id());
    }

    public function index()
    {
        // Aseguramos que el servicio tenga el usuario correcto
        $this->service->forUser(Auth::id());
        $settings = $this->service->all()->map(function ($setting) {
            return [
                'key' => $setting->key,
                'label' => $setting->label,
                'type' => $setting->type,
                'default' => $setting->default,
                // Aquí traemos el valor real del usuario, no el default
                'value' => $this->service->get($setting->key, $setting->default),
                'description' => $setting->description,
                'options' => $setting->options->map(function ($option) {
                    return [
                        'value' => $option->value,
                        'label' => $option->label,
                        'default' => $option->default,
                        'description' => $option->description,
                    ];
                }),
            ];
        });
        return Inertia::render('settings/system', [
            'settings' => $settings,
        ]);
    }

    public function show($key)
    {
        $value = $this->service->get($key, null);
        if ($value === null) {
            return response()->json(['message' => 'Setting not found'], 404);
        }
        return response()->json(['key' => $key, 'value' => $value]);
    }

    public function update(Request $request, $key)
    {
        try {

            $value = $request->input('value');
            $this->service->set($key, $value);

            return redirect()->back()->with('message', [
                'type' => 'success',
                'text' => 'Updated configuration'
            ]);

        } catch (\Throwable $th) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Error when updating the configuration: ' . $th->getMessage()
            ]);
            return response()->json(['message' => 'Error when updating the configuration'], 500);
        }
    }

    public function updateAll(Request $request)
    {
        try {

            $data = $request->all();
            foreach ($data as $key => $value) {

                $this->service->set($key, $value);
            }
            return redirect()->back()->with('message', [
                'type' => 'success',
                'text' => 'Updated configurations'
            ]);


        } catch (\Throwable $th) {
            return redirect()->back()->with('message', [
                'type' => 'error',
                'text' => 'Error when updating the configurations: ' . $th->getMessage()
            ]);
        }
    }
}
