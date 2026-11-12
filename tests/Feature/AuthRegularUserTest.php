<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Akun;
use App\Models\JenisAkun;
use App\Mail\SendOtpMail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

class AuthRegularUserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Siapkan tabel schema di sqlite in-memory
        Schema::create('jenis_akun', function (Blueprint $table) {
            $table->increments('id_jenis_akun');
            $table->string('jenis_akun')->nullable();
        });

        Schema::create('akun', function (Blueprint $table) {
            $table->increments('id_akun');
            $table->string('nama_lengkap', 60);
            $table->string('email')->unique();
            $table->string('no_telefon', 15)->unique();
            $table->string('password');
            $table->integer('jenis_akun_id_jenis_akun');
            $table->integer('token')->default(0);
            $table->dateTime('masa_awal')->nullable();
            $table->dateTime('masa_berakhir')->nullable();
            $table->string('path_gambar')->default('default.png');
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        JenisAkun::insert([
            ['id_jenis_akun' => 1, 'jenis_akun' => 'Admin'],
            ['id_jenis_akun' => 2, 'jenis_akun' => 'Pengguna Biasa'],
            ['id_jenis_akun' => 3, 'jenis_akun' => 'Mitra Usaha'],
        ]);
    }

    public function test_regular_user_login_bypasses_otp_and_redirects_to_dashboard_with_caching(): void
    {
        Mail::fake();
        Cache::flush();

        $user = Akun::create([
            'nama_lengkap' => 'Regular User Test',
            'email' => 'user@test.com',
            'no_telefon' => '081234567890',
            'password' => Hash::make('password123'),
            'jenis_akun_id_jenis_akun' => 2,
            'token' => 0,
            'masa_awal' => now(),
            'masa_berakhir' => now()->addYears(10),
            'path_gambar' => 'default.png',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'user@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'requires_otp' => false,
                'redirect_to' => '/dashboard/user',
            ])
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'redirect_to',
                'requires_otp',
                'data',
            ]);

        // Pastikan tidak ada pengiriman email OTP untuk regular user
        Mail::assertNothingSent();

        // Pastikan profil user di-cache
        $this->assertTrue(Cache::has("user_profile_{$user->id_akun}"));
    }

    public function test_non_regular_user_login_still_requires_otp(): void
    {
        Mail::fake();

        $mitra = Akun::create([
            'nama_lengkap' => 'Mitra Usaha Test',
            'email' => 'mitra@test.com',
            'no_telefon' => '089876543210',
            'password' => Hash::make('password123'),
            'jenis_akun_id_jenis_akun' => 3,
            'token' => 0,
            'masa_awal' => now(),
            'masa_berakhir' => now()->addYears(10),
            'path_gambar' => 'default.png',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'mitra@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'requires_otp' => true,
                'email' => 'mitra@test.com',
            ])
            ->assertJsonMissing(['access_token']);

        // OTP email harus terkirim untuk Mitra
        Mail::assertSent(SendOtpMail::class, function ($mail) use ($mitra) {
            return $mail->hasTo($mitra->email);
        });
    }

    public function test_regular_user_profile_uses_cache(): void
    {
        $user = Akun::create([
            'nama_lengkap' => 'Regular Cached User',
            'email' => 'cached@test.com',
            'no_telefon' => '081112223334',
            'password' => Hash::make('password123'),
            'jenis_akun_id_jenis_akun' => 2,
            'token' => 0,
            'masa_awal' => now(),
            'masa_berakhir' => now()->addYears(10),
            'path_gambar' => 'default.png',
        ]);

        $token = $user->createToken('test_token')->plainTextToken;

        // Panggilan pertama mengisi cache
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/akun/profile');

        $response->assertStatus(200)
            ->assertJsonPath('data.nama_lengkap', 'Regular Cached User');

        $this->assertTrue(Cache::has("user_profile_{$user->id_akun}"));

        // Ubah langsung di database tanpa updateProfile untuk mengecek respon berasal dari cache
        Akun::where('id_akun', $user->id_akun)->update(['nama_lengkap' => 'Direct DB Edit']);

        $responseCached = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/akun/profile');

        $responseCached->assertStatus(200)
            ->assertJsonPath('data.nama_lengkap', 'Regular Cached User');
    }

    public function test_regular_user_logout_clears_cache(): void
    {
        $user = Akun::create([
            'nama_lengkap' => 'Regular Logout User',
            'email' => 'logout@test.com',
            'no_telefon' => '081999888777',
            'password' => Hash::make('password123'),
            'jenis_akun_id_jenis_akun' => 2,
            'token' => 0,
            'masa_awal' => now(),
            'masa_berakhir' => now()->addYears(10),
            'path_gambar' => 'default.png',
        ]);

        $token = $user->createToken('test_token')->plainTextToken;
        Cache::put("user_profile_{$user->id_akun}", $user, now()->addHours(24));
        $this->assertTrue(Cache::has("user_profile_{$user->id_akun}"));

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $response->assertStatus(200);
        $this->assertFalse(Cache::has("user_profile_{$user->id_akun}"));
    }
}
