<?php

namespace Tests\Feature\Controllers;

use Tests\Support\TestCase;

/**
 * @internal
 */
final class RoleControllerTest extends TestCase
{
    public function testCanAccessRolesIndex(): void
    {
        $user = $this->createTestUser();
        $this->loginUser($user['id']);

        $result = $this->get('/roles');
        $result->assertOK();
        $result->assertSee('Roles');
    }

    public function testCanAccessCreateRoleForm(): void
    {
        $user = $this->createTestUser();
        $this->loginUser($user['id']);

        $result = $this->get('/roles/create');
        $result->assertOK();
    }

    public function testCanCreateRole(): void
    {
        $user = $this->createTestUser();
        $this->loginUser($user['id']);

        $result = $this->post('/roles', [
            'name' => 'Test Role',
            'description' => 'Test description'
        ]);

        $result->assertRedirect();
        
        // Verify role was created
        $roleModel = new \App\Models\RoleModel();
        $role = $roleModel->where('slug', 'test-role')->first();
        $this->assertNotNull($role);
    }
}

