<?php

namespace Modules\Core\Entities;

//use Shetabit\Shopit\Modules\Core\Entities\Role as BaseRole;


use DateTimeInterface;
use Spatie\Permission\Guard;
use Modules\Core\Contracts\Role as RoleContract;
use Spatie\Permission\Models\Role as RolePermission;

class Role extends RolePermission implements RoleContract
{

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    public static function customFindOrCreate(string $name, string $label, ?string $guardName = null): RoleContract
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);

        $role = static::where('name', $name)->where('guard_name', $guardName)->first();

        if (! $role) {
            return static::query()->create(['name' => $name, 'label' => $label, 'guard_name' => $guardName]);
        }

        return $role;
    }
}
