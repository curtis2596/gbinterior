<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InsertAutoIncreaments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("SET FOREIGN_KEY_CHECKS = 0;");
        DB::statement("TRUNCATE auto_increaments");
        DB::statement("SET FOREIGN_KEY_CHECKS = 1;");
        
        $q = <<<'Q'
            INSERT INTO `auto_increaments` (`id`, `type`, `pattern`, `counter`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
            (1, 'purchase', 'Pur-YY-counter', 0, '2025-01-16 02:02:27', '2025-01-16 02:47:30', 23, 23),
            (2, 'purchase_return', 'Pur-Ret-YY-counter', 0, '2025-01-16 02:44:40', '2025-01-16 02:47:43', 23, 23),
            (3, 'expense', 'E-YY-counter', 0, '2025-01-16 02:45:22', '2025-01-16 02:45:22', 23, NULL),
            (4, 'sale', 'Sale-YY-counter', 0, '2025-01-16 02:45:35', '2025-01-16 02:47:56', 23, 23),
            (5, 'sale_return', 'Sale-Ret-YY-counter', 0, '2025-01-16 02:46:53', '2025-01-16 02:48:09', 23, 23),
            (6, 'payment', 'Pay-YY-counter', 0, '2025-01-16 02:48:33', '2025-01-16 02:48:33', 23, NULL);
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
        DB::statement("TRUNCATE auto_increaments");
        DB::statement("SET FOREIGN_KEY_CHECKS = 1;");
    }
}
