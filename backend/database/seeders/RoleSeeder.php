<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
// use PhpParser\Node\Expr\BinaryOp\NotEqual;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::table('roles')->insert([
            [
                'id' => 1,
                'uuid' => Str::uuid(),
                'role' => 'superadmin',
                'updated_at' => now(),
                'created_at' => now()

            ],
            [
                'id' => 2,
                'uuid' => Str::uuid(),
                'role' => 'gudang',
                'updated_at' => now(),
                'created_at' => now()

            ],
            [
                'id' => 3,
                'uuid' => Str::uuid(),
                'role' => 'kasir',
                'updated_at' => now(),
                'created_at' => now()
            ]
        ]);
    }
}
