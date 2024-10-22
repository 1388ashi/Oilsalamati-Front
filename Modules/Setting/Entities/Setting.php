<?php

namespace Modules\Setting\Entities;

//use Shetabit\Shopit\Modules\Setting\Entities\Setting as BaseSetting;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Core\Entities\BaseModel as Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Lang;
use Modules\Core\Helpers\Helpers;
use stdClass;

class Setting extends Model
{
    protected $fillable = [
        'group',
        'label',
        'name',
        'type',
        'value',
        'options',
        'private'
    ];

    protected $appends = ['url'];

    protected $hidden = ['created_at', 'updated_at'];

    public static $cache = [];

    protected static function booted()
    {
        parent::booted();

        Helpers::clearCacheInBooted(static::class, 'settings');
    }

    public function getUrlAttribute()
    {
        if (($this->type != 'image' && $this->type != 'video') || empty($this->value)) {
            return null;
        }

        return url(config('app.url').'/storage/' . $this->value);
    }

    public static function getFromName($name)
    {
        if (!app()->runningInConsole() && isset(static::$cache[$name])) {
            return static::$cache[$name];
        }
        static::$cache[$name] = \Shetabit\Shopit\Modules\Setting\Entities\Setting::where('name', '=', $name)->first()->value ?? '';

        return static::$cache[$name];
    }

    public static function validate($setting, $value)
    {
        /** $methodName format: [group]Group[name]Validation */
        $methodName = $setting->group . 'Group' . ucfirst($setting->name) . 'Validation';
        $className = static::class;
        if (method_exists(static::class, $methodName)) {
            /** The method should throw exception: @var HttpResponseException */
            $className::{$methodName}($value, $setting->label);
        }
        $methodName = 'type' . ucfirst($setting->type) . 'Validation';
        if (method_exists(static::class, $methodName)) {
            /** The method should throw exception: @var HttpResponseException */
            $className::{$methodName}($value, $setting->label);
        }

        return true;
    }

    public static function typeNumberValidation($value, $name)
    {
        $validator = Validator::make(['value' => $value],
            [
                'value' => 'required|numeric'
            ]);
        $validator->setAttributeNames(['value' => $name]);
        $validator->validate();
    }

    public static function typePriceValidation($value, $name)
    {
        $validator = Validator::make(['value' => $value],
            [
                'value' => 'required|numeric'
            ]);
        $validator->setAttributeNames(['value' => $name]);
        $validator->validate();
    }

    public static function getGroupName(string $groupName)
    {
        $key = "setting::groups.$groupName";

        return Lang::has($key) ? trans($key) : $groupName;
    }

    public static function getGroups()
    {
        $groups = DB::table('settings')->select('group as name')->distinct()->get();
        $groupsArray = [];
        foreach ($groups as $group) {
            $temp = new StdClass();
            $temp->name = $group->name;
            $temp->label = Setting::getGroupName($group->name);
            $groupsArray[] = $temp;
        }

        return $groupsArray;
    }
}
