<?php

namespace Tests\Feature\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserSoftDeletesMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_soft_delete_migration_removes_deleted_at_when_rolled_back(): void
    {
        $migration = require database_path(
            'migrations/2025_10_30_063725_add_deleted_at_to_users_table.php'
        );

        $migration->down();

        $this->assertFalse(Schema::hasColumn('users', 'deleted_at'));
    }
}
