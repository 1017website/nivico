<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DeveloperAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $developer;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasColumn('users', 'is_developer')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_developer')->default(false);
            });
        }

        $this->developer = User::create([
            'first_name' => 'Test',
            'last_name' => 'Developer',
            'email' => 'developer-test@example.com',
            'password' => 'test-password',
            'role' => 'admin',
            'role_id' => null,
            'is_active' => true,
        ]);

        DB::table('users')
            ->where('id', $this->developer->id)
            ->update(['is_developer' => true]);

        $this->developer->refresh();
    }

    public function test_developer_flag_identifies_the_special_account(): void
    {
        $developer = $this->developer;

        $this->assertTrue($developer->isDeveloper());
        $this->assertTrue($developer->isAdmin());
        $this->assertTrue($developer->is_active);
    }

    public function test_developer_is_hidden_from_the_user_page(): void
    {
        $developer = $this->developer;

        $this->actingAs($developer)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertDontSee($developer->email);
    }

    public function test_only_developer_can_access_the_system_page(): void
    {
        $developer = $this->developer;
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
