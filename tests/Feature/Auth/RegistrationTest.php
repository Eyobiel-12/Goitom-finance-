<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Otp;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register_with_otp_verification(): void
    {
        // Mock Mail facade to prevent actual email sending
        Mail::fake();
        
        // Step 1: Send registration OTP
        $response = $this->post('/otp/send-registration', [
            'email' => 'test@example.com',
        ]);

        $response->assertJson(['success' => true]);

        // Step 2: Verify OTP
        $otp = Otp::where('email', 'test@example.com')
            ->where('type', 'registration')
            ->where('used', false)
            ->first();

        $this->assertNotNull($otp);

        $response = $this->post('/otp/verify-registration', [
            'email' => 'test@example.com',
            'code' => $otp->code,
        ]);

        $response->assertJson(['success' => true]);

        // Step 3: Complete registration
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        
        // Check user was created
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);
        
        // Assert that an email was sent
        Mail::assertSent(\App\Mail\OtpMail::class);
    }

    public function test_registration_requires_otp_verification(): void
    {
        // Try to register without OTP verification
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_users_can_send_registration_otp(): void
    {
        // Mock Mail facade to prevent actual email sending
        Mail::fake();
        
        $response = $this->post('/otp/send-registration', [
            'email' => 'newuser@example.com',
        ]);

        $response->assertJson(['success' => true]);
        
        // Check OTP was created in database
        $this->assertDatabaseHas('otps', [
            'email' => 'newuser@example.com',
            'type' => 'registration',
            'used' => false,
        ]);
        
        // Assert that an email was sent
        Mail::assertSent(\App\Mail\OtpMail::class);
    }

    public function test_users_can_not_send_registration_otp_for_existing_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post('/otp/send-registration', [
            'email' => 'existing@example.com',
        ]);

        $response->assertJson(['success' => false]);
    }
}