<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PrefectOfDiscipline;

class PrefectOfDisciplineSeeder extends Seeder
{
    public function run(): void
    {
        PrefectOfDiscipline::create([
            'prefect_fname'       => 'Shawn',
            'prefect_lname'       => 'Abaco',
            'prefect_sex'         => 'Male',
            'prefect_email'       => 'tshssvts@gmail.com',
            'prefect_password'    => bcrypt('password123'),
            'prefect_contactinfo' => '09171234567',
        ]);

        PrefectOfDiscipline::create([
            'prefect_fname'       => 'Kent Zyrone',
            'prefect_lname'       => 'Flores',
            'prefect_sex'         => 'Male',
            'prefect_email'       => 'k.zyroneflores@gmail.com',
            'prefect_password'    => bcrypt('prefect'),
            'prefect_contactinfo' => '09093246917',
        ]);
    }
}
