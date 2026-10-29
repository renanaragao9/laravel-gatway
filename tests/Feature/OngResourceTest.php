<?php

namespace Tests\Feature;

use App\Models\Ong;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OngResourceTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        return $user;
    }

    public function test_guest_is_redirected_from_ongs_panel(): void
    {
        $this->get('/admin/ongs')->assertRedirect();
    }

    public function test_admin_can_access_the_ongs_list_page(): void
    {
        $this->actingAsAdmin();

        $this->get('/admin/ongs')->assertSuccessful();
    }

    public function test_admin_can_access_the_create_ong_page(): void
    {
        $this->actingAsAdmin();

        $this->get('/admin/ongs/create')->assertSuccessful();
    }

    public function test_ong_can_be_persisted(): void
    {
        $ong = Ong::create([
            'name' => 'ONG Exemplo',
            'email' => 'contato@ong.org',
            'phone' => '(11) 99999-0000',
            'city' => 'São Paulo',
            'description' => 'Organização de teste.',
            'active' => true,
        ]);

        $this->assertDatabaseHas('ongs', [
            'id' => $ong->id,
            'name' => 'ONG Exemplo',
            'city' => 'São Paulo',
            'active' => true,
        ]);

        $this->assertIsBool($ong->fresh()->active);
    }
}
