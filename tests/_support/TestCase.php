<?php

namespace Tests\Support;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Base Test Case Class
 * 
 * Extended by all test classes to provide common functionality
 */
class TestCase extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App';
    
    /**
     * Setup test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Load all migrations
        $this->migrate();
    }
    
    /**
     * Create a test user
     */
    protected function createTestUser(array $data = []): array
    {
        $defaultData = [
            'email' => 'test@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'status' => 'active',
            'full_name' => 'Test User'
        ];
        
        $userData = array_merge($defaultData, $data);
        $userModel = new \App\Models\UserModel();
        $userId = $userModel->insert($userData);
        
        return $userModel->find($userId);
    }
    
    /**
     * Create a test role
     */
    protected function createTestRole(array $data = []): array
    {
        $defaultData = [
            'name' => 'Test Role',
            'slug' => 'test-role',
            'description' => 'Test role description'
        ];
        
        $roleData = array_merge($defaultData, $data);
        $roleModel = new \App\Models\RoleModel();
        $roleId = $roleModel->insert($roleData);
        
        return $roleModel->find($roleId);
    }
    
    /**
     * Create a test workspace
     */
    protected function createTestWorkspace(int $ownerId, array $data = []): array
    {
        $defaultData = [
            'name' => 'Test Workspace',
            'slug' => 'test-workspace',
            'description' => 'Test workspace description',
            'owner_id' => $ownerId
        ];
        
        $workspaceData = array_merge($defaultData, $data);
        $workspaceModel = new \App\Models\WorkspaceModel();
        $workspaceId = $workspaceModel->insert($workspaceData);
        
        return $workspaceModel->find($workspaceId);
    }
    
    /**
     * Create a test project
     */
    protected function createTestProject(int $workspaceId, int $ownerId, array $data = []): array
    {
        $defaultData = [
            'workspace_id' => $workspaceId,
            'key' => 'TEST',
            'name' => 'Test Project',
            'description' => 'Test project description',
            'visibility' => 'private',
            'status' => 'active',
            'owner_id' => $ownerId
        ];
        
        $projectData = array_merge($defaultData, $data);
        $projectModel = new \App\Models\ProjectModel();
        $projectId = $projectModel->insert($projectData);
        
        return $projectModel->find($projectId);
    }
    
    /**
     * Simulate user login for feature tests
     */
    protected function loginUser(int $userId): void
    {
        $session = \Config\Services::session();
        $session->set('user_id', $userId);
        $session->set('user_email', 'test@example.com');
    }
}

