<?php

namespace App\Models;



class ItemConversionAndMovement extends BaseModel
{
    protected $dates = ['challan_date'];

    public array $child_model_class = [
        LedgerTransaction::class => [
            "foreignKey" => "manufacture_id",
            "preventDelete" => false
        ],        
    ];

    public static function boot()
    {
        parent::boot();

        self::deleting(function ($model) {
            WarehouseStock::updateQty($model->from_warehouse_id, $model->from_item_id, $model->from_qty);
            WarehouseStock::updateQty($model->to_warehouse_id, $model->to_item_id, -1 * $model->to_qty);
        });
    }

    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function fromitem()
    {
        return $this->belongsTo(Item::class, 'from_item_id');
    }

    public function toitem()
    {
        return $this->belongsTo(Item::class, 'to_item_id');
    }

    public function process()
    {
        return $this->belongsTo(Process::class, 'process_id');
    }


    public static function processStockMovement($data)
    {
        WarehouseStock::updateQty($data['from_warehouse_id'], $data['from_item_id'], -1 * $data['from_qty']);
        WarehouseStock::updateQty($data['to_warehouse_id'], $data['to_item_id'], $data['to_qty']);

        return self::create($data);
    }
}
