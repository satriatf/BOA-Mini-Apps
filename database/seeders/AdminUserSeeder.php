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
        $adminNik = (int) env('ADMIN_NIK', 0);
        $adminEmail = (string) env('ADMIN_EMAIL', 'adminBOA@adira.co.id');
        $adminName = (string) env('ADMIN_NAME', 'Admin BOA');
        $adminPassword = (string) env('ADMIN_PASSWORD', 'admin123');

        $oldScriptAdmins = User::where('employee_nik', $adminNik)
            ->where('employee_email', '!=', $adminEmail)
            ->get();

        foreach ($oldScriptAdmins as $old) {
            try {
                $old->delete();
            } catch (\Throwable $e) {
                $old->is_admin = false;
                $old->save();
            }
        }

        $admin = User::where('employee_nik', $adminNik)
            ->where('employee_email', $adminEmail)
            ->first();

        if (! $admin) {
            User::create([
                'employee_name' => $adminName,
                'employee_email' => $adminEmail,
                'employee_nik' => $adminNik,
                'is_admin' => true,
                'level' => null,
                'password' => Hash::make($adminPassword),
            ]);
            echo "Created script admin: {$adminEmail}\n";
        } else {
            if (! (bool) $admin->is_admin) {
                $admin->is_admin = true;
                $admin->save();
            }
            // Ensure admin doesn't inherit a default "level" value
            if ($admin->level !== null) {
                $admin->level = null;
                $admin->save();
            }
            echo "Script admin exists: {$adminEmail}\n";
        }
    }
}
