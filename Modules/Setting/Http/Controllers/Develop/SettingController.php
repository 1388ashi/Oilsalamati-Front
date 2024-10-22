<?php

namespace Modules\Setting\Http\Controllers\Develop;

//use Shetabit\Shopit\Modules\Setting\Http\Controllers\Develop\SettingController as BaseSettingController;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Modules\Setting\Entities\Setting;
use Modules\Setting\Http\Requests\Develop\SettingStoreRequest;
use Modules\Setting\Http\Requests\Develop\SettingUpdateRequest;
use Modules\Setting\Http\Traits\PushToConfig;

class SettingController extends Controller
{
    use PushToConfig;

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        die();
        $find = request('find', false);

        $settings = Setting::query()->when($find, function ($query) use($find){
            $query->where('name', 'like', '%'.$find.'%')->latest();
        }, function ($query){
            $query->latest();
        })->get();

        return view('setting::index' , compact('settings'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $this->run();

        return view('setting::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param SettingStoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(SettingStoreRequest $request)
    {
        Setting::query()->create($request->all());
        $this->run();

        return  redirect()->route('settings.index');
    }
    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(Setting $setting)
    {
        return view('setting::edit' , compact('setting'));
    }

    /**
     * Update the specified resource in storage.
     * @param SettingUpdateRequest $request
     * @param Setting $setting
     * @return Renderable
     */
    public function update(SettingUpdateRequest $request, Setting $setting)
    {

        $setting->update($request->all());
        $this->run();

        return redirect()->route('settings.index');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Setting $setting)
    {
        $setting->delete();
        $this->run();

        return redirect()->route('settings.index');
    }
}
