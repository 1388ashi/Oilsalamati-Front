<?php

namespace Modules\Dashboard\Http\Controllers\Admin;

//use Shetabit\Shopit\Modules\Dashboard\Http\Controllers\Admin\SiteViewController as BaseSiteViewController;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Modules\Blog\Entities\Post;
use Modules\Home\Entities\SiteView;
use Modules\Product\Entities\Product;

class SiteViewController extends Controller
{

    public function index()
    {
        //خروجی ساعت ها رو دارم ==> خروجی روز رو ازش بدست میارم

        $siteviews = SiteView::query()
            ->orderBy('id','DESC')
            ->where('date', '<=', now()

            )->get()->groupBy('date');

        //calculate siteviews
        $siteviewslist = array();

        foreach ($siteviews as $y => $siteview) {
            $siteviewslist[$y] = 0;
            foreach ($siteview as $x) {
                $siteviewslist[$y] = $siteviewslist[$y] + $x->count;
            }
        }

        $siteviewsCollection = collect($siteviewslist);

        $siteviewsCollection = SiteView::paginateArray(24,$siteviewsCollection);


        $totalPostViewsCount = views(Post::class)->count();
        $totalProductViewsCount = views(Product::class)->count();


        return response()->success('site views count', [
            'site_views' => $siteviewsCollection, // values is siteview array
            'totalPostViewsCount' => $totalPostViewsCount,
            'totalProductViewsCount' => $totalProductViewsCount,
        ]);
    }



    public function show($date)
    {
        $siteviews = SiteView::query()
            ->where('date', $date)
            ->latest('hour')
            ->get(['date','hour','count']);

        return response()->success('', [
            'site_views' => $siteviews,
        ]);
    }

}
