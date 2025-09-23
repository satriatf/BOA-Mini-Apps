<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'employee_name' => 'Zestado Mahesa Yudha',
            'employee_email' => 'zestado.yudha@adira.co.id',
            'employee_nik' => '12233344',
            'level' => 'Manager',
            'is_active' => 'Active',
            'join_date' => now(),
            'end_date' => null,
            'password' => Hash::make('12345'), 
        ]);
    }
}
