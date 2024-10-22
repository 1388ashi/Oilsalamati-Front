<?php

namespace Modules\Admin\Classes;

class ActivityLogHelper
{
    public static function simple($description, $event, $affectedModel):void {
        activity()
            ->causedBy(auth()->user())
            ->event($event)
            ->performedOn($affectedModel)
            ->log($description);
    }

    public static function updatedModel($description,$model):void {
        $changedColumns = $model->getChanges();
        if (count($changedColumns) >= 1 && isset($changedColumns['updated_at']))
            unset($changedColumns['updated_at']);

        activity()
            ->causedBy(auth()->user())
            ->event('update')
            ->performedOn($model)
            ->withProperties([
                'changedColumns' => $changedColumns,
            ])
            ->log($description);
    }

    public static function storeModel($description,$model):void {
        activity()
            ->causedBy(auth()->user())
            ->event('store')
            ->performedOn($model)
            ->log($description);
    }

    public static function deletedModel($description,$model):void {
        $originalColumns = $model->getOriginal();

        activity()
            ->causedBy(auth()->user())
            ->event('delete')
            ->performedOn($model)
            ->withProperties([
                'originalColumns' => $originalColumns,
            ])
            ->log($description);
    }

}
