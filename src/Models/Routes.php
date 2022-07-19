<?php

namespace Avxman\Rewrite\Models;

use Database\Factories\RoutesFactory;

class Routes extends RoutesModel
{
    protected static function newFactory(): RoutesFactory
    {
        return RoutesFactory::new();
    }
}
