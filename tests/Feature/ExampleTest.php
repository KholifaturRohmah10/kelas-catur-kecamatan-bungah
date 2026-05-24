<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_root_url_redirects_guests_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_login_page_can_be_opened(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('Masuk dengan Email');
    }

    public function test_guest_is_redirected_to_login_when_opening_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_authenticated_user_can_open_registration_page(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/daftar-kelas-catur');

        $response->assertOk();
        $response->assertSee('Form Siswa');
    }

    public function test_authenticated_user_can_open_class_history_page(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/riwayat-kelas');

        $response->assertOk();
        $response->assertSee('Riwayat Pertemuan');
    }

    public function test_authenticated_user_can_open_print_all_scores_page(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/cetak-rapot/seluruh-nilai');

        $response->assertOk();
        $response->assertSee('Rekap Seluruh Nilai Siswa');
    }
}
