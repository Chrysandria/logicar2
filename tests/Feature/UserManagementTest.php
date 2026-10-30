<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_snr_can_create_a_user(): void
    {
        $this->actingAs(User::create([
            'name' => 'snr-admin',
            'service' => 'SNR',
            'password' => 'secret123',
        ]));

        $response = $this->postJson('/users', [
            'name' => 'chef-cuisine',
            'service' => 'CUISINE',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.name', 'chef-cuisine')
            ->assertJsonPath('user.service', 'CUISINE')
            ->assertJsonPath('user.must_change_password', true);

        $this->assertDatabaseHas('users', ['name' => 'chef-cuisine', 'service' => 'CUISINE']);
        $createdUser = User::where('name', 'chef-cuisine')->firstOrFail();
        $this->assertTrue(Hash::check('0000', $createdUser->password));
    }

    public function test_new_user_cannot_use_protected_endpoints_before_changing_password(): void
    {
        $this->actingAs(User::create([
            'name' => 'new-user',
            'service' => 'CUISINE',
            'password' => '0000',
            'must_change_password' => true,
        ]));

        $this->getJson('/users')
            ->assertStatus(423)
            ->assertJsonPath('message', 'Vous devez modifier votre mot de passe avant de continuer.');
    }

    public function test_non_snr_user_cannot_manage_user_accounts(): void
    {
        $this->actingAs(User::create([
            'name' => 'service-user',
            'service' => 'CUISINE',
            'password' => 'secret123',
        ]));

        $this->postJson('/users', [
            'name' => 'another-user',
            'service' => 'CUISINE',
        ])->assertForbidden()
            ->assertJsonPath('message', 'Accès réservé au service SNR.');
    }

    public function test_snr_can_delete_another_user_but_not_itself(): void
    {
        $admin = User::create([
            'name' => 'snr-admin',
            'service' => 'SNR',
            'password' => 'secret123',
        ]);
        $this->actingAs($admin);

        $otherUser = User::create([
            'name' => 'chef-cuisine',
            'service' => 'CUISINE',
            'password' => 'secret456',
        ]);

        $this->deleteJson("/users/{$otherUser->id}")
            ->assertOk();

        $this->assertDatabaseMissing('users', ['id' => $otherUser->id]);

        $this->deleteJson("/users/{$admin->id}")
            ->assertStatus(422)
            ->assertJsonPath('message', 'Vous ne pouvez pas supprimer votre propre compte.');
    }

    public function test_user_can_change_its_password(): void
    {
        $user = User::create([
            'name' => 'chef-cuisine',
            'service' => 'CUISINE',
            'password' => '0000',
            'must_change_password' => true,
        ]);

        $this->actingAs($user);

        $response = $this->putJson('/auth/password', [
            'current_password' => '0000',
            'password' => 'new-secret-123',
            'password_confirmation' => 'new-secret-123',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Mot de passe mis à jour avec succès.');

        $this->assertTrue(Hash::check('new-secret-123', $user->fresh()->password));
        $this->assertFalse($user->fresh()->must_change_password);
        $this->getJson('/services')->assertOk();
    }
}
