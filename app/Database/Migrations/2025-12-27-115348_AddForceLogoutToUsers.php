<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddForceLogoutToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'force_logout_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Timestamp when user was force logged out. If this is newer than last_login_at, user session will be invalidated.',
                'after' => 'updated_at'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'force_logout_at');
    }
}
