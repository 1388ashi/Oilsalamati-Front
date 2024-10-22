<?php

namespace Modules\Attribute\Entities;

use AjCastro\EagerLoadPivotRelations\EagerLoadPivotTrait;
use Modules\Core\Entities\BaseModel;
use Modules\Core\Entities\HasCommonRelations;
use Modules\Core\Helpers\Helpers;
use Modules\Core\Traits\HasAuthors;
use Modules\Core\Traits\HasDefaultFields;
use Modules\Product\Entities\Variety;
//use Shetabit\Shopit\Modules\Attribute\Entities\Attribute as BaseAttribute;
use Modules\Attribute\Entities\AttributeValue;

class Attribute extends BaseModel
{
    use HasAuthors, /*HasCommonRelations, */EagerLoadPivotTrait, HasDefaultFields;

    protected $defaults = [
        'style' => 'select'
    ];

//    protected static $commonRelations = [
//        'values'
//    ];
    protected $with = ['values'];

    protected $fillable = [
        'name',
        'label',
        'type',
        'show_filter',
        'public',
        'style',
        'status'
    ];

    const TYPE_SELECT = 'select';
    const TYPE_TEXT = 'text';

    protected $hidden = ['created_at', 'updated_at','creator_id','updater_id'];

    public static function booted()
    {
        static::deleting(function (\Modules\Attribute\Entities\Attribute $attribute) {
            if ($attribute->varieties()->exists()) {
            return redirect()->route('admin.attributes.index')->with([
              'error' => 'به علت استفاده شدن در یک محصول امکان حذف آن وجود ندارد']);
            }
        });
    }
    public function scopeFilters($query)
    {
        $status = request('status');
        $label = request('label');
        $type = request('type');
        $show_filter = request('show_filter');

        return $query
            ->when($type,fn($q) => $q->where('type',$type))
            ->when($label, fn($q) => $q->where('label', 'like', "%$label%"))
            ->when(isset($show_filter), fn($q) => $q->where("show_filter", $show_filter))
            ->when(isset($status), fn($q) => $q->where("status", $status));
    }

    //Relations

    public function values()
    {
        return $this->hasMany(AttributeValue::class, 'attribute_id');
    }

    public function varieties(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Variety::class);
    }

    public static function getAvailableType(): array
    {
        return [static::TYPE_TEXT,static::TYPE_SELECT];
    }

    public function scopeActive($query)
    {
        $query->where('status' , true);
    }
}
