<?php

namespace Tests\Feature;

use App\Models\GatewayAccount;
use App\Models\Ong;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_lists_active_ongs_with_donate_link(): void
    {
        $active = Ong::create(['name' => 'ONG Ativa', 'active' => true, 'description' => 'Ajuda comunitária local']);
        $inactive = Ong::create(['name' => 'ONG Inativa', 'active' => false]);

        $response = $this->get('/');

        $response->assertSuccessful()
            ->assertSee('ONG Ativa')
            ->assertSee('Ajuda comunitária local')
            ->assertDontSee('ONG Inativa')
            ->assertSee(route('checkout.store', $active->id));
    }

    public function test_home_paginates_with_more_than_nine_ongs(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            Ong::create(['name' => 'ONG '.str_pad((string) $i, 2, '0', STR_PAD_LEFT), 'active' => true]);
        }

        $this->get('/')
            ->assertSuccessful()
            ->assertSee('Próxima')
            ->assertSee('Página 1 de 2');

        $this->get('/?page=2')
            ->assertSuccessful()
            ->assertSee('Anterior');
    }

    public function test_quick_donate_from_home_creates_payment(): void
    {
        GatewayAccount::create([
            'provider' => 'asaas',
            'mode' => 'sandbox',
            'credentials' => ['api_key' => 'key-123'],
            'active' => true,
        ]);

        Http::fake([
            '*/customers' => Http::response(['id' => 'cus_1'], 200),
            '*/payments/pay_1/pixQrCode' => Http::response(['payload' => 'PIX', 'encodedImage' => 'img'], 200),
            '*/payments' => Http::response(['id' => 'pay_1', 'status' => 'PENDING', 'value' => 30.0], 200),
        ]);

        $ong = Ong::create(['name' => 'ONG Ativa', 'active' => true]);

        $this->post(route('checkout.store', $ong->id), ['amount' => '30'])
            ->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'ong_id' => $ong->id,
            'amount_cents' => 3000,
            'status' => 'pending',
        ]);
    }

    public function test_home_shows_empty_state_without_ongs(): void
    {
        $this->get('/')
            ->assertSuccessful()
            ->assertSee('Nenhuma ONG disponível');
    }
}
