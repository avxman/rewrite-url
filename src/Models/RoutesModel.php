<?php

namespace Avxman\Rewrite\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Avxman\Rewrite\Interfaces\RewriteInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class RoutesModel extends Model implements RewriteInterface
{
    use HasFactory;

    public function getURL(){
        if($this->pattern_url){
            $url = "";
            $pattern = json_decode($this->pattern_url);
            if($pattern->G??NULL){
                $groups = Cache::get("link_groups")->whereIn("id", $pattern->G)->sortBy("position")->keyBy("id")->all();
                collect($groups)->each(function ($self) use (&$url){
                    if($self->prefix && !empty($self->prefix)) $url .= Str::of($self->prefix)->start('/');
                });
            }
            if($pattern->R??NULL){
                $routes = Cache::get("link_routes")->whereIn("id", $pattern->R)->sortBy("position")->keyBy("id")->all();
                collect($routes)->each(function ($self) use (&$url){
                    if($self->uri && !empty($self->uri)) $url .= Str::of($self->uri)->start('/');
                });
            }
            $this->url = !empty($url) ? Str::of($url)->finish(Str::of($this->uri)->start('/'))."" : "";
        }
        else{
            $this->url = $this->uri;
        }
        return $this;
    }

    public function getParentsURL(){
        if($this->pattern_url){
            $url = "";
            $pattern = json_decode($this->pattern_url);
            if($pattern->G??NULL){
                $groups = Cache::get("link_groups")->whereIn("id", $pattern->G)->sortBy("position")->keyBy("id")->all();
                collect($groups)->each(function ($self) use (&$url){
                    if($self->prefix && !empty($self->prefix)) $url .= Str::of($self->prefix)->start('/');
                });
            }
            if($pattern->R??NULL){
                $routes = Cache::get("link_routes")->whereIn("id", $pattern->R)->sortBy("position")->keyBy("id")->all();
                collect($routes)->each(function ($self) use (&$url){
                    if($self->uri && !empty($self->uri)) $url .= Str::of($self->uri)->start('/');
                });
            }
            $this->url = $url;
        }
        else{
            $this->url = $this->uri;
        }
        return $this;
    }

    public function ScopeGetChildURL($scope){}

    public function ScopeGetCollection($scope){}

    public function ScopeGetParentsCollection($scope){}

    public function ScopeGetChildCollection($scope){}

}
