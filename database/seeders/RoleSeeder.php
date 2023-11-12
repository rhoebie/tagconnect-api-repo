<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'Admin',
            'Moderator',
            'User',
        ];

        foreach ($roles as $name => $data) {
            DB::table('roles')->insert([
                'name' => $data,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}