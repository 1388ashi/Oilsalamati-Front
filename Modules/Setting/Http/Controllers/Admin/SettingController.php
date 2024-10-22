<?php

namespace Modules\Setting\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Modules\Core\Helpers\File;
use Modules\Core\Helpers\Helpers;
use Modules\Setting\Entities\Setting;
use Modules\Setting\Http\Requests\SettingUpdateRequest;
//use Shetabit\Shopit\Modules\Setting\Http\Controllers\Admin\SettingController as BaseSettingController;

class SettingController extends Controller
{

	public function update(SettingUpdateRequest $request)
	{
		$inputs = $request->all();
		foreach ($inputs as $name => $value) {
			if ($setting = Setting::where('name', '=', $name)->first()) {
				Setting::validate($setting, $value);

				if (($setting->type === 'image' || $setting->type === 'file') && $request->file($name)) {
					if (!$request->file($name)->isValid()) {
						if ($request->file($name)->getError() == \UPLOAD_ERR_INI_SIZE) {
							throw Helpers::makeValidationException(
								'حجم فایل بیش از حد مجاز است: ' . ini_get('upload_max_filesize') . '. برای' . $name
							);
						}
						throw Helpers::makeValidationException($request->file($name)->getErrorMessage());
					}
					File::delete($setting->value);
					$value = File::imageUpload($value, 'settings');
				}
				if ($setting->type == 'number' || $setting->type == 'price') {
					$value = (int)$value;
				}
				$setting->update(['value' => $value]);
			}
		}
		Cache::forget('settings');

		if (request()->header('Accept') == 'application/json') {
			return response()->success(trans('core::setting.admin.update'), null);
		}

		return redirect()->back()->with('success', trans('core::setting.admin.update'));
	}









	// came from vendor ================================================================================================
	public function index()
	{
		$groupsArray = Setting::getGroups();

		if (request()->header('Accept') == 'application/json') {
			return response()->success(trans('core::setting.admin.index'), $groupsArray);
		}
	}

	public function allSettings()
	{
		return response()->success('', ['settings' => Setting::all()]);
	}

	/*
     * Get group name and return group settings
     */
	public function show(string $groupName)
	{
		$settings = Setting::query()->where('group', '=', $groupName)->get();

		if (request()->header('Accept') == 'application/json') {
			return response()->success(
				trans('core::setting.name') . ' ' . Setting::getGroupName($groupName),
				compact('settings')
			);
		}

		$settingTypes = Setting::query()->where('group', $groupName)->get()->groupBy('type');

		return view('setting::admin.show', compact('settingTypes'));
	}

	/**
	 * Delete a setting file.
	 *
	 * @param Setting $setting
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function destroyFile($setting)
	{
		$setting = is_numeric($setting) ? Setting::query()->findOrFail($setting) : Setting::whereName($setting)->first();
		abort_if(!$setting || $setting->type != 'image', 403);
		$deleted = File::delete($setting->value);

		if ($deleted['success']) {
			$setting->update(['value' => null]);
		}

		if (request()->header('Accept') == 'application/json') {
			return response()->success(trans(' core::setting.admin.destroy_file'), null);
		}

		return redirect()->back()->with('success', trans(' core::setting.admin.destroy_file'));
	}
}
