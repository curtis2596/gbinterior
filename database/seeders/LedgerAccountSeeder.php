<?php

namespace Database\Seeders;

use App\Models\LedgerAccount;
use App\Models\LedgerCategory;
use App\Models\Party;
use Exception;
use Illuminate\Database\Seeder;

class LedgerAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $default_save_arr = [
            "type" => "simple",
            "opening_balance" => 0,
            "opening_balance_type" => "",
            "current_balance" => 0,
            "is_active" => 1,
            "is_pre_defined" => 1,
            "can_delete" => 0,
        ];

        $legder_category_records = LedgerCategory::get();

        $ledger_category_code_id_list = [];

        foreach(LedgerCategory::CODES_REQUIRED_TO_EXIST_DATABASE as $code)
        {
            $is_found = false;
            foreach($legder_category_records as $legder_category_record)
            {
                if ($code == $legder_category_record->code)
                {
                    $is_found = true;
                    $ledger_category_code_id_list[$code] = $legder_category_record->id;
                }
            }

            if (!$is_found)
            {
                $this->command->error("Ledger Category : record of code : $code not exist");
                return false;
            }   
        }

        $code = LedgerAccount::CODE_stock;

        $ledger_account = LedgerAccount::where("code", $code)->first();
        
        $save_arr = array_merge($default_save_arr, [
            "code" => $code,
            "name" => "Stock",
            "ledger_category_id" => $ledger_category_code_id_list[LedgerCategory::CODE_stock],
        ]);

        if ($ledger_account)
        {
            $ledger_account->fill($save_arr);
            $ledger_account->update();
        }
        else
        {
            LedgerAccount::create($save_arr);
        }

        $code = LedgerAccount::CODE_purchase;

        $ledger_account = LedgerAccount::where("code", $code)->first();
        
        $save_arr = array_merge($default_save_arr, [
            "code" => $code,
            "name" => "Purchase Payable",
            "ledger_category_id" => $ledger_category_code_id_list[LedgerCategory::CODE_payable],
        ]);

        if ($ledger_account)
        {
            $ledger_account->fill($save_arr);
            $ledger_account->update();
        }
        else
        {
            LedgerAccount::create($save_arr);
        }

        
        $code = LedgerAccount::CODE_sale;

        $ledger_account = LedgerAccount::where("code", $code)->first();
        
        $save_arr = array_merge($default_save_arr, [
            "code" => $code,
            "name" => "Sale Receivable",
            "ledger_category_id" => $ledger_category_code_id_list[LedgerCategory::CODE_receivable],
        ]);

        if ($ledger_account)
        {
            $ledger_account->fill($save_arr);
            $ledger_account->update();
        }
        else
        {
            LedgerAccount::create($save_arr);
        }

        
        $code = LedgerAccount::CODE_manufacturing;

        $ledger_account = LedgerAccount::where("code", $code)->first();
        
        $save_arr = array_merge($default_save_arr, [
            "code" => $code,
            "name" => "Manufacture Payable",
            "ledger_category_id" => $ledger_category_code_id_list[LedgerCategory::CODE_payable],
        ]);

        if ($ledger_account)
        {
            $ledger_account->fill($save_arr);
            $ledger_account->update();
        }
        else
        {
            LedgerAccount::create($save_arr);
        }

        $code = LedgerAccount::CODE_igst;

        $ledger_account = LedgerAccount::where("code", $code)->first();
        
        $save_arr = array_merge($default_save_arr, [
            "code" => $code,
            "name" => "IGST Payable",
            "ledger_category_id" => $ledger_category_code_id_list[LedgerCategory::CODE_tax],
        ]);

        if ($ledger_account)
        {
            $ledger_account->fill($save_arr);
            $ledger_account->update();
        }
        else
        {
            LedgerAccount::create($save_arr);
        }


        $code = LedgerAccount::CODE_sgst;

        $ledger_account = LedgerAccount::where("code", $code)->first();
        
        $save_arr = array_merge($default_save_arr, [
            "code" => $code,
            "name" => "SGST Payable",
            "ledger_category_id" => $ledger_category_code_id_list[LedgerCategory::CODE_tax],
        ]);

        if ($ledger_account)
        {
            $ledger_account->fill($save_arr);
            $ledger_account->update();
        }
        else
        {
            LedgerAccount::create($save_arr);
        }
        

        $code = LedgerAccount::CODE_cgst;

        $ledger_account = LedgerAccount::where("code", $code)->first();
        
        $save_arr = array_merge($default_save_arr, [
            "code" => $code,
            "name" => "CGST Payable",
            "ledger_category_id" => $ledger_category_code_id_list[LedgerCategory::CODE_tax],
        ]);

        if ($ledger_account)
        {
            $ledger_account->fill($save_arr);
            $ledger_account->update();
        }
        else
        {
            LedgerAccount::create($save_arr);
        }


        $code = LedgerAccount::CODE_cash;

        $ledger_account = LedgerAccount::where("code", $code)->first();
        
        $save_arr = array_merge($default_save_arr, [
            "code" => $code,
            "name" => "Cash",
            "ledger_category_id" => $ledger_category_code_id_list[LedgerCategory::CODE_cash],
        ]);

        if ($ledger_account)
        {
            $ledger_account->fill($save_arr);
            $ledger_account->update();
        }
        else
        {
            LedgerAccount::create($save_arr);
        }

        $parties = Party::get();

        foreach($parties as $party)
        {
            $party->is_active = 0;
            $party->save();

            $party->is_active = 1;
            $party->save();
        }
    }
}
