<?php

namespace Avxman\Rewrite\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class UrlServiceProvider extends ServiceProvider
{

    // collect($this->getMiddleware())->keys(), collect($this->getMiddlewareGroups())->keys());

    /**
     *
    */
    protected \Illuminate\Support\Collection $routes_list;

    /**
     *
    */
    protected \Illuminate\Support\Collection $routes_group_list;

    /**
     * @var \Illuminate\Support\Collection список параметров из конфигурационного файла rewrite
    */
    protected \Illuminate\Support\Collection $config;

    /**
     * @return string возвращаем локализацию en, ua, ru и т.д. но только одну
    */
    protected function getPrefixLocalization(): string
    {
        return $this->config->has('localization') ? $this->config->get('localization')::setLocale() : "";
    }

    /**
     * @param $routes
     * @return string
     */
    public function writeRoute($routes): string
    {
        $result = '';
        $routes->each(function ($item) use (&$result){
            if($item['redirect']){
                $result .= "Route::redirect('{$item['uri']}', '{$item['redirect']}')->name('{$item['name']}')";
            }
            elseif($item['fallback']){
                $result .= "Route::fallback(".Str::of($item['controller'])->start('\\')->finish('::class').".'@{$item['method']}')";
            }
            else{
                $route = $item['resource'] ? 'resource' : $item['route'];
                $result .= "Route::{$route}('{$item['uri']}', ";
                $result .= ($item['resource'] ? Str::of($item['controller'])->start('\\')->finish('::class') : Str::of($item['controller'])->start('\\')->finish('::class').".'@{$item['method']}'").")";
                $result .= ($item['resource'] ? "->names" : "->name")."('{$item['name']}')";
                if($item['resource'] && !empty($item['only'])) $result .= "->only({$item['only']})";
                if($item['resource'] && !empty($item['except'])) $result .= "->except({$item['except']})";
            }
            if($item['middleware']) $result .= "->middleware({$item['middleware']});";
            else $result .= ";";
        });
        return $result;
    }

    /**
     * @param $group
     * @return string
     */
    public function writeGroup($group): string
    {
        $all = '';
        $group->each(function ($route) use (&$all){
            $result = "Route::";
            $prefix = $route->prefix ? ($this->config->get('enable_localization') ? "prefix('{$this->getPrefixLocalization()}')" : "prefix('{$route->prefix}')") : "";
            $middleware = $route->middleware ? ($route->prefix ? "->middleware({$route->middleware})" : "middleware({$route->middleware})") : "";
            $result .= $prefix.$middleware;
            $result .= empty($prefix) && empty($middleware) ? "group([], function(){" : "->group(function(){";
            $result .= $this->writeRoute($route->routes);
            if($route->children->count()) {
                $result .= $this->writeGroup($route->children);
                $result .= "});";
            }
            else{
                $result .= "});";
            }
            $all .= $result;
        });
        return $all;
    }

    /**
     * @param $routes
     */
    public function startGroup($routes) : void{
        /**
         * @var $modelRoutes \Avxman\Rewrite\Models\Routes
         */
//        $modelRoutes = $this->config->get('rewrite.models.routes');

        if($this->config->get('enable')) {
            collect($this->config->get('middleware_groups'))->each(function ($item, $index){
                $this->middlewareGroup($index, $item);
            });

            collect($this->config->get('middleware_routes'))->each(function ($item, $index){
                $this->aliasMiddleware($index, $item);
            });

            eval($this->writeGroup($routes));
        }

    }

    /**
     * @param $list
     * @param $urls
     * @return \Illuminate\Support\Collection
     */
    private function getUrlRecurse($list, $urls): \Illuminate\Support\Collection
    {
        $res = collect();
        $list->each(function ($self) use (&$res, $urls){
            if(!empty($urls) && !empty($self->prefix))
                $self->url = Str::finish($urls, Str::start($self->prefix, "/"));
            elseif(!empty($self->prefix)) $self->url = Str::start($self->prefix, "/");
            elseif(!empty($urls)) $self->url = $urls;
            else $self->url = "";
            $url = $self->url;
            if($self->children){
                $self->children = $self->children->map(function ($child) use ($self, $url){
                    if(!empty($child->prefix) && !empty($url))
                        $child->url = Str::finish($url, Str::start($child->prefix, "/"));
                    elseif(!empty($child->prefix)) $child->url = $url.Str::start($child->prefix, "/");
                    elseif(!empty($self->url)) $child->url = $url.Str::start($self->url, "/");
                    else $child->url = "";
                    if($child->children){
                        $child->children = $this->getUrlRecurse($child, $url);
                    }
                    if($child->routes){
                        $route = $child->routes->map(function ($route) use ($url, $child){
                            if(!empty($route->uri) && !empty($child->url))
                                $route->url = Str::start($child->url, "/").Str::start($route->uri, "/");
                            elseif(!empty($route->uri)) $route->url = Str::start($route->uri, "/");
                            elseif(!empty($child->url)) $route->url = Str::start($child->url, "/");
                            else $route->url = "";
                            return $route;
                        })->keyBy("id");
                        $child->setRelation("routes", $route);
                    }
                    return $child;
                });
            }
            if($self->routes){
                $route = $self->routes->map(function ($route) use ($url){
                    if(!empty($route->uri) && !empty($url))
                        $route->url = $url.Str::start($route->uri, "/");
                    elseif(!empty($route->uri)) $route->url = $url.Str::start($route->uri, "/");
                    elseif(!empty($url)) $route->url = $url;
                    else $route->url = "";
                    return $route;
                })->keyBy("id");
                $self->setRelation("routes", $route);
            }
            $res->push($self);
        });
        return $res;
    }

