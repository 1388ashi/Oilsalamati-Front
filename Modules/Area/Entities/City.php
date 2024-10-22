<?php

namespace Modules\Area\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Shipping\Entities\Shipping;
//use Modules\Core\Entities\BaseModel;
//use Modules\Core\Entities\HasCommonRelations;
//use Shetabit\Shopit\Modules\Area\Database\factories\CityFactory;
//use Shetabit\Shopit\Modules\Area\Entities\City as BaseCity;

class City extends Model
{

  //    protected static $commonRelations = ['province'];

  protected $fillable = ['name', 'province_id', 'status'];
  protected $hidden = ['created_at', 'updated_at'];
  protected $with = ['province'];


  public function province(): \Illuminate\Database\Eloquent\Relations\BelongsTo
  {
    return $this->belongsTo(Province::class);
  }

  public function shippings()
  {
    return $this->morphToMany(Shipping::class, 'shippable');
  }

  public function scopeActive($query)
  {
    $query->where('status', true);
  }

  public function isDeletable(): bool
  {
    return $this->shippings->isEmpty();
  }

  public function scopeFilters($query)
  {
    return $query
      ->when(request('id'), fn($q) => $q->where('id', request('id')))
      ->when(!is_null(request('status')), fn($q) => $q->where('status', request('status')))
      ->when(request('province_id'), function($q) {
        if (request('province_id') != 'all') {
          $q->where('province_id', request('province_id'));
        }
      })
      ->when(request('name'), fn($q) => $q->where('name', 'LIKE', '%' . request('name') . '%'))
      ->when(request('start_date'), fn($q) => $q->whereDate('created_at', '>=', request('start_date')))
      ->when(request('end_date'), fn($q) => $q->whereDate('created_at', '<=', request('end_date')));
  }
}
