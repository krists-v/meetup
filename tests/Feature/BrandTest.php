<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_introduces_varza(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Varza')
            ->assertSee('Get the crew together.')
            ->assertSee('Pick a time, lock the pitch, play.');
    }

    public function test_dashboard_shows_the_varza_name(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Varza')
            ->assertSee('My upcoming events')
            ->assertSee('Public upcoming events')
            ->assertSee('No games yet');
    }

    public function test_password_reset_email_uses_the_varza_name(): void
    {
        $user = User::factory()->create();
        $mail = (new ResetPassword('token'))->toMail($user);

        $html = $mail->render();

        $this->assertSame('Reset your Varza password', $mail->subject);
        $this->assertStringContainsString('Varza', $mail->introLines[0]);
        $this->assertStringContainsString('Varza', $html);
        $this->assertStringContainsString('#1c633b', $html);
        $this->assertStringContainsString('Choose a new password', $html);
    }
}
