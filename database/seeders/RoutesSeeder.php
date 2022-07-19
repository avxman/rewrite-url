<?php

namespace Database\Seeders;

use Avxman\Rewrite\Models\RouteGroups;
use Avxman\Rewrite\Models\Routes;
use Illuminate\Database\Seeder;

class RoutesSeeder extends Seeder
{

    protected array $controllers = [
        \App\Http\Controllers\Routes\RouteController::class,
        \App\Http\Controllers\Routes\ResourceController::class
    ];
    protected array $routes = [
        'get',
        'post',
        'all'
    ];
    protected array $methods = [
        'index',
        'store'
    ];
    protected array $middlewares = [
        NULL,
        NULL,
        '["web"]',
        '["api"]',
        '["web", "api"]'
    ];
    protected array $only = [
        '["index"]',
        '["store"]',
        '["index", "store"]',
        '["create"]',
        '["show"]',
        '["edit"]',
        '["update"]',
        '["destroy"]',
        '["create", "show"]',
        '["edit", "update"]',
        '["create", "destroy"]',
        '["index", "store", "create", "show", "edit", "update", "destroy"]',
    ];
    protected array $exceptions = [
        '["index"]',
        '["store"]',
        '["index", "store"]',
        '["create"]',
        '["show"]',
        '["edit"]',
        '["update"]',
        '["destroy"]',
        '["create", "show"]',
        '["edit", "update"]',
        '["create", "destroy"]',
        '["index", "store", "create", "show", "edit", "update", "destroy"]',
    ];
    protected array $redirects = [
        NULL,
        NULL,
        NULL,
        "https://google.com/",
        NULL,
        "/404",
        NULL,
        NULL
    ];

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        dump('RoutesSeeder - '.floor(memory_get_usage()/1024/1024)."МБ");
        $groups = RouteGroups::all();
        $list = $groups->pluck('id');
        $count = Routes::count();
        $num = $count ? Routes::offset(1)->first()->id : 0;
        Routes::factory(2)->make()->each(function ($item, $k) use ($groups, $list, $num){
            $num += ($k + 1);
            $item->position = $num;
            $item->resource = rand(0,1);
            $item->fallback = rand(0,1);
            $item->controller = collect($this->controllers)->first();
            $item->route = $this->routes[rand(0, count($this->routes)-1)];
            $item->method = $this->methods[rand(0, count($this->methods)-1)];
            $item->group_id = $list[rand(0, $groups->count()-1)];
            $item->middleware = $this->middlewares[rand(0, count($this->middlewares)-1)];
            $item->redirect = $this->redirects[rand(0, count($this->redirects)-1)];
            if($item->resource) {
                $item->route = 'resource';
                $item->controller = collect($this->controllers)->last();
                $item->fallback = 0;
                $item->only = $this->only[rand(0, count($this->only)-1)];
                $item->except = $this->exceptions[rand(0, count($this->exceptions)-1)];
            }
            $item->save();
        });
        unset($list, $groups, $count, $num);
        dump('RoutesSeeder завершено - '.floor(memory_get_usage()/1024/1024)."МБ");
    }

}
