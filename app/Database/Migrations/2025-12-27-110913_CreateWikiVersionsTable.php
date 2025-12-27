<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWikiVersionsTable extends Migration
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
            ],
            'version_number' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'content' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'change_summary' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('wiki_page_id');
        $this->forge->addKey(['wiki_page_id', 'version_number'], false, true); // Unique constraint
        $this->forge->addForeignKey('wiki_page_id', 'wiki_pages', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('wiki_versions');
    }

    public function down()
    {
        $this->forge->dropTable('wiki_versions');
    }
}