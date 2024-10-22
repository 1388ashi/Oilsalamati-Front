<?php

namespace Modules\Core\Entities;

//use Shetabit\Shopit\Modules\Core\Entities\BaseModel as BaseBaseModel;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Entities\BaseModelTrait;

/**
 * Class BaseModel
 * @package Modules\Core\Entities
 * @method static create($attributes)
 * @method static findOrFail($id)
 * @method static find($id)
 * @method static Builder dateFilter()
 * @method static Builder sortFilter()
 * @method static Builder searchFilters()
 * @method static Builder filters()
 * @property array  withCommonRelations()
 * @property @protected @static array  $commonRelations; // should be static
 */
abstract class BaseModel extends Model
{
    use BaseModelTrait;
}
