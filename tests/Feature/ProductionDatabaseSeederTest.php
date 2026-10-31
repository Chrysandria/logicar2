<?php

namespace Tests\Feature;

use App\Models\Employe;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class ProductionDatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_seeding_creates_only_the_configured_initial_admin(): void
    {
        $this->app['env'] = 'production';
        config([
            'seeding.initial_admin.name' => 'production-admin',
            'seeding.initial_admin.password' => 'A-secure-initial-password-123',
        ]);

        app(DatabaseSeeder::class)->run();

        $admin = User::where('name', 'production-admin')->firstOrFail();

        $this->assertSame('SNR', $admin->service);
        $this->assertTrue($admin->must_change_password);
        $this->assertSame(0, Employe::query()->count());
    }

    public function test_non_local_seeding_fails_without_a_configured_initial_admin_password(): void
    {
        $this->app['env'] = 'staging';
        config([
            'seeding.initial_admin.name' => 'production-admin',
            'seeding.initial_admin.password' => null,
        ]);
        $this->expectException(LogicException::class);

        app(UserSeeder::class)->run();
    }
}
