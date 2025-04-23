<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Builder;

class LedgerAccount extends BaseModel
{
    protected static $model_cache_key = 'LedgerAccount';

    CONST TYPE_SIMPLE = "simple";
    CONST TYPE_PARTY = "party";
    CONST TYPE_EMPLOYEE = "employee";

    const CODE_cash = "cash";
    const CODE_purchase = "purchase";
    const CODE_sale = "sale";
    const CODE_stock = "stock";
    const CODE_igst = "igst";
    const CODE_sgst = "sgst";
    const CODE_cgst = "cgst";
    const CODE_manufacturing = "manufacturing";

    const TYPE_LIST = [
        self::TYPE_SIMPLE => "Simple",
        self::TYPE_PARTY => "Party",
        // self::TYPE_EMPLOYEE => "Employee",
    ];

    const CODE_LIST = [
        self::CODE_cash,
        self::CODE_cgst, 
        self::CODE_igst,
        self::CODE_manufacturing,
        self::CODE_purchase,
        self::CODE_sale,
        self::CODE_sgst,
    ];

    const ACCCOUNT_CODES_WHICH_CAN_NEVER_NEGTIVE = [
    ];

    protected $appends = [
        'name_id',
    ];

    /**
     * set extra relationship array to overcome problem of accidential delete
     * this variable used in Controller.php -> delete()
     */
    public Array $child_model_class = [
        LedgerPayment::class => [
            "foreignKey" => ["from_account_id", "to_account_id"],
            "preventDelete" => true
        ],
        LedgerTransaction::class => [
            "foreignKey" => ["main_account_id", "other_account_id"],
            "preventDelete" => true
        ],
    ];

    public function party()
    {
        return $this->belongsTo(Party::class, "party_id");
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, "employee_id");
    }

    public function ledgerCategory()
    {
        return $this->belongsTo(LedgerCategory::class, "ledger_category_id");
    }

    public function getDisplayName()
    {
        $name = $this->name;

        if ($this->type == self::TYPE_PARTY)
        {
            if (!isset($this->party->name))
            {
                throw_exception("party->name is not set");

            }
            $name = $this->party->name;
        }
        else if ($this->type == self::TYPE_EMPLOYEE)
        {
            if (!isset($this->employee->name))
            {
                throw_exception("employee->name is not set");
            }

            $name = $this->employee->name;
        }

        return $name;
    }

    public function getNameIdAttribute()
    {
        $name = $this->getDisplayName();
        
        $name .= "-" . $this->id;

        return $name;
    }

    public static function getList(String $id = "id", String $value = "nameId", $conditions = [], $order_by = "name", $order_dir = "ASC")
    {
        $builder = self::query();

        $conditions["is_active"] = 1;

        foreach($conditions as $k => $v)
        {
            if (is_array($v))
            {
                $builder->whereIn($k, $v);
            }
            else
            {
                $builder->where($k, $v);
            }
        }

        $records = $builder->with([
            "party" => function($q) {
                $q->select(["id", "name"]);
            },
            "employee" => function($q) {
                $q->select(["id", "name"]);
            },
        ])->orderBy($order_by, $order_dir)->get();

        $list = [];

        foreach($records as $k => $record)
        {
            $name = "";

            if ($value == "display_name")
            {
                $name = $record->getDisplayName();
            }            
            else
            {            
                $name = $record->{$value};
            }

            $list[$record->{$id}] = $name;
        }

        return $list;
    }

    public static function getListCache(String $id = "id", String $value = "nameId", $order_by = "name", $order_dir = "ASC")
    {
        throw_exception("Because List is comes from diffrent models, so can not cache data");
    }

    public static function getByCode($code)
    {
        $model = self::where("code", $code)->first();

        if (!$model)
        {
            abort(\ACTION_NOT_PROCEED, "Ledger Account : code : $code not found");
        }

        return $model;
    }

    public function updateBalance($amount)
    {
        $balance = $this->getBalance();

        $new_balance = $balance;

        $new_balance += $amount;

        if (in_array($this->code, self::ACCCOUNT_CODES_WHICH_CAN_NEVER_NEGTIVE))
        {
            if ($new_balance < 0)
            {
                throw new Exception("System Account : $this->name can not be negtive");
            }
        }

        if ($this->ledgerCategory->code == LedgerCategory::CODE_bank)
        {
            if ($new_balance < 0)
            {
                throw new Exception("Bank Account : $this->name have balance $balance, which is more than " . abs($amount));
            }
        }

        $this->current_balance += $amount;

        $this->save();
    }

    public function getOpeningBalance()
    {
        if (in_array($this->code, self::ACCCOUNT_CODES_WHICH_CAN_NEVER_NEGTIVE))
        {
            return $this->opening_balance;
        }

        $balance = $this->opening_balance;
        if ($this->opening_balance_type == "credit" && $balance > 0)
        {
            $balance *= -1;
        }

        return $balance;
    }

    public function getOpeningBalanceWithCrOrDr()
    {
        $balance = $this->getOpeningBalance();
        if ($balance == 0)
        {
            return 0;
        }

        if (in_array($this->code, self::ACCCOUNT_CODES_WHICH_CAN_NEVER_NEGTIVE))
        {
            return $balance;
        }

        $balance_types = laravel_constant("balance_types");

        $text = $balance_types[$this->opening_balance_type] ?? "";

        $text .= " : "  . $balance;

        return $text;
    }

    public function getBalance()
    {
        return $this->getOpeningBalance() + $this->current_balance;
    }

    public function getBalanceWithCrOrDr()
    {
        $balance = $this->getBalance();

        if (in_array($this->code, self::ACCCOUNT_CODES_WHICH_CAN_NEVER_NEGTIVE))
        {
            return $balance;
        }

        return amount_with_dr_cr($balance);
    }

    public function getCurrentBalanceWithCrOrDr()
    {
        if (in_array($this->code, self::ACCCOUNT_CODES_WHICH_CAN_NEVER_NEGTIVE))
        {
            return $this->current_balance;
        }

        return amount_with_dr_cr($this->current_balance);
    }

    
}
