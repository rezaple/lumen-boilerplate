<?php

use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call('UsersTableSeeder');
        DB::table('users')->insert([
            'username' => "Admin",
            'email' => 'admin@mail.com',
            'password' => app('hash')->make('secret'),
            'uid'=>Uuid::uuid4()->toString(),
            'is_active'=>true,
            'role'=>'ADMIN'
        ]);

        DB::table('users')->insert([
            'username' => "Staff",
            'email' => 'staff@mail.com',
            'password' => app('hash')->make('secret'),
            'uid'=>Uuid::uuid4()->toString(),
            'is_active'=>true,
            'role'=>'STAFF'
        ]);

        DB::table('users')->insert([
            'username' => "Company",
            'email' => 'company@mail.com',
            'password' => app('hash')->make('secret'),
            'uid'=>Uuid::uuid4()->toString(),
            'is_active'=>true,
            'role'=>'COMPANY'
        ]);

        DB::table('users')->insert([
            'username' => "Staff",
            'email' => 'staff@mail.com',
            'password' => app('hash')->make('secret'),
            'uid'=>Uuid::uuid4()->toString(),
            'is_active'=>true,
            'role'=>'STAFF'
        ]);
    }
}
