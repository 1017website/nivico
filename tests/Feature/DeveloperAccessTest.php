<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DeveloperAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_creates_the_developer_account(): void
    {
        $developer = User::where('email', '1017website@gmail.com')->firstOrFail();

        $this->assertTrue($developer->isDeveloper());
        $this->assertTrue($developer->isAdmin());
        $this->assertTrue($developer->is_active);
        $this->assertTrue(Hash::check('1017Website2020.', $developer->password));
    }

    public function test_developer_is_hidden_from_the_user_page(): void
    {
        $developer = User::where('email', '1017website@gmail.com')->firstOrFail();

        $this->actingAs($developer)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertDontSee('1017website@gmail.com');
    }

    public function test_only_developer_can_access_the_system_page(): void
    {
        $developer = User::where('email', '1017website@gmail.com')->firstOrFail();
        $superAdmin = User::create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'superadmin@example.com',
            'password' => 'password',
            'role' => 'admin',
            'role_id' => null,
            'is_active' => true,
        ]);

        $this->actingAs($developer)
            ->get(route('admin.system.index'))
            ->assertOk()
            ->assertSee('Khusus Developer');

        $this->actingAs($superAdmin)
            ->get(route('admin.system.index'))
            ->assertForbidden();
    }

    public function test_system_menu_is_hidden_from_super_admin(): void
    {
        $superAdmin = User::create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'superadmin@example.com',
            'password' => 'password',
            'role' => 'admin',
            'role_id' => null,
            'is_active' => true,
        ]);

        $this->actingAs($superAdmin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertDontSee(route('admin.system.index'), false);
    }

    public function test_super_admin_can_run_the_developer_migration_when_it_is_still_pending(): void
    {
        Schema::table('users', function ($table) {
            $table->dropColumn('is_developer');
        });

        $superAdmin = User::create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'bootstrap-admin@example.com',
            'password' => 'password',
            'role' => 'admin',
            'role_id' => null,
            'is_active' => true,
        ]);

        $this->actingAs($superAdmin)
            ->get(route('admin.system.index'))
            ->assertOk()
            ->assertSee('Aktivasi Developer')
            ->assertSee('php artisan migrate')
            ->assertDontSee('php artisan optimize:clear');
    }
}
