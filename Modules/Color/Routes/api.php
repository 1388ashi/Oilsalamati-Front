<?php

Route::superGroup('admin', function () {
    Route::permissionResource('colors','ColorController');
});
