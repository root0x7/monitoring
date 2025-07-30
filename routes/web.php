<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\DashboardsController;


Auth::routes(['register'=>false]);

Route::group(['middleware'=>'auth'],function(){
    Route::get('/',[SiteController::class,'index']);
    Route::get('/dashboards',[SiteController::class,'dashboars']);
    Route::group(['prefix'=>'/dashboards'],function(){
        Route::get('/system',[DashboardsController::class,'system']);
    });
});
