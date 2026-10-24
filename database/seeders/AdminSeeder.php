<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AdminSeeder extends Seeder
{
    /**
     * Seed the one and only administrator account.
     */
    public function run(): void
    {
        $admin = User::query()->firstOrNew(['email' => User::ADMIN_EMAIL]);

        // Only set the default password on first creation so a changed password survives re-seeding.
        if (! $admin->exists) {
            $admin->password = 'admin123';
        }

        $admin->name = 'Admin';
        $admin->email_verified_at ??= Carbon::now();
        $admin->is_admin = true;
        $admin->save();

        // Guarantee there is exactly one admin.
        User::query()->where('is_admin', true)->whereKeyNot($admin->getKey())->update(['is_admin' => false]);
    }
}
