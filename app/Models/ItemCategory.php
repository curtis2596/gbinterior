<?php

namespace App\Models;

use App\Helpers\Util;
use Illuminate\Support\Facades\Cache;

class ItemCategory extends BaseModel
{
    public function parent()
    {
        return $this->belongsTo(ItemCategory::class, 'parent_id');
    }
 
    public function children()
    {
        return $this->hasMany(ItemCategory::class, 'parent_id');
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {

            if (!$model->parent_id)
            {
                $model->parent_id = 0;
            }
        });

        self::updating(function ($model) {

            if (!$model->parent_id)
            {
                $model->parent_id = 0;
            }
        });
    }


    public static function getTreeList()
    {
        $cache_key = self::getModelCacheKey() . "list-id-name-tree";

        if (Cache::has($cache_key))
        {
            return Cache::get($cache_key);
        }

        $item_category_records = self::get()->toArray();

        $item_category_tree_records = Util::getTreeArray($item_category_records, "parent_id");        

        $item_category_list = Util::getTreeListArray($item_category_tree_records, "id", "name", false, false);

        self::addCache($cache_key, $item_category_list);

        return $item_category_list;
    }
}
