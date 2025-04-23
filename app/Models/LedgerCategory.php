<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class LedgerCategory extends BaseModel
{
    CONST TYPE_LIABILITTY = "liability";
    CONST TYPE_ASSET = "asset";
    CONST TYPE_CONTRA = "contra";

    const TYPE_LIST = [
        self::TYPE_LIABILITTY => "Liability",
        self::TYPE_ASSET => "Asset",
        self::TYPE_CONTRA => "contra",
    ];

    const CODE_payable = "payable";
    const CODE_loan = "loan";
    const CODE_tax = "tax";
    const CODE_cash = "cash";
    const CODE_stock = "stock";
    const CODE_receivable = "receivable";
    const CODE_bank = "bank";
    const CODE_debitor = "debitor";
    const CODE_creditor = "creditor";
    const CODE_creditor_debitor = "creditor-debitor";

    const CODES_REQUIRED_TO_EXIST_DATABASE = [
        self::CODE_payable,
        self::CODE_receivable,
        self::CODE_stock,
        self::CODE_cash,
        self::CODE_tax,
    ];

    /**
     * set extra relationship array to overcome problem of accidential delete
     * this variable used in Controller.php -> delete()
     */
    public Array $child_model_class = [
        LedgerAccount::class => [
            "foreignKey" => "ledger_category_id",
            "preventDelete" => true
        ]
    ];

    protected $appends = [
        'name_type',
    ];

    public function getNameTypeAttribute()
    {
        $name = $this->name . " (" . self::TYPE_LIST[$this->type] . ")";

        return $name;
    }

    protected static function _getList(Builder $builder, String $id, String $value)
    {
        $records = $builder->get();

        $list = [];

        foreach($records as $record)
        {
            $list[$record->{$id}] = $record->{$value};
        }

        return $list;
    }

    public static function getList(String $id = "id", String $value = "name_type", $conditions = [], $order_by = "name", $order_dir = "asc")
    {
        return parent::getList($id, $value, $conditions, $order_by, $order_dir);
    }

    public static function getListCache(String $id = "id", String $value = "name_type", $order_by = "name", $order_dir = "asc")
    {
        return parent::getListCache($id, $value, $order_by, $order_dir);
    }


    public static function getByCode($code)
    {
        $record = static::where("code", $code)->get();

        if (!$record)
        {
            throw_exception("Wrong Code : $code");
        }

        return $record;
    }
}
