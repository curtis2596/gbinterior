<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InsertStates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $q = <<<'Q'
            INSERT INTO `states` 
                (`id`, `name`, `code`, `country_id`) 
            VALUES
                (1, 'Andaman and Nicobar Islands', 'AN', 101),
                (2, 'Andhra Pradesh', 'AP', 101),
                (3, 'Arunachal Pradesh', 'AD', 101),
                (4, 'Assam', 'AS', 101),
                (5, 'Bihar', 'BH', 101),
                (6, 'Chandigarh', 'CH', 101),
                (7, 'Chhattisgarh', 'CT', 101),
                (8, 'Dadra and Nagar Haveli', 'DN', 101),
                (9, 'Daman and Diu', 'DD', 101),
                (10, 'Delhi', 'DL', 101),
                (11, 'Goa', 'GA', 101),
                (12, 'Gujarat', 'GJ', 101),
                (13, 'Haryana', 'HR', 101),
                (14, 'Himachal Pradesh', 'HP', 101),
                (15, 'Jammu and Kashmir', 'JK', 101),
                (16, 'Jharkhand', 'JH', 101),
                (17, 'Karnataka', 'KA', 101),
                (18, 'Kenmore', 'Kenmore', 101),
                (19, 'Kerala', 'KL', 101),
                (20, 'Lakshadweep', 'LD', 101),
                (21, 'Madhya Pradesh', 'MP', 101),
                (22, 'Maharashtra', 'MH', 101),
                (23, 'Manipur', 'MN', 101),
                (24, 'Meghalaya', 'ME', 101),
                (25, 'Mizoram', 'MI', 101),
                (26, 'Nagaland', 'NL', 101),
                (27, 'Narora', 'Narora', 101),
                (28, 'Natwar', 'Natwar', 101),
                (29, 'Odisha', 'OR', 101),
                (30, 'Paschim Medinipur', '', 101),
                (31, 'Pondicherry', 'PY', 101),
                (32, 'Punjab', 'PB', 101),
                (33, 'Rajasthan', 'RJ', 101),
                (34, 'Sikkim', 'SK', 101),
                (35, 'Tamil Nadu', 'TN', 101),
                (36, 'Telangana', 'TS', 101),
                (37, 'Tripura', 'TR', 101),
                (38, 'Uttar Pradesh', 'UP', 101),
                (39, 'Uttarakhand', 'UT', 101),
                (40, 'Vaishali', 'Vaishali', 101),
                (41, 'West Bengal', 'WB', 101);
            Q;

        DB::statement($q);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("SET FOREIGN_KEY_CHECKS = 0;");
        DB::statement("TRUNCATE states");
        DB::statement("SET FOREIGN_KEY_CHECKS = 1;");
    }
}
