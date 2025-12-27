<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnhanceCardsToIssues extends Migration
{
    public function up()
    {
        // Rename cards table to issues
        $this->forge->renameTable('cards', 'issues');
        
        // Add new columns for issues
        $fields = [
            'project_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'after' => 'id',
            ],
            'issue_key' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'project_id',
            ],
            'issue_type' => [
                'type' => 'ENUM',
                'constraint' => ['task', 'bug', 'story', 'epic', 'sub_task'],
                'default' => 'task',
                'after' => 'issue_key',
            ],
            'priority' => [
                'type' => 'ENUM',
                'constraint' => ['lowest', 'low', 'medium', 'high', 'critical'],
                'default' => 'medium',
                'after' => 'issue_type',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'title',
            ],
            'assignee_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'after' => 'description',
            ],
            'reporter_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'after' => 'assignee_id',
            ],
            'due_date' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'reporter_id',
            ],
            'estimation' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
                'comment' => 'Story points or hours',
                'after' => 'due_date',
            ],
            'parent_issue_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'after' => 'estimation',
            ],
        ];

        $this->forge->addColumn('issues', $fields);
        
        // Add foreign keys
        $this->forge->addForeignKey('project_id', 'projects', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('assignee_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('reporter_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('parent_issue_id', 'issues', 'id', 'CASCADE', 'CASCADE');
        
        // Add indexes
        $this->forge->addKey('issue_key');
        $this->forge->addKey(['project_id', 'issue_key'], false, true);
    }

    public function down()
    {
        // Drop foreign keys
        $this->forge->dropForeignKey('issues', 'issues_project_id_foreign');
        $this->forge->dropForeignKey('issues', 'issues_assignee_id_foreign');
        $this->forge->dropForeignKey('issues', 'issues_reporter_id_foreign');
        $this->forge->dropForeignKey('issues', 'issues_parent_issue_id_foreign');
        
        // Drop columns
        $this->forge->dropColumn('issues', [
            'project_id', 'issue_key', 'issue_type', 'priority', 'description',
            'assignee_id', 'reporter_id', 'due_date', 'estimation', 'parent_issue_id'
        ]);
        
        // Rename back to cards
        $this->forge->renameTable('issues', 'cards');
    }
}
