<?php

namespace Modules\Link\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Modules\Core\Classes\CoreSettings;
use Shetabit\Shopit\Modules\Core\Contracts\HasParent;
//use Shetabit\Shopit\Modules\Link\Http\Controllers\Admin\LinkController as BaseLinkController;

class LinkController extends Controller
{
    public function index()
    {
        $links = app(CoreSettings::class)->get('linkables');

        return response()->success('', ['linkables' => $links]);
    }




    // came from vendor ================================================================================================
    public function create()
    {
        $options = [];
        foreach (app(CoreSettings::class)->get('linkables') as $linkable) {
            $model = class_exists($linkable['model']) ? new $linkable['model'] : null;
            if ($linkable['index']) {
                $option = [];
                if (!Str::contains($linkable['model'], 'Custom')) {
                    $option['label'] = 'لیست ' . $linkable['label'];
                } else {
                    $option['label'] = $linkable['label'];
                }
                $option['linkable_type'] = $linkable['model'];
                $option['unique_type'] = 'Index' . basename($linkable['model']);
                $option['title'] = $linkable['title'] ?? 'title';
                $option['models'] = null;

                $options[] = $option;
            }
            if ($linkable['show'] && $model) {
                $option = [];
                $option['label'] = 'آیتم های ' . $linkable['label'];
                $option['linkable_type'] = $linkable['model'];
                $option['unique_type'] = basename($linkable['model']);
                $option['title'] = $linkable['title'] ?? 'title';
                if ($model instanceof HasParent) {
                    $model = $model->with('parent');
                }
                $option['models'] = $model->get();
                foreach ($option['models'] as $tempModel) {
                    // Hide long texts
                    $tempModel->makeHidden('text');
                    $tempModel->makeHidden('body');
                    $tempModel->makeHidden('description');
                }
                $options[] = $option;
//                dd($option['models']);

            }
        }

        return response()->success('', ['linkables' => $options]);
    }
}
