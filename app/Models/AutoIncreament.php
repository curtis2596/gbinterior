<?php

namespace App\Models;

class AutoIncreament extends BaseModel
{
    const TYPE_EXPENSE = "expense"; 
    const TYPE_PURCHASE_ORDER = "purchase_order"; 
    const TYPE_PURCHASE_BILL = "purchase_bill"; 
    const TYPE_PURCHASE_RETURN = "purchase_return"; 
    const TYPE_SALE_ORDER = "sale_order"; 
    const TYPE_JOB_ORDER = "job_order"; 
    const TYPE_COMPLAINT = "complaint"; 
    const TYPE_SALE_BILL = "sale_bill"; 
    const TYPE_SALE_RETURN = "sale_return"; 
    const TYPE_PAYMENT = "payment"; 
    const TYPE_RECEIPT = "recipt"; 
    const TYPE_INVENTORY_MOVEMENT = "warehouse_inventory_movement"; 
    const TYPE_PARTY_INVENTORY_MOVEMENT = "party_inventory_movement"; 

    const TYPE_LIST = [
        self::TYPE_PAYMENT => 'Payment',
        self::TYPE_RECEIPT => 'Recipt',
        self::TYPE_PURCHASE_ORDER => 'Purchase Order',
        self::TYPE_PURCHASE_BILL => 'Purchase Bill',
        self::TYPE_PURCHASE_RETURN => 'Purchase Return',
        self::TYPE_SALE_ORDER => 'Sale Order',
        self::TYPE_JOB_ORDER => 'Job Order',
        self::TYPE_COMPLAINT => 'Complaint',
        self::TYPE_SALE_BILL => 'Sale Bill',
        self::TYPE_SALE_RETURN => 'Sale Return',
        self::TYPE_PARTY_INVENTORY_MOVEMENT => 'Party Inventory Movement Challan',
        self::TYPE_INVENTORY_MOVEMENT => 'Warehouse Inventory Movement Challan',
    ];

    /**
     * set extra relationship array to overcome problem of accidential delete
     * this variable used in Controller.php -> delete()
     */
    public Array $child_model_class = [
    ];

    public static function getNextCounter($type)
    {
        $record = static::where("type", $type)->first();

        if (!$record)
        {
            throw_exception("AutoIncreament : type $type not found");
        }

        $pattern = $record->pattern;

        $pattern = str_replace("YY", date("Y"), $pattern); //long year - 2025
        $pattern = str_replace("Y", date("y"), $pattern); //short year - 25
        $pattern = str_replace("MMM", date("F"), $pattern); // long month - August 

        $pattern = str_replace("MM", date("M"), $pattern); // short month - Aug
        $pattern = str_replace("M", date("m"), $pattern); // numerical month - 01

        $pattern = str_replace("counter", $record->counter + 1, $pattern);

        return $pattern;
    }

    public static function increaseCounter($type)
    {
        $record = static::where("type", $type)->first();

        if (!$record)
        {
            throw_exception("AutoIncreament : type $type not found");
        }

        $record->counter++;
        $record->save();
    }
}
