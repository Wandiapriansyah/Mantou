<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin Mantou Ganteng',
            'username' => 'mantou admin',
            'email' => 'admin@mantou.com',
            'phone' => '085612486579',
            'password' => Hash::make('admin123'),
            'roles' => 'admin',
        ]);

        User::create([
            'name' => 'Udy Daya',
            'username' => 'Tukang Jaga-L',
            'email' => 'Udydaya@mantou.com',
            'phone' => '083165249467',
            'password' => Hash::make('jaga123'),
            'roles' => 'cashier',
        ]);
    }
}
