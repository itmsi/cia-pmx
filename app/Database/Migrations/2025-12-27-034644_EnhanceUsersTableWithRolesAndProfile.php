<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnhanceUsersTableWithRolesAndProfile extends Migration
{
    public function up()
    {
        $fields = [
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default' => 'active',
                'after' => 'password',
            ],
            'role_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'after' => 'status',
            ],
            'full_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'role_id',
            ],
            'photo' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'full_name',
            ],
            'phone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'photo',
            ],
        ];

        $this->forge->addColumn('users', $fields);
        
        // Add foreign key for role_id
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'SET NULL', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropForeignKey('users', 'users_role_id_foreign');
        $this->forge->dropColumn('users', ['status', 'role_id', 'full_name', 'photo', 'phone']);
    }
}
