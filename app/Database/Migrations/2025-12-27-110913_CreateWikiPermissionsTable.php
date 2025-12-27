<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWikiPermissionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
            ],
            'wiki_page_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'NULL for project-level permissions',
            ],
            'project_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'NULL for page-level permissions',
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'can_view' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'can_edit' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'can_delete' => [
                'type' => 'BOOLEAN',
                'default' => false,
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
        $this->forge->addKey('wiki_page_id');
        $this->forge->addKey('project_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey(['wiki_page_id', 'user_id'], false, true);
        $this->forge->addKey(['project_id', 'user_id'], false, true);
        $this->forge->addForeignKey('wiki_page_id', 'wiki_pages', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('project_id', 'projects', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('wiki_permissions');
    }

    public function down()
    {
        $this->forge->dropTable('wiki_permissions');
    }
}