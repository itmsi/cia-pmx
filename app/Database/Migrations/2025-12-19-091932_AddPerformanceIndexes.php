<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPerformanceIndexes extends Migration
{
    public function up()
    {
        // Boards
        $this->forge->addKey('user_id', false, false, 'boards');

        // Columns
        $this->forge->addKey('board_id', false, false, 'columns');
        $this->forge->addKey(['board_id', 'position'], false, false, 'columns');

        // Cards
        $this->forge->addKey('column_id', false, false, 'cards');
        $this->forge->addKey(['column_id', 'position'], false, false, 'cards');

        // Activity Logs
        $this->forge->addKey('user_id', false, false, 'activity_logs');
        $this->forge->addKey('created_at', false, false, 'activity_logs');
    }

    public function down()
    {
        //
    }
}
