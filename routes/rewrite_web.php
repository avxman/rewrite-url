<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

$routes = collect([
    [
        'route'=>'get',
        'resource'=>true,
        'name'=>'home',
        'uri'=>'home',
        'controller'=>'\App\Http\Controllers\RouteController::class',
        'method'=>'index',
        'only'=>"['store']",
        'except'=>"['index']",
        'middleware'=>"['web', 'auth']",
        'fallback'=>false,
        'prefix'=>NULL,
        'group'=>NULL,
        'model_id'=>NULL,
        'redirect'=>'https://google.com.ua',
        'position'=>1
    ],
    [
        'route'=>'get',
        'resource'=>false,
        'name'=>'home2',
        'uri'=>'{a?}',
        'controller'=>'\App\Http\Controllers\RouteController::class',
        'method'=>'index',
        'only'=>"['index']",
        'except'=>"['edit']",
        'middleware'=>"['web', 'auth']",
        'fallback'=>false,
        'prefix'=>NULL,
        'group'=>NULL,
        'redirect'=>NULL,
        'position'=>1
    ],
    [
        'route'=>'get',
        'resource'=>false,
        'name'=>'home3',
        'uri'=>'{a?}/{b?}',
        'controller'=>'\App\Http\Controllers\RouteController::class',
        'method'=>'fallback',
        'only'=>"['index']",
        'except'=>"['edit']",
        'middleware'=>"['web', 'auth']",
        'fallback'=>true,
        'prefix'=>NULL,
        'group'=>NULL,
        'redirect'=>NULL,
        'position'=>1
    ],
]);
$routes->each(function ($item){
    if($item['redirect']){
        $result = "Route::redirect('{$item['uri']}', '{$item['redirect']}')->name('{$item['name']}');";
    }
    elseif($item['fallback']){
        $result = "Route::fallback({$item['controller']}.'@{$item['method']}');";
    }
    else{
        $route = $item['resource'] ? 'resource' : $item['route'];
        $result = "Route::{$route}('{$item['uri']}', ";
        $result .= ($item['resource'] ? $item['controller'] : "{$item['controller']}.'@{$item['method']}'").")";
        $result .= ($item['resource'] ? "->names" : "->name")."('{$item['name']}')";
        if($item['resource'] && !empty($item['only'])) $result .= "->only({$item['only']})";
        if($item['resource'] && !empty($item['except'])) $result .= "->except({$item['except']})";
        $result .= ";";
    }
//    dd($result);
    eval($result);
});
unset($routes);
