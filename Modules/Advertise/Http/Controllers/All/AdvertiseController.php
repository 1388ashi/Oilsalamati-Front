<?php

namespace Modules\Advertise\Http\Controllers\All;

use Illuminate\Routing\Controller;
use Modules\Advertise\Entities\Advertise;
use Modules\Advertise\Entities\PositionAdvertise;
use Shetabit\Shopit\Modules\Advertise\Http\Controllers\All\AdvertiseController as BaseAdvertiseController;

class AdvertiseController extends Controller
{

    public function index()
    {

        $random_possibility = random_int(0,100);
        $positions = PositionAdvertise::query()->with('advertisements')->active()->get();

        $advertisements = [];
        foreach ($positions as $position){
            $lover_possibility = 0;
            foreach ($position->advertisements()->get() as $ads) {
                if ($ads->possibility != 0) {
                    $advertisements[] =
                        ['position' => $position->id ,
                            'ads' => $ads->id ,
                            'start' => $lover_possibility,
                            'end' => $lover_possibility + $ads->possibility];
                    $lover_possibility += $ads->possibility;
                }
            }
        }

        $ads = [];

        foreach ($advertisements as $advertisement){
            $start = $advertisement['start'];
            $end = $advertisement['end'];
            if ($start < $random_possibility && $end > $random_possibility){
                $ads[] = [
                    'advertisement' => Advertise::find($advertisement['ads']),
                    'position' => PositionAdvertise::find($advertisement['position'])
                ];
            }
        }

        return response()->success('' , compact('ads'));
    }

}
