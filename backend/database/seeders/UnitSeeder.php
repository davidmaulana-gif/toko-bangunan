<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
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
                'uuid'=>Str::uuid(),
                'unit' => 'g',
                'updated_at' => now(),
                'created_at' => now()

            ],
            [
                'id' => 2,
                'uuid'=>Str::uuid(),
                'unit' => 'kg',
                'updated_at' => now(),
                'created_at' => now()

            ],
            [
                'id' => 3,
                'uuid'=>Str::uuid(),
                'unit' => 'm2',
                'updated_at' => now(),
                'created_at' => now()
            ]
        ]);
    }
}
