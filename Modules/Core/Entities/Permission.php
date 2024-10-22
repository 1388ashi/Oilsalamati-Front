<?php

namespace Modules\Core\Entities;

//use Shetabit\Shopit\Modules\Core\Entities\Permission as BasePermission;

use Spatie\Permission\Guard;
use Modules\Core\Contracts\Permission as PermissionContract;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission implements PermissionContract
{
    public static function customFindOrCreate(string $name, string $label, ?string $guardName = null): PermissionContract
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);
        $permission = static::getPermissions(['name' => $name, 'guard_name' => $guardName])->first();

        if (! $permission) {
            return static::query()->create(['name' => $name, 'label' => $label, 'guard_name' => $guardName]);
        }

        return $permission;
    }
}
