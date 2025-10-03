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
            'prefect_email'       => 'shawnprefect@gmail.com',
            'prefect_password'    => bcrypt('password123'),
            'prefect_contactinfo' => '09171234567',
        ]);
    }
}
