<?php

namespace Tests\Feature;

use App\Models\ClassSession;
use App\Models\Student;
use App\Models\StudentScore;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use PDOException;
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
        config()->set('auth.login_bypass.enabled', false);

        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        config()->set('auth.login_bypass.enabled', false);
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_enter_dashboard_from_login_page_when_bypass_is_enabled(): void
    {
        config()->set('auth.login_bypass.enabled', true);
        config()->set('auth.login_bypass.email', 'adminkc@gmail.com');
        config()->set('auth.login_bypass.name', 'ADMIN KELAS CATUR');

        $response = $this->post('/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'adminkc@gmail.com',
        ]);
    }

    public function test_user_can_enter_dashboard_even_when_login_fields_are_filled_if_bypass_is_enabled(): void
    {
        config()->set('auth.login_bypass.enabled', true);
        config()->set('auth.login_bypass.email', 'adminkc@gmail.com');
        config()->set('auth.login_bypass.name', 'ADMIN KELAS CATUR');

        $response = $this->post('/login', [
            'email' => 'apaaja@example.com',
            'password' => 'bebas',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertSame('adminkc@gmail.com', auth()->user()?->email);
    }

    public function test_guest_can_open_dashboard_directly_when_bypass_is_enabled(): void
    {
        config()->set('auth.login_bypass.enabled', true);
        config()->set('auth.login_bypass.email', 'adminkc@gmail.com');
        config()->set('auth.login_bypass.name', 'ADMIN KELAS CATUR');

        $response = $this->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Dashboard');
    }

    public function test_logout_returns_user_to_login_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_login_page_stays_accessible_when_database_check_fails(): void
    {
        Auth::shouldReceive('check')
            ->once()
            ->andThrow($this->databaseUnavailableException());

        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('Masuk dengan Email');
        $response->assertSee('Database MySQL belum aktif atau sedang bermasalah.');
    }

    public function test_login_shows_clear_message_when_database_is_unavailable(): void
    {
        config()->set('auth.login_bypass.enabled', false);

        Auth::shouldReceive('attempt')
            ->once()
            ->with([
                'email' => 'adminkc@gmail.com',
                'password' => 'catur1',
            ], false)
            ->andThrow($this->databaseUnavailableException());

        $response = $this->from('/login')->post('/login', [
            'email' => 'adminkc@gmail.com',
            'password' => 'catur1',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors([
            'email' => 'Database MySQL belum aktif atau sedang bermasalah. Jalankan MySQL di XAMPP lalu coba login lagi.',
        ]);
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

    public function test_registration_uppercases_student_identity_fields_before_saving(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/daftar-kelas-catur', [
            'name' => 'aditya pamungkas',
            'gender' => 'Laki-laki',
            'birth_date' => '2014-01-01',
            'school_name' => 'SD Harapan Bangsa',
            'parent_name' => 'wahyu hidayat',
            'phone' => '081234567890',
            'address' => 'jl. melati no. 3 bungah',
            'registration_date' => '2026-05-25',
            'notes' => 'catatan biasa',
        ]);

        $response->assertRedirect('/daftar-kelas-catur');
        $this->assertDatabaseHas('students', [
            'name' => 'ADITYA PAMUNGKAS',
            'parent_name' => 'WAHYU HIDAYAT',
            'address' => 'JL. MELATI NO. 3 BUNGAH',
        ]);
    }

    public function test_session_title_is_uppercased_before_saving(): void
    {
        $user = User::factory()->create();
        $student = $this->createStudent();

        $response = $this->actingAs($user)->post('/jadwal-kelas', [
            'title' => 'latihan pembukaan sisilia',
            'material' => 'materi tetap biasa',
            'session_date' => '2026-05-25',
            'notes' => 'catatan kecil',
            'student_ids' => [$student->id],
            'scores' => [
                $student->id => 88,
            ],
        ]);

        $response->assertRedirect('/jadwal-kelas');
        $this->assertDatabaseHas('class_sessions', [
            'title' => 'LATIHAN PEMBUKAAN SISILIA',
        ]);
    }

    public function test_nonactive_student_cannot_be_scored_in_new_session(): void
    {
        $user = User::factory()->create();
        $nonactiveStudent = $this->createStudent([
            'name' => 'Siswa Nonaktif',
            'status' => 'Nonaktif',
        ]);

        $response = $this->actingAs($user)
            ->from('/jadwal-kelas')
            ->post('/jadwal-kelas', [
                'title' => 'Latihan Taktik',
                'material' => 'Fork dan pin',
                'session_date' => '2026-05-25',
                'notes' => '',
                'student_ids' => [$nonactiveStudent->id],
                'scores' => [
                    $nonactiveStudent->id => 80,
                ],
            ]);

        $response->assertRedirect('/jadwal-kelas');
        $response->assertSessionHasErrors([
            'student_ids' => 'Siswa nonaktif tidak bisa dipilih untuk penilaian.',
        ]);
        $this->assertDatabaseCount('class_sessions', 0);
        $this->assertDatabaseCount('student_scores', 0);
    }

    public function test_existing_score_for_now_nonactive_student_is_preserved_when_editing_session(): void
    {
        $user = User::factory()->create();
        $student = $this->createStudent([
            'name' => 'Siswa Lama',
            'status' => 'Nonaktif',
        ]);
        $session = ClassSession::create([
            'title' => 'Materi Lama',
            'material' => 'Latihan dasar',
            'session_date' => '2026-05-20',
            'notes' => null,
        ]);

        StudentScore::create([
            'class_session_id' => $session->id,
            'student_id' => $student->id,
            'score' => 74,
        ]);

        $response = $this->actingAs($user)->put("/riwayat-kelas/{$session->id}", [
            'title' => 'Materi Lama Revisi',
            'material' => 'Latihan dasar',
            'session_date' => '2026-05-20',
            'notes' => 'Catatan revisi',
            'student_ids' => [$student->id],
            'scores' => [
                $student->id => 74,
            ],
        ]);

        $response->assertRedirect('/riwayat-kelas');
        $this->assertDatabaseHas('class_sessions', [
            'id' => $session->id,
            'title' => 'MATERI LAMA REVISI',
            'notes' => 'Catatan revisi',
        ]);
        $this->assertDatabaseHas('student_scores', [
            'class_session_id' => $session->id,
            'student_id' => $student->id,
            'score' => 74,
        ]);
    }

    public function test_existing_score_for_now_nonactive_student_cannot_be_changed(): void
    {
        $user = User::factory()->create();
        $student = $this->createStudent([
            'name' => 'Siswa Lama',
            'status' => 'Nonaktif',
        ]);
        $session = ClassSession::create([
            'title' => 'Materi Lama',
            'material' => 'Latihan dasar',
            'session_date' => '2026-05-20',
            'notes' => null,
        ]);

        StudentScore::create([
            'class_session_id' => $session->id,
            'student_id' => $student->id,
            'score' => 74,
        ]);

        $response = $this->actingAs($user)
            ->from("/riwayat-kelas/{$session->id}/edit")
            ->put("/riwayat-kelas/{$session->id}", [
                'title' => 'Materi Lama',
                'material' => 'Latihan dasar',
                'session_date' => '2026-05-20',
                'notes' => null,
                'student_ids' => [$student->id],
                'scores' => [
                    $student->id => 80,
                ],
            ]);

        $response->assertRedirect("/riwayat-kelas/{$session->id}/edit");
        $response->assertSessionHasErrors([
            "scores.{$student->id}" => 'Nilai siswa nonaktif yang sudah tersimpan tidak bisa diubah.',
        ]);
        $this->assertDatabaseHas('student_scores', [
            'class_session_id' => $session->id,
            'student_id' => $student->id,
            'score' => 74,
        ]);
    }

    private function databaseUnavailableException(): QueryException
    {
        return new QueryException(
            'mysql',
            'select * from `users` where `email` = ? limit 1',
            ['adminkc@gmail.com'],
            new PDOException('SQLSTATE[HY000] [2002] No connection could be made because the target machine actively refused it'),
        );
    }

    private function createStudent(array $overrides = []): Student
    {
        return Student::create(array_merge([
            'student_code' => 'CATUR-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT),
            'name' => 'Siswa Uji',
            'gender' => 'Laki-laki',
            'birth_date' => '2014-01-01',
            'school_name' => 'SD Uji',
            'parent_name' => 'Orang Tua Uji',
            'phone' => '081234567890',
            'address' => 'Jl. Uji',
            'registration_date' => '2026-05-01',
            'status' => 'Aktif',
            'notes' => null,
        ], $overrides));
    }
}
