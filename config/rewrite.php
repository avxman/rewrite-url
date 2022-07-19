<?php

return [

    'enable' => true,

    'load_first' => true,

    'enable_localization' => false,

    //'localization' => \LaravelLocalization::class,

    'provider' => \Avxman\Rewrite\Providers\UrlServiceProvider::class,

    'routes' => [

        'web',

    ],

    'middleware_groups'=>[

        'admin_group' => [
            \Avxman\Rewrite\Middlewares\Rewrite::class,
        ],

    ],

    'middleware_routes'=>[

        'admin_route' => \Avxman\Rewrite\Middlewares\Rewrite::class,

    ],

    'models' => [

        'routes'=> \Avxman\Rewrite\Models\Routes::class,
        'route_groups'=> \Avxman\Rewrite\Models\RouteGroups::class,

    ],

    'controllers'=>[
        base_path('app/Http/Controllers'),
    ],

];
