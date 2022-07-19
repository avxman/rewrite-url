<?php

namespace Avxman\Rewrite\Interfaces;

interface RewriteInterface
{

    /**
     *
    */
    public function getURL();

    /**
     *
    */
    public function getParentsURL();

    /**
     *
     */
    public function ScopeGetChildURL($scope);

    /**
     *
    */
    public function ScopeGetCollection($scope);

    /**
     *
    */
    public function ScopeGetParentsCollection($scope);

    /**
     *
    */
    public function ScopeGetChildCollection($scope);

}
