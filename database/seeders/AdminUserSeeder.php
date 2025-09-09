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
            'name' => 'Zestado Mahesa Yudha',
            'email' => 'zestado.yudha@adira.co.id',
            'nik' => '12233344',
            'is_active' => 'Active',
            'join_date' => now(),
            'end_date' => null,
            'password' => Hash::make('12345'), 
        ]);
    }
}
