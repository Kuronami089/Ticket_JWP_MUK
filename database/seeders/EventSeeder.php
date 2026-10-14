<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('events')->insert([
            [
                'title' => 'The Weekend',
                'category' => 'Musik',
                'ticket_price' => 1000000,
                'description' => 'Konser The Weekend tidak datang 2 kali!',
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Comifuro',
                'category' => 'Festival',
                'ticket_price' => 85000,
                'description' => 'Festival bertema jepang',
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Court Road',
                'category' => 'Seminar',
                'ticket_price' => 100000,
                'description' => 'Seminar yang diadakan langsung oleh reza auditore',
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
