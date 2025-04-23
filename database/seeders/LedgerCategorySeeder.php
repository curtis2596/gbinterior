<?php

namespace Database\Seeders;

use App\Models\LedgerCategory;
use Illuminate\Database\Seeder;

class LedgerCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $default_save_arr = [
            "is_pre_defined" => 1,
        ];  

        $multi_save_arr = [
            [
                "type" => "liability",
                "name" => "Payable",
                "code" => LedgerCategory::CODE_payable,
            ],
            [
                "type" => "liability",
                "name" => "Tax",
                "code" => LedgerCategory::CODE_tax,
            ],
            [
                "type" => "liability",
                "name" => 'Creditor',
                "code" => LedgerCategory::CODE_creditor,
            ],
            [
                "type" => 'asset',
                "name" => 'Receivable',
                "code" => LedgerCategory::CODE_receivable,
            ],
            [
                "type" => 'asset',
                "name" => 'Stock',
                "code" => LedgerCategory::CODE_stock,
            ],
            [
                "type" => 'asset',
                "name" => 'Cash',
                "code" => LedgerCategory::CODE_cash,
            ],
            [
                "type" => 'asset',
                "name" => 'Bank',
                "code" => LedgerCategory::CODE_bank,
            ],
            [
                "type" => 'asset',
                "name" => 'Debtor',
                "code" => LedgerCategory::CODE_debitor,
            ],
            [
                "type" => 'contra',
                "name" => 'Creditor Or Debitor',
                "code" => LedgerCategory::CODE_creditor_debitor,
            ],
        ];

        foreach($multi_save_arr as $save_arr)
        {
            $ledger_category = LedgerCategory::where("code", $save_arr['code'])->first();
        
            $save_arr = array_merge($default_save_arr, $save_arr);
    
            if ($ledger_category)
            {
                $ledger_category->fill($save_arr);
                $ledger_category->update();
            }
            else
            {
                LedgerCategory::create($save_arr);
            }
        }
    }
}
