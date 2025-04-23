<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("TRUNCATE table `settings`;");

        $q = <<<EOT
            INSERT INTO `settings` (`id`, `name`, `value`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES            
            (1, 'export_csv_max_record_count', '100000', '2025-01-13 23:31:40', '2025-02-05 01:08:03', 1, 23),
            (2, 'item_sku_pattern', '{category}-{group}-{brand}-{item_name}-{specification}', '2025-02-05 01:08:03', '2025-02-05 01:08:03', 23, NULL);
        EOT;

        DB::insert($q);
    }
}
