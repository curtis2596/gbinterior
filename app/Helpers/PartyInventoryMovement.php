<?php

namespace App\Models;

use App\Models\WarehouseStock;

class PartyInventoryMovement extends BaseModel
{
    protected $dates = ['challan_date'];

    public function partyinventoryitem()
    {
        return $this->hasMany(PartyInventoryMovementItem::class, 'party_inventory_movement_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function party()
    {
        return $this->belongsTo(Party::class, 'party_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    // public static function processStockMovement($data)
    // {        
    //     WarehouseStock::updateQty($data['warehouse_id'], $data['item_id'], -1 * $data['qty']);

    //     return self::create($data);
    // }
}
