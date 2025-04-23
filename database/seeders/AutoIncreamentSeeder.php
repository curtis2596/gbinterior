<?php

namespace Database\Seeders;

use App\Models\AutoIncreament;
use Illuminate\Database\Seeder;

class AutoIncreamentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $type = AutoIncreament::TYPE_PAYMENT;
        $model = AutoIncreament::where("type", $type)->first();

        if ($model)
        {
            $model->counter = 0;
            $model->save();
        }
        else
        {
            AutoIncreament::create([
                "type" => $type,
                "pattern" => "Pay-YY-counter",
                "counter" => 0
            ]);
        }

        $type = AutoIncreament::TYPE_PURCHASE_ORDER;
        $model = AutoIncreament::where("type", $type)->first();

        if ($model)
        {
            $model->counter = 0;
            $model->save();
        }
        else
        {
            AutoIncreament::create([
                "type" => $type,
                "pattern" => "PO-YY-counter",
                "counter" => 0
            ]);
        }

        $type = AutoIncreament::TYPE_PURCHASE_BILL;
        $model = AutoIncreament::where("type", $type)->first();

        if ($model)
        {
            $model->counter = 0;
            $model->save();
        }
        else
        {
            AutoIncreament::create([
                "type" => $type,
                "pattern" => "Pur-YY-counter",
                "counter" => 0
            ]);
        }

        $type = AutoIncreament::TYPE_PURCHASE_RETURN;
        $model = AutoIncreament::where("type", $type)->first();

        if ($model)
        {
            $model->counter = 0;
            $model->save();
        }
        else
        {
            AutoIncreament::create([
                "type" => $type,
                "pattern" => "PurRet-YY-counter",
                "counter" => 0
            ]);
        }

        $type = AutoIncreament::TYPE_SALE_ORDER;
        $model = AutoIncreament::where("type", $type)->first();

        if ($model)
        {
            $model->counter = 0;
            $model->save();
        }
        else
        {
            AutoIncreament::create([
                "type" => $type,
                "pattern" => "SO-YY-counter",
                "counter" => 0
            ]);
        }

        $type = AutoIncreament::TYPE_SALE_BILL;
        $model = AutoIncreament::where("type", $type)->first();

        if ($model)
        {
            $model->counter = 0;
            $model->save();
        }
        else
        {
            AutoIncreament::create([
                "type" => $type,
                "pattern" => "Sale-YY-counter",
                "counter" => 0
            ]);
        }

        $type = AutoIncreament::TYPE_SALE_RETURN;
        $model = AutoIncreament::where("type", $type)->first();

        if ($model)
        {
            $model->counter = 0;
            $model->save();
        }
        else
        {
            AutoIncreament::create([
                "type" => $type,
                "pattern" => "SaleRet-YY-counter",
                "counter" => 0
            ]);
        }

        $type = AutoIncreament::TYPE_INVENTORY_MOVEMENT;
        $model = AutoIncreament::where("type", $type)->first();

        if ($model)
        {
            $model->counter = 0;
            $model->save();
        }
        else
        {
            AutoIncreament::create([
                "type" => $type,
                "pattern" => "YY-counter",
                "counter" => 0
            ]);
        }

        $type = AutoIncreament::TYPE_JOB_ORDER;
        $model = AutoIncreament::where("type", $type)->first();

        if ($model)
        {
            $model->counter = 0;
            $model->save();
        }
        else
        {
            AutoIncreament::create([
                "type" => $type,
                "pattern" => "Job-YY-counter",
                "counter" => 0
            ]);
        }
    }
}
