<?php

namespace Modules\Home\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Core\Entities\Media;
use Modules\Core\Helpers\Helpers;
use Modules\Home\Entities\BeforeAfterImage;
use Modules\Home\Http\Requests\BeforeAfterImage as BeforeAfterImageRequest;
use Modules\Home\Http\Requests\BeforeAfterImageUpdate as BeforeAfterImageUpdateRequest;

class BeforeAfterImageController extends Controller
{
    public function setBeforeAfterImage(BeforeAfterImageRequest $request)
    {
        $uuid = Str::uuid()->toString();

        //media
        $imageBefore = new BeforeAfterImage($request->all());
        $imageBefore->title = $request->title_before;
        $imageBefore->type = 'before';
        $imageBefore->uuid = $uuid;
//        $imageBefore->enabled = 1;
        $imageBefore->save();
        $imageBefore->saveFileSpatieMedia($request->before_image,'before_after_image_before');
        $imageBefore->saveFileSpatieMedia($request->customer_image,'before_after_image_customer');

        $imageAfter = new BeforeAfterImage();
        $imageAfter->title = $request->title_after;
        $imageAfter->type = 'after';
        $imageAfter->uuid = $uuid;
//        $imageAfter->enabled = 1;
        $imageAfter->save();
        $imageAfter->saveFileSpatieMedia($request->after_image,'before_after_image_after');

        return response()->success('تصاویر قبل و بعد محصول با موفقیت ثبت شد');
    }

    public function getBeforeAfterImagesForAdmin()
    {
        return $this->getBeforeAfterImage('admin');
    }

    public function getBeforeAfterImagesForFront()
    {
        return $this->getBeforeAfterImage('front');
    }

    public function getBeforeAfterImage($requester)
    {
        if ($requester == 'admin'){
            $row = BeforeAfterImage::where('type','before')->orderBy('id','desc')->get();
        } else {
            $row = BeforeAfterImage::where('type','before')->where('enabled',1)->orderBy('id','desc')->get();
        }

        $list = array();
        foreach ($row as $item) {
            $before_id = $item->id;
            $after = BeforeAfterImage::where('uuid',$item->uuid)->where('type','after')->first();
//            Log::info("before id is $before_id");
            $after_id = $after->id;
//            Log::info("after id is $after_id");
            $list[] = [
                'id' => $item->id,
                'customer_name' => $item->customer_name,
                'title_before' => $item->title,
                'title_after' => $after->title,
                'short_description' => $item->short_description,
                'full_description' => $item->full_description,
                'enabled' => (bool)$item->enabled,
                'product' => [
                    'id' => $item->product_id,
                    'title' => $item->product?$item->product->title:'',
                    'images' => Helpers::getImages('Product',$item->product_id)
                ],
                'before_image' => DB::table('media')
                    ->where('model_type','Modules\Home\Entities\BeforeAfterImage')
                    ->where('model_id',$before_id)
                    ->where('collection_name','before_after_image_before')
                    ->select('uuid', 'file_name')
                    ->first(),
                'after_image' => DB::table('media')
                    ->where('model_type','Modules\Home\Entities\BeforeAfterImage')
                    ->where('model_id',$after_id)
                    ->where('collection_name','before_after_image_after')
                    ->select('uuid', 'file_name')
                    ->first(),
                'customer_image' => DB::table('media')
                    ->where('model_type','Modules\Home\Entities\BeforeAfterImage')
                    ->where('model_id',$before_id)
                    ->where('collection_name','before_after_image_customer')
                    ->select('uuid', 'file_name')
                    ->first(),
            ];
        }
        return response()->success('تصاویر ثبت شده قبل و بعد محصولات ' , $list);
    }

