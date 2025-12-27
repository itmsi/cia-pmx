<?php

namespace Tests\Unit\Models;

use Tests\Support\TestCase;
use App\Models\LabelModel;

/**
 * @internal
 */
final class LabelModelTest extends TestCase
{
    protected LabelModel $labelModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->labelModel = new LabelModel();
    }

    public function testCanCreateLabel(): void
    {
        $user = $this->createTestUser();
        $workspace = $this->createTestWorkspace($user['id']);
        $project = $this->createTestProject($workspace['id'], $user['id']);
        
        $data = [
            'workspace_id' => $workspace['id'],
            'project_id' => $project['id'],
            'name' => 'Bug',
            'color' => '#FF0000',
            'description' => 'Bug label'
        ];

        $id = $this->labelModel->insert($data);
        $this->assertIsInt($id);

        $label = $this->labelModel->find($id);
        $this->assertNotNull($label);
        $this->assertEquals('Bug', $label['name']);
        $this->assertEquals('#FF0000', $label['color']);
    }

    public function testCanUpdateLabel(): void
    {
        $user = $this->createTestUser();
        $workspace = $this->createTestWorkspace($user['id']);
        $project = $this->createTestProject($workspace['id'], $user['id']);
        
        $id = $this->labelModel->insert([
            'workspace_id' => $workspace['id'],
            'project_id' => $project['id'],
            'name' => 'Bug',
            'color' => '#FF0000'
        ]);

        $this->labelModel->update($id, [
            'color' => '#00FF00'
        ]);

        $label = $this->labelModel->find($id);
        $this->assertEquals('#00FF00', $label['color']);
    }
}