    /**
     * @param $list
     * @return Collection|\Illuminate\Support\Collection
     */
    private function getUrl($list) : Collection{
        return $list->map(function ($self){
            $self->url = !empty($self->prefix) ? Str::start($self->prefix, "/") : "";
            if($self->children){
                $self->children = $self->children->map(function ($child) use ($self){
                    if(!empty($self->prefix) && !empty($child->prefix))
                        $child->url = Str::start($self->prefix, "/").Str::start($child->prefix, "/");
                    elseif(!empty($child->prefix)) $child->url = Str::start($child->prefix, "/");
                    elseif(!empty($self->prefix)) $child->url = Str::start($self->prefix, "/");
                    else $child->url = "";
                    if($child->children){
                        $child->children = $this->getUrlRecurse($child->children, $self->url);
                    }
                    if($child->routes){
                        $route = $child->routes->map(function ($route) use ($self, $child){
                            if(!empty($route->uri) && !empty($child->url))
                                $route->url = Str::start($child->url, "/").Str::start($route->uri, "/");
                            elseif(!empty($route->uri)) $route->url = Str::start($route->uri, "/");
                            elseif(!empty($child->url)) $route->url = Str::start($child->url, "/");
                            else $route->url = "";
                            return $route;
                        })->keyBy("id");
                        $child->setRelation("routes", $route);
                    }
                    return $child;
                });
            }
            if($self->routes){
                $route = $self->routes->map(function ($route) use ($self){
                    $route->url = !empty($route->uri) ? Str::of($self->prefix)->start("/")->finish(Str::of($route->uri)->start("/")) : "";
                    if(!empty($route->uri) && !empty($self->prefix))
                        $route->url = Str::start($self->prefix, "/").Str::start($route->uri, "/");
                    elseif(!empty($route->uri)) $route->url = Str::start($route->uri, "/");
                    elseif(!empty($self->prefix)) $route->url = Str::start($self->prefix, "/");
                    else $route->url = "";
                    return $route;
                })->keyBy("id");
                $self->setRelation("routes", $route);
            }
            return $self;
        });
    }

    /**
     *
    */
    private function ggetUrlRoutePattern(){
        dump($this->routes_group_list);
        dump($this->routes_list);
    }

    /**
     *
    */
    private function ggetUrlRoute($list, $url) : Collection{
        return $list->map(function ($self) use ($url){
            $this->routes_list->push($self);
            $self->url = $url.Str::start($self->uri, "/");
            return $self;
        })->keyBy("id");
    }

    /**
     *
    */
    private function ggetUrl(Collection $list, string $url = "") : Collection{
        return $list->map(function ($self) use ($url){
            $this->routes_group_list->push($self);
            $self->url = !empty($self->prefix) ? $url.Str::start($self->prefix, "/") : $url;
            if($self->routes->count()) $self->setRelation("routes", $this->ggetUrlRoute($self->routes, $self->url));
            if($self->children->count()) $self->children = $this->ggetUrl($self->children, $self->url);
            return $self;
        });
    }

    /**
     *  Загрузка всех роутов
     */
    public function loadRoutes() : void{

        /**
         * @var $modelRouteGroups \Avxman\Rewrite\Models\RouteGroups
         */
        $modelRouteGroups = $this->config->get('models')['route_groups'];
        /**
         * @var $modelRoutes \Avxman\Rewrite\Models\Routes
         */
        $modelRoutes = $this->config->get('models')['routes'];

//        Cache::flush();
        $this->routes_group_list = collect();
        $this->routes_list = collect();
        $model = (new $modelRouteGroups)->with('routes')->tree()->lists();
        $this->ggetUrl($model);
//        dd($this->ggetUrlRoutePattern());
//        dd($this->ggetUrl($model));

        if(!Cache::has('routes')) {
//            $model = (new $modelRouteGroups)->with('routes')->tree();
            $model = (new $modelRouteGroups)->with('routes')->tree()->lists();
            Cache::add('routes', $this->getUrl($model));
            unset($model);
        }
//        else $routes = Cache::get('routes');

        if(!Cache::has('link_groups')){
            $model = (new $modelRouteGroups);
            Cache::add('link_groups', $model->all());
            unset($model);
        }
//        else $routes = Cache::get('link_groups');

        if(!Cache::has('link_routes')){
            $model = (new $modelRoutes);
            Cache::add('link_routes', $model->all());
            unset($model);
        }
//        else $routes = Cache::get('link_routes');

//        Cache::forget('routes');
//        Cache::forget('link_group');
//        Cache::forget('link_routes');
//        Cache::flush();

        $model = (new $modelRoutes)->find(1);


//        dd(Cache::get('routes'));
//        dd($model->getURL());

        $this->startGroup((new $modelRouteGroups)->lists());

    }

    /**
     * Bootstrap any application services.
     *
     * @return void info
     */
    public function boot(){

        $config = $this->config = collect(Config()->get('rewrite'));

        $this->configureRateLimiting();

        if(!$config->get('load_first')){
            $this->loadRoutes();
        }

        $this->group([],function (){
            foreach ($this->config->get('routes') as $v){
                Route::group([], base_path('routes/'.$v.'.php'));
            }
        });

        if($config->get('load_first')){
            $this->loadRoutes();
        }

    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting() : void
    {
        RateLimiter::for('web', function (Request $request) {
            return Limit::perMinute(100)->by(optional($request->user())->id ?: $request->ip());
        });
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }

}
