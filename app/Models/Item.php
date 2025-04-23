<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Item extends BaseModel
{
    public $appends = [
        "max_gst_per",
        "display_name"
    ];

    public array $child_model_class = [
        WarehouseStock::class => [
            "foreignKey" => "item_id",
            "preventDelete" => false
        ],
        PurchaseOrderItem::class => [
            "foreignKey" => "item_id",
            "preventDelete" => true
        ],
        PurchaseBillItem::class => [
            "foreignKey" => "item_id",
            "preventDelete" => true
        ],
        EnquiryItem::class => [
            "foreignKey" => "product_id",
            "preventDelete" => true
        ],
        QuotationItem::class => [
            "foreignKey" => "product_id",
            "preventDelete" => true
        ],
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class, "unit_id");
    }

    public function itemCategory()
    {
        return $this->belongsTo(ItemCategory::class, "item_category_id");
    }

    public function itemGroup()
    {
        return $this->belongsTo(ItemGroup::class, "item_group_id");
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, "brand_id");
    }

    public function getMaxGstPerAttribute()
    {
        return $this->tax_rate > 0 ? $this->tax_rate : 40;
    }

    protected static function _getList(Builder $builder, String $id, String $value)
    {
        if (!$id)
        {
            $id = "id";
        }

        if (!$value)
        {
            $value = "display_name_with_category";
        }

        $records = $builder->get();

        $list = [];

        $category_tree_list = ItemCategory::getTreeList();

        foreach($records as $record)
        {
            if ($value == "display_name")
            {
                $list[$record->{$id}] = $record->getDisplayName();
            }
            else if ($value == "display_name_with_category")
            {
                $category_name = "";

                if (isset($category_tree_list[$record->item_category_id]))
                {
                    $category_name = $category_tree_list[$record->item_category_id] . "->";
                }

                $list[$record->{$id}] = $category_name . $record->getDisplayName();
            }
            else
            {
                $list[$record->{$id}] = $record->{$value};
            }
        }

        return $list;
    }

    public function getDisplayName()
    {        
        $name =  $this->name;

        if ($this->specification)
        {
            $name .= " (" . $this->specification . " )";
        }

        return $name;
    }

    public function getDisplayNameAttribute()
    {
        return $this->getDisplayName();
    }

    public static function getUnitList()
    {
        $records = static::select("id", "unit_id")->with([
            "unit"
        ])->get();

        // dd($records->toArray()); 

        $list = [];

        foreach($records as $record)
        {
            $list[$record->id] = $record->unit->code;
        }

        return $list;
    }
}
