<?php

namespace Modules\Contact\Entities;

//use Shetabit\Shopit\Modules\Contact\Entities\Repository as BaseRepository;
use Modules\Core\Entities\Repository as BaseRepository;
use Modules\Contact\Entities\Contact;

class Repository extends BaseRepository
{
    public function model()
    {
        return Contact::class;
    }

}
