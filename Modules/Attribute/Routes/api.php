<?php

Route::superGroup('admin', function () {
    Route::permissionResource('attributes', 'AttributeController');
});
