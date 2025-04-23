<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobOrderReceiveItem extends BaseModel
{
    use HasFactory;

    public function toItem()
    {
        return $this->belongsTo(Item::class, 'to_item_id');
    }

    public function jobOrderItem()
    {
        return $this->belongsTo(JobOrderItem::class, 'job_order_item_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'receive_warehouse_id', 'id');
    }
    public function party()
    {
        return $this->belongsTo(Party::class, 'receive_party_id', 'id');
    }

    public static function boot()
    {
        parent::boot();

        self::created(function ($model) {
            $model->afterSave();
        });

        self::updated(function ($model) {
            $model->afterSave();
        });

        self::deleted(function ($model) {
            $model->afterDelete();
        });
    }

    protected function afterSave()
    {
        if (!empty($this->receive_warehouse_id)) {
            WarehouseStock::updateQty($this->receive_warehouse_id, $this->to_item_id, $this->to_qty);
        }
    }

    protected function afterDelete()
    {
        if (!empty($this->receive_warehouse_id)) {
            WarehouseStock::updateQty($this->receive_warehouse_id, $this->to_item_id, -1 * $this->to_qty);
        }
    }
}
