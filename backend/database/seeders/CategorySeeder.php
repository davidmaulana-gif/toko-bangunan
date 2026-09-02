<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
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

        $uuid = Str::uuid();
        DB::table('categories')->insert([
            [
                'id' => 1,
                'uuid' => Str::uuid(),
                'category' => 'cat',
                'updated_at' => now(),
                'created_at' => now()

            ],
            [
                'id' => 2,
                'uuid' => Str::uuid(),
                'category' => 'kayu',
                'updated_at' => now(),
                'created_at' => now()

            ],
            [
                'id' => 3,
                'uuid' => Str::uuid(),
                'category' => 'semen',
                'updated_at' => now(),
                'created_at' => now()
            ]
        ]);
    }
}
