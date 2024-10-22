<?php


namespace Modules\Core\Contracts;

//use Shetabit\Shopit\Modules\Core\Contracts\Permission as BasePermission;


interface Permission extends \Spatie\Permission\Contracts\Permission
{
    /**
     * Find or Create a permission by its name and guard name.
     *
     * @param string $name
     * @param string $label
     * @param string|null $guardName
     *
     * @return \Shetabit\Shopit\Modules\Core\Contracts\Permission
     */
    public static function customFindOrCreate(string $name, string $label, ?string $guardName): self;
}
