<?php

namespace Tests\Feature\Admin;

use App\Models\Menu;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_sidebar_only_renders_active_menus_the_user_can_access(): void
    {
        $allowedPermission = Permission::create([
            'name' => 'view allowed menu',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'view restricted menu',
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create();
        $user->givePermissionTo($allowedPermission);

        $allowedMenu = Menu::create([
            'name' => 'Allowed menu',
            'permission_name' => 'view allowed menu',
            'is_active' => true,
        ]);

        Menu::create([
            'name' => 'Allowed child',
            'parent_id' => $allowedMenu->id,
            'permission_name' => 'view allowed menu',
            'is_active' => true,
        ]);

        Menu::create([
            'name' => 'Restricted child',
            'parent_id' => $allowedMenu->id,
            'permission_name' => 'view restricted menu',
            'is_active' => true,
        ]);

        Menu::create([
            'name' => 'Inactive child',
            'parent_id' => $allowedMenu->id,
            'permission_name' => 'view allowed menu',
            'is_active' => false,
        ]);

        Menu::create([
            'name' => 'Inactive menu',
            'permission_name' => 'view allowed menu',
            'is_active' => false,
        ]);

        Menu::create([
            'name' => 'Restricted menu',
            'permission_name' => 'view restricted menu',
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $sidebar = view('layouts.admin.sidebar')->render();

        $this->assertStringContainsString('Allowed menu', $sidebar);
        $this->assertStringContainsString('Allowed child', $sidebar);
        $this->assertStringNotContainsString('Inactive menu', $sidebar);
        $this->assertStringNotContainsString('Restricted menu', $sidebar);
        $this->assertStringNotContainsString('Inactive child', $sidebar);
        $this->assertStringNotContainsString('Restricted child', $sidebar);
    }
}
