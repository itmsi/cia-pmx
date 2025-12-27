<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSprintIdToIssues extends Migration
{
    public function up()
    {
        $fields = [
            'sprint_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'after' => 'project_id',
            ],
        ];

        $this->forge->addColumn('issues', $fields);
        $this->forge->addForeignKey('sprint_id', 'sprints', 'id', 'SET NULL', 'CASCADE', 'issues_sprint_id_foreign');
    }

    public function down()
    {
        $this->forge->dropForeignKey('issues', 'issues_sprint_id_foreign');
        $this->forge->dropColumn('issues', ['sprint_id']);
    }
}
