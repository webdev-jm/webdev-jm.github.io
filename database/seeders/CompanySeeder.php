<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies_arr = [
            'BEVI',
            'BEVA',
            'PBB',
            'BEVM',
            'OSP',
        ];

        foreach($companies_arr as $name) {
            $company = new Company([
                'name' => $name
            ]);
            $company->save();
        }
    }
}
