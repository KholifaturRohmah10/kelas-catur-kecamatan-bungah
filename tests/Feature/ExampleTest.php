<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\ClassSession;
use App\Models\Student;
use App\Models\StudentScore;
use App\Models\User;
use App\Support\GuardianSession;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
            'role' => UserRole::Admin->value,
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

    public function test_login_bypass_uses_configured_role_for_the_authenticated_account(): void
    {
        config()->set('auth.login_bypass.enabled', true);
        config()->set('auth.login_bypass.email', 'pimpinan@example.com');
        config()->set('auth.login_bypass.name', 'Pimpinan Uji');
        config()->set('auth.login_bypass.role', UserRole::Pimpinan->value);

        $response = $this->post('/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'pimpinan@example.com',
            'role' => UserRole::Pimpinan->value,
        ]);
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

    public function test_class_history_shows_all_sessions_before_month_filter_is_selected(): void
    {
        $user = User::factory()->create();
        $maySession = $this->createClassSession([
            'title' => 'Pertemuan Mei',
            'tanggal' => '2026-05-30',
        ]);
        $juneSession = $this->createClassSession([
            'title' => 'Pertemuan Juni',
            'tanggal' => '2026-06-05',
        ]);

        $response = $this->actingAs($user)->get('/riwayat-kelas');

        $response->assertOk();
        $response->assertSee('Seluruh riwayat kelas');
        $response->assertSee('Jumlah data: 2 pertemuan');
        $response->assertSee($maySession->title);
        $response->assertSee($juneSession->title);
        $response->assertSee('Semua Data');
    }

    public function test_class_history_shows_session_count_for_selected_month(): void
    {
        $user = User::factory()->create();
        $maySessionOne = $this->createClassSession([
            'title' => 'Pertemuan Mei Pertama',
            'tanggal' => '2026-05-03',
        ]);
        $maySessionTwo = $this->createClassSession([
            'title' => 'Pertemuan Mei Kedua',
            'tanggal' => '2026-05-30',
        ]);
        $juneSession = $this->createClassSession([
            'title' => 'Pertemuan Juni',
            'tanggal' => '2026-06-05',
        ]);

        $response = $this->actingAs($user)->get('/riwayat-kelas?month=2026-05');

        $response->assertOk();
        $response->assertSee('Riwayat bulan Mei 2026');
        $response->assertSee('Jumlah data: 2 pertemuan');
        $response->assertSee($maySessionOne->title);
        $response->assertSee($maySessionTwo->title);
        $response->assertDontSee($juneSession->title);
        $response->assertSee('Semua Data');
    }

    public function test_authenticated_user_can_open_print_all_scores_page(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/cetak-rapot/seluruh-nilai');

        $response->assertOk();
        $response->assertSee('Rekap Seluruh Nilai Siswa');
    }

    public function test_authenticated_user_can_see_their_role_label_in_the_account_panel(): void
    {
        $response = $this->actingAs(User::factory()->create([
            'role' => UserRole::Operator,
        ]))->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Operator');
    }

    public function test_registration_uppercases_siswa_identity_fields_before_saving(): void
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
            'school_name' => 'SD HARAPAN BANGSA',
            'parent_name' => 'WAHYU HIDAYAT',
            'address' => 'JL. MELATI NO. 3 BUNGAH',
        ]);
    }

    public function test_session_title_is_uppercased_before_saving(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $student = $this->createStudent();

        $response = $this->actingAs($user)->post('/jadwal-kelas', [
            'title' => 'latihan pembukaan sisilia',
            'material_file' => $this->fakeMaterialFile('materi-sisilia.pdf'),
            'tanggal' => '2026-05-25',
            'notes' => 'catatan kecil',
            'siswa_ids' => [$student->id],
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
        Storage::fake('local');
        $user = User::factory()->create();
        $nonactiveStudent = $this->createStudent([
            'name' => 'Siswa Nonaktif',
            'status' => 'Nonaktif',
        ]);

        $response = $this->actingAs($user)
            ->from('/jadwal-kelas')
            ->post('/jadwal-kelas', [
                'title' => 'Latihan Taktik',
                'material_file' => $this->fakeMaterialFile('latihan-taktik.pdf'),
                'tanggal' => '2026-05-25',
                'notes' => '',
                'siswa_ids' => [$nonactiveStudent->id],
                'scores' => [
                    $nonactiveStudent->id => 80,
                ],
            ]);

        $response->assertRedirect('/jadwal-kelas');
        $response->assertSessionHasErrors([
            'siswa_ids' => 'Siswa nonaktif tidak bisa dipilih untuk penilaian.',
        ]);
        $this->assertDatabaseCount('class_sessions', 0);
        $this->assertDatabaseCount('student_scores', 0);
    }

    public function test_active_student_can_be_marked_absent_and_material_stays_recorded(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $student = $this->createStudent([
            'name' => 'Siswa Absen',
        ]);

        $response = $this->actingAs($user)->post('/jadwal-kelas', [
            'title' => 'Latihan Strategi',
            'material_file' => $this->fakeMaterialFile('strategi-tengah.pdf'),
            'tanggal' => '2026-05-25',
            'notes' => '',
            'siswa_ids' => [$student->id],
            'attendance' => [
                $student->id => 'absent',
            ],
            'scores' => [
                $student->id => '',
            ],
        ]);

        $response->assertRedirect('/jadwal-kelas');
        $this->assertDatabaseHas('student_scores', [
            'siswa_id' => $student->id,
            'attendance_status' => StudentScore::STATUS_ABSENT,
            'score' => null,
        ]);
    }

    public function test_guardian_materials_show_absent_status_for_recorded_session(): void
    {
        $student = $this->createStudent([
            'name' => 'Siswa Absen',
        ]);
        $session = ClassSession::create([
            'title' => 'Materi Absen',
            'material' => 'Pertahanan dasar',
            'tanggal' => '2026-05-25',
            'notes' => null,
        ]);

        StudentScore::create([
            'sesi_kelas_id' => $session->id,
            'siswa_id' => $student->id,
            'attendance_status' => StudentScore::STATUS_ABSENT,
            'score' => null,
        ]);

        $response = $this->withSession([
            GuardianSession::SESSION_KEY => $student->id,
        ])->get('/jadwal-materi-wali-murid');

        $response->assertOk();
        $response->assertSee('MATERI ABSEN');
        $response->assertSee('Absen');
    }

    public function test_guardian_can_open_uploaded_material_file(): void
    {
        Storage::fake('local');
        $student = $this->createStudent([
            'name' => 'Siswa File',
        ]);
        Storage::disk('local')->put('materials/materi-siswa-file.pdf', 'PDF-CONTENT');

        $session = ClassSession::create([
            'title' => 'Materi File',
            'material' => 'materi-siswa-file.pdf',
            'material_file_path' => 'materials/materi-siswa-file.pdf',
            'material_file_name' => 'materi-siswa-file.pdf',
            'material_file_mime' => 'application/pdf',
            'tanggal' => '2026-05-26',
            'notes' => null,
        ]);

        StudentScore::create([
            'sesi_kelas_id' => $session->id,
            'siswa_id' => $student->id,
            'score' => 82,
        ]);

        $response = $this->withSession([
            GuardianSession::SESSION_KEY => $student->id,
        ])->get(route('sessions.material-file', $session));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_guardian_dashboard_shows_only_the_latest_material_summary(): void
    {
        $student = $this->createStudent([
            'name' => 'Siswa Ringkasan',
        ]);

        $olderSession = ClassSession::create([
            'title' => 'MATERI LAMA UNIK',
            'material' => 'Materi lama yang tidak boleh muncul di ringkasan.',
            'tanggal' => '2026-05-20',
            'notes' => null,
        ]);

        $latestSession = ClassSession::create([
            'title' => 'MATERI TERBARU UNIK',
            'material' => 'Materi terbaru yang harus muncul di dashboard wali.',
            'tanggal' => '2026-05-30',
            'notes' => null,
        ]);

        StudentScore::create([
            'sesi_kelas_id' => $olderSession->id,
            'siswa_id' => $student->id,
            'score' => 78,
        ]);

        StudentScore::create([
            'sesi_kelas_id' => $latestSession->id,
            'siswa_id' => $student->id,
            'score' => 88,
        ]);

        $response = $this->withSession([
            GuardianSession::SESSION_KEY => $student->id,
        ])->get('/dashboard-wali-murid');

        $response->assertOk();
        $response->assertSee('MATERI TERBARU UNIK');
        $response->assertDontSee('MATERI LAMA UNIK');
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
            'tanggal' => '2026-05-20',
            'notes' => null,
        ]);

        StudentScore::create([
            'sesi_kelas_id' => $session->id,
            'siswa_id' => $student->id,
            'score' => 74,
        ]);

        $response = $this->actingAs($user)->put("/riwayat-kelas/{$session->id}", [
            'title' => 'Materi Lama Revisi',
            'material' => 'Latihan dasar',
            'tanggal' => '2026-05-20',
            'notes' => 'Catatan revisi',
            'siswa_ids' => [$student->id],
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
            'sesi_kelas_id' => $session->id,
            'siswa_id' => $student->id,
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
            'tanggal' => '2026-05-20',
            'notes' => null,
        ]);

        StudentScore::create([
            'sesi_kelas_id' => $session->id,
            'siswa_id' => $student->id,
            'score' => 74,
        ]);

        $response = $this->actingAs($user)
            ->from("/riwayat-kelas/{$session->id}/edit")
            ->put("/riwayat-kelas/{$session->id}", [
                'title' => 'Materi Lama',
                'material' => 'Latihan dasar',
                'tanggal' => '2026-05-20',
                'notes' => null,
                'siswa_ids' => [$student->id],
                'scores' => [
                    $student->id => 80,
                ],
            ]);

        $response->assertRedirect("/riwayat-kelas/{$session->id}/edit");
        $response->assertSessionHasErrors([
            "scores.{$student->id}" => 'Nilai siswa nonaktif yang sudah tersimpan tidak bisa diubah.',
        ]);
        $this->assertDatabaseHas('student_scores', [
            'sesi_kelas_id' => $session->id,
            'siswa_id' => $student->id,
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
            'kode_siswa' => 'CATUR-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT),
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

    private function createClassSession(array $overrides = []): ClassSession
    {
        return ClassSession::create(array_merge([
            'title' => 'Pertemuan Uji',
            'material' => 'Materi uji',
            'tanggal' => '2026-05-01',
            'notes' => null,
        ], $overrides));
    }

    private function fakeMaterialFile(string $filename = 'materi.pdf'): UploadedFile
    {
        return UploadedFile::fake()->create($filename, 256, 'application/pdf');
    }
}
