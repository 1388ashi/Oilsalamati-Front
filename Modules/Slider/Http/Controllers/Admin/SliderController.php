<?php

namespace Modules\Slider\Http\Controllers\Admin;

//use Shetabit\Shopit\Modules\Slider\Http\Controllers\Admin\SliderController as BaseSliderController;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Link\Http\Controllers\Admin\LinkController;
use Modules\Slider\Entities\Slider;
use Modules\Slider\Http\Requests\Admin\SliderSortRequest;
use Modules\Slider\Http\Requests\Admin\SliderStoreRequest;

class SliderController extends Controller
{
    public function groups()
    {
        $group_labels = collect(config('slider.groups'))->map(function ($name) {
            if (Lang::has('core::groups.' . $name)) {
                return trans('core::groups.' . $name);
            }
            return false;
        });

        if (request()->header('Accept') == 'application/json') {
          return response()->success('', ['groups' => config('slider.groups'),
              'group_labels' => $group_labels]);
        }
        return view('slider::admin.groups', ['groups' => config('slider.groups'),'group_labels' => $group_labels]);
    }

    public function index($group)
    {
        $linkableData = (new LinkController)->create();

        $dataArray = $linkableData->getData(true);

        if ($dataArray['success'] && isset($dataArray['data']['linkables'])) {
            $linkables = $dataArray['data']['linkables'];
        }
        $sliders = Slider::orderBy('order', 'DESC')->whereGroup($group)->paginate(15);

        if (request()->header('Accept') == 'application/json') {
          return response()->success('', compact('sliders'));
        }
        return view('slider::admin.index', compact('sliders','linkables'));
    }

    public function store(SliderStoreRequest $request)
    {
        $slider = Slider::create($request->all());
        $slider->addImage($request->file('image'));
        $slider->load('media');
        $slider->refresh();
        ActivityLogHelper::storeModel('اسلایدر ثبت شد', $slider);

        if (request()->header('Accept') == 'application/json') {
          return response()->success('اسلایدر با موفقیت اضافه شد', ['slider' => $slider]);
        }
        return redirect()->route('admin.sliders.groups.index',$slider->group)
        ->with('success', 'اسلایدر با موفقیت اضافه شد.');
    }

    public function update(SliderStoreRequest $request, $sliderId)
    {
        $slider = Slider::findOrFail($sliderId);
        if ($request->linkable_id) {
            $slider->link = null;
        }
        $slider->update($request->all());
        $slider->updateFiles($request->images, 'image');
        $slider->load('media');
        ActivityLogHelper::updatedModel('اسلایدر بروز شد', $slider);

        if (request()->header('Accept') == 'application/json') {
          return response()->success('اسلایدر با موفقیت بروز شد', ['slider' => $slider]);
        }
        return redirect()->route('admin.sliders.groups.index',$slider->group)
        ->with('success', 'اسلایدر با موفقیت بروز شد.');
    }
    public function sort(SliderSortRequest $request)
    {
        $idsFromRequest = $request->input('orders');
        $c = 999999;
          foreach ($idsFromRequest as $id) {
              $slider = Slider::find($id);
              $slider->order = $c--;
              $slider->save();
            }
        if (request()->header('Accept') == 'application/json') {
          return response()->success('مرتب سازی با موفقیت انجام شد');
        }
        return redirect()->route('admin.sliders.groups.index',$request->group)
        ->with('success', 'اسلایدر با موفقیت مرتب سازی شد.');
    }


    public function destroy($sliderId)
    {
        $slider = Slider::findOrFail($sliderId);
        $group = $slider->group;
        $slider->delete();
        ActivityLogHelper::deletedModel('اسلایدر حذف شد', $slider);

        if (request()->header('Accept') == 'application/json') {
          return response()->success('اسلایدر با موفقیت حذف شد', ['slider' => $slider]);
        }
        return redirect()->route('admin.sliders.groups.index',$group)
        ->with('success', 'اسلایدر با موفقیت حذف شد.');
    }
}

