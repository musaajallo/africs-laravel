<?php

namespace Tests\Feature\Console;

use App\Models\User;
use App\Models\VaultEntry;
use App\Models\VaultFolder;
use App\Support\Rbac;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class VaultTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function manager(): User
    {
        $user = User::factory()->create(['password' => 'secret-pw-123']);
        $user->assignRole(Rbac::ROLE_CONSOLE_MANAGER);

        return $user;
    }

    public function test_a_console_user_without_permission_cannot_open_the_vault(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Rbac::PERM_CONSOLE_ACCESS);

        $this->actingAs($user)->get('/console/vault')->assertForbidden();
    }

    public function test_the_password_is_stored_encrypted(): void
    {
        $this->actingAs($this->manager())
            ->post('/console/vault', ['title' => 'Acme cPanel', 'password' => 'hunter2', 'custom' => []])
            ->assertRedirect();

        $raw = DB::table('vault_entries')->value('password');

        $this->assertNotNull($raw);
        $this->assertStringNotContainsString('hunter2', $raw);
        $this->assertSame('hunter2', VaultEntry::first()->password);
    }

    public function test_reveal_requires_an_unlock_and_is_audited(): void
    {
        $entry = VaultEntry::factory()->create(['password' => 'topsecret']);
        $user = $this->manager();

        // Locked → 423
        $this->actingAs($user)
            ->getJson("/console/vault/{$entry->id}/reveal")
            ->assertStatus(423);

        // Wrong password → 422
        $this->actingAs($user)
            ->postJson('/console/vault/unlock', ['password' => 'nope'])
            ->assertStatus(422);

        // Right password unlocks
        $this->actingAs($user)
            ->postJson('/console/vault/unlock', ['password' => 'secret-pw-123'])
            ->assertOk();

        $this->actingAs($user)
            ->getJson("/console/vault/{$entry->id}/reveal")
            ->assertOk()
            ->assertJsonPath('password', 'topsecret');

        $this->assertTrue(
            Activity::where('subject_id', $entry->id)->where('event', 'revealed')->exists(),
        );
    }

    public function test_keepass_xml_export_needs_an_unlock_and_contains_entries(): void
    {
        VaultEntry::factory()->create(['title' => 'Router admin', 'password' => 'r0uter']);
        $user = $this->manager();

        $this->actingAs($user)->get('/console/vault/export/xml')->assertStatus(423);

        $this->actingAs($user)->postJson('/console/vault/unlock', ['password' => 'secret-pw-123']);

        $response = $this->actingAs($user)->get('/console/vault/export/xml');
        $response->assertOk();

        $xml = $response->streamedContent();
        $this->assertStringContainsString('<KeePassFile>', $xml);
        $this->assertStringContainsString('Router admin', $xml);
        $this->assertStringContainsString('r0uter', $xml);
    }

    public function test_kdbx_export_reports_when_it_is_not_configured(): void
    {
        config(['vault.kdbx_python' => null]);
        VaultEntry::factory()->create();
        $user = $this->manager();

        $this->actingAs($user)->postJson('/console/vault/unlock', ['password' => 'secret-pw-123']);

        $this->actingAs($user)
            ->post('/console/vault/export/kdbx', ['password' => 'a-long-password'])
            ->assertSessionHasErrors('password');
    }

    public function test_folders_can_be_managed_and_deleting_one_unfiles_its_entries(): void
    {
        $user = $this->manager();

        $this->actingAs($user)->post('/console/vault/folders', ['name' => 'Clients'])->assertRedirect();
        $folder = VaultFolder::first();

        $entry = VaultEntry::factory()->create(['folder_id' => $folder->id]);

        $this->actingAs($user)->delete("/console/vault/folders/{$folder->id}")->assertRedirect();

        $this->assertNull($entry->fresh()->folder_id);
        $this->assertSame(0, VaultFolder::count());
    }

    public function test_delete_and_restore(): void
    {
        $entry = VaultEntry::factory()->create();
        $user = $this->manager();

        $this->actingAs($user)->delete("/console/vault/{$entry->id}")->assertRedirect();
        $this->assertSoftDeleted($entry);

        $this->actingAs($user)->put("/console/vault/{$entry->id}/restore")->assertRedirect();
        $this->assertNotSoftDeleted($entry->fresh());
    }
}
