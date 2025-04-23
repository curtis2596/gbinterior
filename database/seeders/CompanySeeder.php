<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Company::create([
            'name' => 'GB Interior Solution',
            'logo' => '',
            'address' => 'Shop No. 12, UG Floor, Surya Shopping Arcade, National Road, Ghumar Mandi, Ludhiana',
            'gst_number' => '03AQXPS9329P1ZM',
            'phone_number' => '93167-66667',
            'email' => 'gbinteriorsolution2@gmail.com',
            'website' => 'www.gbinterior.co.in',
            // 'state_gst' => '9',
            // 'central_gst' => '9',
            // 'integrated_gst' => '0',
            'bank_name' => 'ICICI Bank',
            'account_name' => 'G B Interior Solution',
            'ifsc_code' => 'ICIC0000017',
            'account_number' => '001705013407',
            'terms' => '1.) Goods Once Sold Shall not be returned.
                        2.) Interest @ 24% Shall be Charged if not paid within 30 days
                        3.) All dispute subject to Ludhiana Jurisdiction only.
                        4.) Goods sold vide this invoice are delivered in good
                        condition if damage must be returned at the time of
                        delivery.'
        ]);
    }
}
