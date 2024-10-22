<?php

namespace Modules\Area\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Shipping\Entities\Shipping;
//use Modules\Core\Entities\BaseModel;
//use Modules\Core\Entities\HasCommonRelations;
//use Shetabit\Shopit\Modules\Area\Database\factories\ProvinceFactory;
//use Shetabit\Shopit\Modules\Area\Entities\Province as BaseProvince;

class Province extends Model
{

  protected $fillable = ['name', 'status'];
  protected $hidden = ['created_at', 'updated_at'];

  public function cities(): \Illuminate\Database\Eloquent\Relations\HasMany
  {
    return $this->hasMany(City::class);
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
    return $this->cities->isEmpty();
  }

  public function scopeFilters($query)
  {
    return $query
      ->when(request('id'), fn($q) => $q->where('id', request('id')))
      ->when(request('name'), fn($q) => $q->where('name', 'LIKE', '%'.request('name').'%'))
      ->when(request('start_date'), fn($q) => $q->whereDate('created_at', '>=', request('start_date')))
      ->when(request('end_date'), fn($q) => $q->whereDate('created_at', '<=', request('end_date')));
  }
}
