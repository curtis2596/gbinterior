<?php

namespace App\Models;

class Party extends BaseModel
{
    public $appends = [
        "display_name",
        "full_address"
    ];

    public array $child_model_class = [
        LedgerAccount::class => [
            "foreignKey" => "party_id",
            "preventDelete" => false
        ],
        PurchaseOrder::class => [
            "foreignKey" => "party_id",
            "preventDelete" => true
        ],
        PurchaseBill::class => [
            "foreignKey" => "party_id",
            "preventDelete" => true
        ],
        SaleOrder::class => [
            "foreignKey" => "party_id",
            "preventDelete" => true
        ],
        NewComplaint::class => [
            "foreignKey" => "customer_id",
            "preventDelete" => true
        ],
        Enquiry::class => [
            "foreignKey" => "party_id",
            "preventDelete" => true
        ],
        Warehouse::class => [
            "foreignKey" => "party_id",
            "preventDelete" => true
        ]
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id')->withDefault(['name' => '-']);
    }
    
    public function ledgerAccount()
    {
        return $this->hasOne(LedgerAccount::class, "party_id");
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
    }
    
    public function afterSave()
    {
        if ($this->is_supplier && $this->is_customer)
        {
            $ledgerCategory = LedgerCategory::where("code", LedgerCategory::CODE_creditor_debitor)->first();
        }
        else if ($this->is_supplier)
        {
            $ledgerCategory = LedgerCategory::where("code", LedgerCategory::CODE_creditor)->first();
        }
        else if ($this->is_customer)
        {
            $ledgerCategory = LedgerCategory::where("code", LedgerCategory::CODE_debitor)->first();
        }
        else
        {
            $ledgerCategory = LedgerCategory::where("code", LedgerCategory::CODE_creditor_debitor)->first();
        }
        // dd($ledgerCategory);
        if (!$ledgerCategory)
        {         
            throw_exception("Ledger Category Not Found");
        }
        
        $ledgerAccount = $this->ledgerAccount()->first();

        $save_arr = [
            "opening_balance_type" => $this->opening_balance_type,
            "opening_balance" => $this->opening_balance,
            "ledger_category_id" => $ledgerCategory->id,
            "can_delete" => 0,
        ];

        if ($ledgerAccount)
        {
            $ledgerAccount->update($save_arr);
        }
        else
        {
            $ledgerAccount = new LedgerAccount();

            $save_arr['type'] = LedgerAccount::TYPE_PARTY;
            $save_arr['party_id'] = $this->id;
            $save_arr['is_active'] = 1;

            $ledgerAccount->fill($save_arr);

            $ledgerAccount = $this->ledgerAccount()->save($ledgerAccount);

            if (!$ledgerAccount)
            {
                throw_exception("Fail To Save Ledger Account");
            }
        }
    }

    public function getDisplayName()
    {
        $name = $this->name;

        if ($this->is_job_worker)
        {
            $name .= " (Job-Worker)";
        }

        return $name;
    }

    public function getDisplayNameAttribute()
    {
        return $this->getDisplayName();
    }

    public function getFullAddressAttribute()
    {
        $address = "";

        if ($this->address)
        {
            $address .= $this->address;
        }

        if (!$this->relationLoaded('city')) {
            $this->load('city');
        }

        if (isset($this->city->name) && $this->city->name)
        {
            if ($address)
            {
                $address .= ", ";
            }

            $address .= $this->city->name;
        }

        if (!$this->city->relationLoaded('state')) {
            $this->city->load('state');
        }

        if (isset($this->city->state->name) && $this->city->state->name)
        {
            if ($address)
            {
                $address .= ", ";
            }

            $address .= $this->city->state->name;
        }

        return $address;
    }
}
