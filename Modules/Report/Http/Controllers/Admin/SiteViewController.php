<?php

namespace Modules\Report\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Modules\Home\Entities\SiteView;

class SiteViewController extends Controller
{
    public function index()
    {
        $siteviews = SiteView::query()
            ->select('date')
            ->selectRaw('SUM(count) as total_count')
            ->where('date', '<=', now())
            ->groupBy('date')
            ->orderBy('date', 'DESC')
            ->paginate();

        return view('report::admin.siteview.index', compact('siteviews'));
    }

    public function show($date)
    {
        $siteviews = SiteView::query()
            ->where('date', $date)
            ->latest('hour')
            ->get(['date','hour','count']);

        return view('report::admin.siteview.show', compact('siteviews'));
    }
}
