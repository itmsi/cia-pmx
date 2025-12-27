<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWorkflowRulesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
            ],
            'board_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'NULL = global rule, otherwise board-specific',
            ],
            'from_column_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'to_column_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'rule_type' => [
                'type' => 'ENUM',
                'constraint' => ['blocked', 'allowed', 'conditional'],
                'default' => 'blocked',
                'comment' => 'blocked = tidak boleh, allowed = boleh, conditional = dengan kondisi',
            ],
            'condition' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON condition untuk conditional rules',
            ],
            'message' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Custom error message',
            ],
            'is_active' => [
                'type' => 'BOOLEAN',
                'default' => true,
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
        $this->forge->addKey(['board_id', 'from_column_id', 'to_column_id']);
        $this->forge->addForeignKey('board_id', 'boards', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('from_column_id', 'columns', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('to_column_id', 'columns', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('workflow_rules');
    }

    public function down()
    {
        $this->forge->dropTable('workflow_rules');
    }
}
