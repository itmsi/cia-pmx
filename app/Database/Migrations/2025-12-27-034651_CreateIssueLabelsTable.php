<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateIssueLabelsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
            ],
            'issue_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'label_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['issue_id', 'label_id'], false, true);
        $this->forge->addForeignKey('issue_id', 'issues', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('label_id', 'labels', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('issue_labels');
    }

    public function down()
    {
        $this->forge->dropTable('issue_labels');
    }
}
