<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class LinkBoardsToProjects extends Migration
{
    public function up()
    {
        $fields = [
            'project_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'after' => 'user_id',
            ],
            'board_type' => [
                'type' => 'ENUM',
                'constraint' => ['kanban', 'scrum'],
                'default' => 'kanban',
                'after' => 'project_id',
            ],
        ];

        $this->forge->addColumn('boards', $fields);
        $this->forge->addForeignKey('project_id', 'projects', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropForeignKey('boards', 'boards_project_id_foreign');
        $this->forge->dropColumn('boards', ['project_id', 'board_type']);
    }
}
