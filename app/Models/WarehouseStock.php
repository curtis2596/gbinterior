<?php

namespace App\Models;

use Exception;

class WarehouseStock extends BaseModel
{
    protected static array $unique_fields = [
        "item_id",
        "warehouse_id"
    ];

    protected $table = 'warehouse_stocks';
    protected $fillable = [
        'warehouse_id',
        'item_id',
        'opening_qty',
        'qty'
    ];

    /**
     * set extra relationship array to overcome problem of accidential delete
     * this variable used in Controller.php -> delete()
     */
    public array $child_model_class = [];

    public function item()
    {
        return $this->belongsTo(Item::class, "item_id");
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, "warehouse_id");
    }

    public function city()
    {
        return $this->belongsTo(City::class, "city_id");
    }

    public function getAvailabilitQty()
    {
        return $this->opening_qty + $this->qty;
    }

    public static function updateQty($warehouse_id, $item_id, $qty)
    {
        $model = self::where("warehouse_id", $warehouse_id)->where("item_id", $item_id)->first();

        // dd($model->toArray());

        if ($qty < 0)
        {
            $ware_house_name = Warehouse::findOrFail($warehouse_id);
            $ware_house_name = $ware_house_name->getDisplayName();
            $item_name = Item::findOrFail($item_id);
            $item_name = $item_name->getDisplayName();

            if (!$model)
            {
                throw new Exception("Warehouse $ware_house_name have no any Item $item_name");
            }

            $available_qty = $model->getAvailabilitQty();

            if ($available_qty < abs($qty))
            {
                throw new Exception("Warehouse $ware_house_name has $available_qty qty of $item_name");
            }

            $model->qty += $qty;
            
            $model->save();
        }
        else if ($qty > 0)
        {
            if (!$model)
            {
                $warehouse_arr = [
                    "warehouse_id" => $warehouse_id,
                    "item_id" => $item_id,
                    "qty" => $qty,
                ];

                WarehouseStock::create($warehouse_arr);
            }
            else
            {
                $model->qty += $qty;
            
                $model->save();
            }
        }

    }
}
