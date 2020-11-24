<?php

namespace Avxman\Rewrite\URL\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class UrlServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void info
     */
    public function boot(){

        $this->configureRateLimiting();

        $this->group([],function (){
            foreach (Config()->get('rewrite.routes') as $k=>$v){
                Route::group([], base_path('routes/'.$v.'.php'));
            }
        });

//        $this->middleware('web')
//            ->group(base_path('routes/avxman/web.php'));

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
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }

}
