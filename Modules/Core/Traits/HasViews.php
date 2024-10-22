<?php

namespace Modules\Core\Traits;

//use Shetabit\Shopit\Modules\Core\Traits\HasViews as BaseHasViews;


use Illuminate\Support\Facades\Request;

trait HasViews
{
    public function record()
    {
        if (\DB::table('model_views')->where('model_id', $this->id)
            ->where('model_type', $this->getMorphClass())
            ->where('ip', Request::ip())->exists()) {
            \DB::table('model_views')->where('model_id', $this->id)
                ->where('model_type', $this->getMorphClass())
                ->where('ip', Request::ip())->update([
                    'count' => \DB::raw('count + 1'),
                    'updated_at' => now()
                ]);
        } else {
            \DB::table('model_views')->insert([
                'model_id' => $this->id,
                'model_type' => $this->getMorphClass(),
                'count' => 1,
                'ip' => Request::ip(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
