<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserIdToBoards extends Migration
{
    public function up()
    {
        $this->forge->addColumn('boards', [
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'after' => 'id'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('boards', 'user_id');
    }

}
