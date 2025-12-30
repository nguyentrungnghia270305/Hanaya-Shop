<?php

namespace Tests\Feature\Admin\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /**
     * @test
     */
    public function admin_can_view_users_list()
    {
        User::factory()->count(10)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.user'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
    }

    /**
     * @test
     */
    public function admin_can_view_user_details()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.user.show', $user));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.show');
    }

    /**
     * @test
     */
    public function admin_can_update_user_info()
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'role' => 'user',
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.user.update', $user), [
                'name' => 'New Name',
                'email' => 'new@example.com',
                'role' => 'admin',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
            'role' => 'admin',
        ]);
    }

    /**
     * @test
     */
    public function admin_cannot_edit_themselves()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.user.edit', $this->admin));

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function admin_cannot_delete_themselves()
    {
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.user.destroy', $this->admin));

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function admin_can_delete_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.user.destroy', $user));

        $response->assertRedirect();
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    /**
     * @test
     */
    public function admin_can_search_users()
    {
        User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.user.search', ['query' => 'John']));

        $response->assertStatus(200);
        $response->assertJsonStructure(['html']);

        $html = $response->json('html');
        $this->assertStringContainsString('John Doe', $html);
        $this->assertStringNotContainsString('Jane Smith', $html);
    }
}
