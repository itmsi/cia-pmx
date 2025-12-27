<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSprintsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
            ],
            'project_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'goal' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'start_date' => [
                'type' => 'DATE',
            ],
            'end_date' => [
                'type' => 'DATE',
            ],
            'duration_weeks' => [
                'type' => 'INT',
                'constraint' => 2,
                'default' => 2,
                'comment' => 'Duration in weeks (1-4)',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['planned', 'active', 'completed'],
                'default' => 'planned',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('project_id');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('project_id', 'projects', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('sprints');
    }

    public function down()
    {
        $this->forge->dropTable('sprints');
    }
}
