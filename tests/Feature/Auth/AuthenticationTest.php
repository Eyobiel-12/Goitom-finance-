<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Otp;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_otp(): void
    {
        $user = User::factory()->create();

        // Send OTP
        $otpService = new OtpService();
        $result = $otpService->sendLoginOtp($user->email);
        
        $this->assertTrue($result['success']);

        // Get the OTP code from database
        $otp = Otp::where('email', $user->email)
            ->where('type', 'login')
            ->where('used', false)
            ->first();

        $this->assertNotNull($otp);

        // Verify OTP and login
        $response = $this->post('/otp/verify-login', [
            'email' => $user->email,
            'code' => $otp->code,
        ]);

        $this->assertAuthenticated();
        $response->assertJson(['success' => true]);
    }

    public function test_users_can_not_authenticate_with_invalid_otp(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/otp/verify-login', [
            'email' => $user->email,
            'code' => '123456', // Invalid OTP
        ]);

        $this->assertGuest();
        $response->assertJson(['success' => false]);
    }

    public function test_users_can_send_login_otp(): void
    {
        // Mock Mail facade to prevent actual email sending
        Mail::fake();
        
        $user = User::factory()->create();

        $response = $this->post('/otp/send-login', [
            'email' => $user->email,
        ]);

        $response->assertJson(['success' => true]);
        
        // Check OTP was created in database
        $this->assertDatabaseHas('otps', [
            'email' => $user->email,
            'type' => 'login',
            'used' => false,
        ]);
        
        // Assert that an email was sent
        Mail::assertSent(\App\Mail\OtpMail::class);
    }

    public function test_users_can_not_send_otp_for_nonexistent_email(): void
    {
        $response = $this->post('/otp/send-login', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertJson(['success' => false]);
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}