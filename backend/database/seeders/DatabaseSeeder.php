<?php

namespace Database\Seeders;

use App\Models\User;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{


    public function run(): void
    {

        $this->call(ProjectPhaseSeeder::class);


        User::firstOrCreate(['email' => 'admin@projectflow.com'], [
            'name' => 'Admin User',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);


        $mentor1 = User::firstOrCreate(['email' => 'ana.kovac@projectflow.com'], [
            'name' => 'Prof. Ana Kovač',
            'password' => bcrypt('password'),
            'role' => 'mentor',
        ]);

        User::firstOrCreate(['email' => 'ivan.maric@projectflow.com'], [
            'name' => 'Prof. Ivan Marić',
            'password' => bcrypt('password'),
            'role' => 'mentor',
        ]);


        User::firstOrCreate(['email' => 'marko@student.com'], [
            'name' => 'Marko Horvat',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        User::firstOrCreate(['email' => 'petra@student.com'], [
            'name' => 'Petra Novak',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        User::firstOrCreate(['email' => 'luka@student.com'], [
            'name' => 'Luka Babić',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);
    }
}
