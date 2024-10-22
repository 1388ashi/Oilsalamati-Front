<?php

namespace Modules\Dashboard\Http\Controllers\Admin;

//use Shetabit\Shopit\Modules\Dashboard\Http\Controllers\Admin\ReportController as BaseReportController;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Dashboard\Services\ReportService;

class ReportController extends Controller
{
    public function __construct(protected ReportService $dashboardService) {}

    public function year(Request $request)
    {
        $request->validate([
            'offset_year' => 'nullable|integer|min:0'
        ]);

        return response()->success('', [
            'year_statistics' => $this->dashboardService->getByYear($request->input('offset_year'))
        ]);
    }

    public function month(Request $request)
    {
        $request->validate([
            'offset_year' => 'nullable|integer|min:0',
            'month' => 'nullable|integer|between:1,12'
        ]);

        return response()->success('', [
            'month_statistics' => $this->dashboardService
                ->getByMonth($request->input('month'), $request->input('offset_year'))
        ]);
    }
}
