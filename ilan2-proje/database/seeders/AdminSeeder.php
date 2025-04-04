<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Admin kullanıcısı oluştur
        User::create([
            'name' => 'Admin',
            'email' => 'alp@alp.com',
            'password' => Hash::make('NSprinta'),
            'is_admin' => true,
        ]);

        $this->command->info('Admin kullanıcısı oluşturuldu!');
    }
}
