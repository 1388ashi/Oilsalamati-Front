<?php

namespace Modules\Setting\Http\Controllers;

//use Shetabit\Shopit\Modules\Setting\Http\Controllers\SettingController as BaseSettingController;

use Modules\Advertise\Http\Controllers\All\AdvertiseController;
use Modules\Blog\Entities\Declaration\Declaration;
use Modules\Blog\Entities\News\News;
use Modules\Category\Entities\Category;
use Illuminate\Routing\Controller;
use Modules\Setting\Entities\Setting;

class SettingController extends Controller
{
    public function index()
    {
        $advertisements = app(AdvertiseController::class)->index()->original['data'];
        $mostViewedDeclarations = Declaration::query()->withCount('views')
            ->orderBy('views_count', 'DESC')->take(7)->get();
        $settings = Setting::where('private', '=', false)->get();
        $declarationCategories = Category::whereModel(Declaration::class)
            ->withCount(['categoryPivots' => function($query) {
                $query->whereHas('categorizable');
            }])->get();
        foreach ($declarationCategories as $declarationCategory) {
            $declarationCategory->loadModelsCount();
        }
        $specialNews = News::whereNotNull('special')->take(5)->get();

        return response()->success('', [
            'advertisements' => $advertisements,
            'settings' => $settings,
            'most_viewed_declarations' => $mostViewedDeclarations,
            'declaration_categories' => $declarationCategories,
            'special_news' => $specialNews
        ]);
    }

    /*
     * Get group name and return group settings
     */
    public function show(string $groupName)
    {
        $settings = Setting::query()->where([['group', '=', $groupName], ['private', '=', false]])->get();

        return response()->success(
            trans('core::setting.name') . ' ' . Setting::getGroupName($groupName),
            $settings
        );
    }
}
