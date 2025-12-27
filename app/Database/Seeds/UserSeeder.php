<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\UserModel;

class UserSeeder extends Seeder
{
    public function run()
    {
        $userModel = new UserModel();
        
        $data = [
            'email' => 'admin@example.com',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
        ];
        
        $userModel->save($data);
        
        echo "First user created:\n";
        echo "Email: admin@example.com\n";
        echo "Password: admin123\n";
    }
}