<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            [
                'id' => 1,
                'category' => 'cat',
                'updated_at' => now(),
                'created_at' => now()

            ],
            [
                'id' => 2,
                'category' => 'kayu',
                'updated_at' => now(),
                'created_at' => now()

            ],
            [
                'id' => 3,
                'category' => 'semen',
                'updated_at' => now(),
                'created_at' => now()
            ]
        ]);
    }
}
