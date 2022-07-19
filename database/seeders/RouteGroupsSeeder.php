<?php

namespace Database\Seeders;

use Avxman\Rewrite\Models\RouteGroups;
use Illuminate\Database\Seeder;

class RouteGroupsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        dump('RouteGroupsSeeder - '.floor(memory_get_usage()/1024/1024)."МБ");
        $list = RouteGroups::all();
        $items = $list->pluck('id');
        $items = collect($items)->push(NULL)->toArray();
        $count = $list->count();
        $num = $count ? collect($list)->last()->id : 0;
        RouteGroups::factory(2)->make()->each(function ($item, $k) use ($list, $items, $num, $count){
            $num += ($k + 1);
            $item->parent_id = $count ? $items[rand(0, $count)] : NULL;
            $item->position = $num;
            $item->save();
        });
        unset($list, $items, $count, $num);
        dump('RouteGroupsSeeder завершено - '.floor(memory_get_usage()/1024/1024)."МБ");
    }
}
