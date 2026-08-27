<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('units')->insert([
            [
                'id' => 1,
                'unit' => 'g',
                'updated_at' => now(),
                'created_at' => now()

            ],
            [
                'id' => 2,
                'unit' => 'kg',
                'updated_at' => now(),
                'created_at' => now()

            ],
            [
                'id' => 3,
                'unit' => 'm2',
                'updated_at' => now(),
                'created_at' => now()
            ]
        ]);
    }
}
