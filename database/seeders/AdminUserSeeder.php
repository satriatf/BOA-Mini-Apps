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
            'name' => 'Satria',
            'email' => 'v.satria.ferdiansyah@adira.co.id',
            'nik' => '51223639',
            'is_active' => 'Active',
            'join_date' => now(),
            'end_date' => null,
            'password' => Hash::make('51223639'), 
        ]);
    }
}
