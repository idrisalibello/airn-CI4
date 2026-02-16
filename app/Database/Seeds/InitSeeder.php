<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        $roles = [
            ['key' => 'admin', 'label' => 'Administrator'],
            ['key' => 'editor', 'label' => 'Editor'],
            ['key' => 'reviewer', 'label' => 'Reviewer'],
            ['key' => 'author', 'label' => 'Author'],
        ];

        foreach ($roles as $r) {
            $exists = $db->table('roles')->where('key', $r['key'])->get()->getRowArray();
            if (!$exists) {
                $db->table('roles')->insert([
                    'key' => $r['key'],
                    'label' => $r['label'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        $email = 'admin@airn.test';
        $user = $db->table('users')->where('email', $email)->get()->getRowArray();

        if (!$user) {
            $db->table('users')->insert([
                'name' => 'System Admin',
                'email' => $email,
                'password_hash' => password_hash('Admin@12345', PASSWORD_BCRYPT),
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $userId = (int) $db->insertID();
        } else {
            $userId = (int) $user['id'];
        }

        $adminRole = $db->table('roles')->where('key', 'admin')->get()->getRowArray();
        if ($adminRole) {
            $exists = $db->table('user_roles')
                ->where('user_id', $userId)
                ->where('role_id', (int) $adminRole['id'])
                ->get()->getRowArray();

            if (!$exists) {
                $db->table('user_roles')->insert([
                    'user_id' => $userId,
                    'role_id' => (int)$adminRole['id'],
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}
