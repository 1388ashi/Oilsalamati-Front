<?php

namespace Modules\Core\Contracts;

//use Shetabit\Shopit\Modules\Core\Contracts\Notifiable as BaseNotifiable;

interface Notifiable {
    public function notifications();

    public function notify($instance);
}
