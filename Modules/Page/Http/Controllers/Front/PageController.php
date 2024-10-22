<?php

namespace Modules\Page\Http\Controllers\Front;

//use Shetabit\Shopit\Modules\Page\Http\Controllers\Front\PageController as BasePageController;

use App\Http\Controllers\Controller;
use Modules\Core\Helpers\Helpers;
use Modules\Gallery\Entities\Gallery;
use Modules\MultiMedia\Entities\MultiMedia;
use Modules\Page\Entities\Page;
use Modules\Page\Http\Requests\Admin\PageRequest;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::latest();
        $pages = Helpers::paginateOrAll($pages);

        return response()->success('', compact('pages'));
    }

    public function show(Page $page)
    {
        return response()->success('', compact('page'));
    }

//    public function listMultiMedia()
//    {
//        $totalAudios = MultiMedia::whereType('audio')->count();
//        $totalVideos = MultiMedia::whereType('video')->count();
//        $totalGalleries = Gallery::whereStatus(1)->count();
//
//        return response()->success('', [
//            'total_audios' => $totalAudios,
//            'total_videos' => $totalVideos,
//            'total_galleries' => $totalGalleries
//        ]);
//    }
}