    public function getBeforeAfterImageForAdmin($id)
    {
        $before = BeforeAfterImage::find($id);
        $before_id = $before->id;
        $after = BeforeAfterImage::where('uuid',$before->uuid)->where('type','after')->first();
        $after_id = $after->id;
        $data = [
            'id' => $before->id,
            'customer_name' => $before->customer_name,
            'title_before' => $before->title,
            'title_after' => $after->title,
            'short_description' => $before->short_description,
            'full_description' => $before->full_description,
            'enabled' => (bool)$before->enabled,
            'product' => [
                'id' => $before->product_id,
                'title' => $before->product?$before->product->title:'',
                'images' => Helpers::getImages('Product',$before->product_id)
            ],
            'before_image' => DB::table('media')
                ->where('model_type','Modules\Home\Entities\BeforeAfterImage')
                ->where('model_id',$before_id)
                ->where('collection_name','before_after_image_before')
                ->select('uuid', 'file_name')
                ->first(),
            'after_image' => DB::table('media')
                ->where('model_type','Modules\Home\Entities\BeforeAfterImage')
                ->where('model_id',$after_id)
                ->where('collection_name','before_after_image_after')
                ->select('uuid', 'file_name')
                ->first(),
            'customer_image' => DB::table('media')
                ->where('model_type','Modules\Home\Entities\BeforeAfterImage')
                ->where('model_id',$before_id)
                ->where('collection_name','before_after_image_customer')
                ->select('uuid', 'file_name')
                ->first(),
        ];

        return response()->success('تصویر قبل و بعد محصول ' , $data);
    }

    public function changeStatusBeforeAfterImage(Request $request)
    {
        $rules = [
            'before_after_id' => 'required',
            'enabled' => 'required'
        ];
        $request->validate($rules);

        $before = BeforeAfterImage::find($request->before_after_id);
        $after = BeforeAfterImage::where('uuid',$before->uuid)->where('type','after')->first();

        $before->enabled = $request->enabled;
        $before->save();

        $after->enabled = $request->enabled;
        $after->save();

        $response = [
            'id' => $before->id,
            'enabled' => $before->enabled,
        ];

        return response()->success('وضعیت تصاویر قبل و بعد محصول با موفقیت تغییر کرد',compact('response'));
    }

    public function deleteBeforeAfterImage(Request $request)
    {
        $rules = [
            'before_after_id' => 'required'
        ];
        $request->validate($rules);

        $beforeAfter = BeforeAfterImage::find($request->before_after_id);
        if (!$beforeAfter){
            return response()->error('مورد درخواست شده یافت نشد');
        }

        $after_id = BeforeAfterImage::where('uuid',$beforeAfter->uuid)->where('type','after')->first()->id;
        DB::table('media')->where('model_type','Modules\Home\Entities\BeforeAfterImage')->whereIn('model_id',[$beforeAfter->id,$after_id])->delete();
        DB::table('before_after_images')->whereIn('id',[$beforeAfter->id,$after_id])->delete();

        return response()->success('تصاویر ثبت شده قبل و بعد محصول با موفقیت حذف شد');
    }

    public function updateBeforeAfterImageForAdmin(BeforeAfterImageUpdateRequest $request, $id)
    {

        $before = BeforeAfterImage::find($id);
        $after = BeforeAfterImage::where('uuid',$before->uuid)->where('type','after')->first();

        $before = BeforeAfterImage::find($id);
        $before->title = $request->title_before;
        $before->short_description = $request->short_description;
        $before->full_description = $request->full_description;
        $before->product_id = $request->product_id;
        $before->customer_name = $request->customer_name;
        $before->save();

        $after->title = $request->title_after;
        $after->save();

        if (!empty($request->before_image)){
            Media::where('model_id', $before->id)->where('collection_name','before_after_image_before')->delete(); // حذف تصویر قبلی
            $before->saveFileSpatieMedia($request->before_image,'before_after_image_before');
        }

        if (!empty($request->customer_image)){
            Media::where('model_id', $before->id)->where('collection_name','before_after_image_customer')->delete(); // حذف تصویر قبلی
            $before->saveFileSpatieMedia($request->customer_image,'before_after_image_customer');
        }

        if (!empty($request->after_image)){
            Media::where('model_id', $after->id)->where('collection_name','before_after_image_after')->delete(); // حذف تصویر قبلی
            $after->saveFileSpatieMedia($request->after_image,'before_after_image_after');
        }

        return response()->success('تصاویر قبل و بعد محصول با موفقیت ویرایش شد');
    }
}
