<?php

namespace Avxman\Rewrite\Models;

use Database\Factories\RouteGroupsFactory;
use Illuminate\Support\Facades\Cache;

class RouteGroups extends RoutesModel
{

    protected static function newFactory()
    {
        return RouteGroupsFactory::new();
    }

    public function routes(){
        return $this->hasMany(Routes::class, 'group_id')->orderBy('position');
    }

    /**
     * @param $scope
     * @return Model
     */
    public function ScopeTree($scope){
        return $scope->orderBy('parent_id','asc')->orderBy('position','asc');
    }

    /**
     * @param $scope
     * @return \Illuminate\Support\Collection
     */
    public function ScopeLists($scope){
//        $query = Cache::has('routes') ? Cache::get('routes') : $scope->get();
        $query = $scope->get();
        $children = $query->whereNotNull('parent_id')->keyBy('id');
        $child = $children->groupBy('parent_id');
        $query->keyBy('id')->map(function ($self, $key) use ($child){
            $self->children = $child->has($key) ? $child[$key]->sortBy('position')->keyBy('id') : collect();
        });
        return $query->whereNotIn('id', $children->pluck('id'))->keyBy('id');
    }

}
