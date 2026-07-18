<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Notifications\PasswordResetNotification;
use App\Notifications\UserCreatedNotification;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class UserEmailFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([AccessControlSeeder::class, SettingSeeder::class]);
    }

    public function test_creating_a_user_sends_a_branded_secure_invitation(): void
    {
        Notification::fake();
        $admin = User::where('username', 'superadmin')->firstOrFail();
        $role = Role::create(['name' => 'Billing Staff', 'slug' => 'billing-staff-mail', 'is_active' => true]);

        $response = $this->actingAs($admin)->postJson(route('admin.users.store'), [
            'name' => 'Invited User',
            'username' => 'invited.user',
            'email' => 'invited@example.test',
            'mobile' => '9876543210',
            'password' => 'TempPass123',
            'role_id' => $role->id,
            'is_active' => true,
        ])->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email_sent', true);

        $user = User::findOrFail($response->json('data.id'));
        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email]);

        Notification::assertSentTo($user, UserCreatedNotification::class, function (UserCreatedNotification $notification) use ($user): bool {
            $mail = $notification->toMail($user);
            $html = view($mail->view['html'], $mail->viewData)->render();
            $text = view($mail->view['text'], $mail->viewData)->render();

            $this->assertStringContainsString('Cholavin ERP account is ready', $mail->subject);
            $this->assertStringContainsString('Set my password', $html);
            $this->assertStringContainsString('Invited User', $html);
            $this->assertStringContainsString('Billing Staff', $html);
            $this->assertStringContainsString('/admin/reset-password/', $html);
            $this->assertStringContainsString('no password is included', strtolower($html));
            $this->assertStringNotContainsString('TempPass123', $html.$text);

            return true;
        });
    }

    public function test_forgot_password_sends_the_branded_reset_notification_without_exposing_accounts(): void
    {
        Notification::fake();
        $user = User::where('username', 'superadmin')->firstOrFail();

        $this->post(route('admin.password.email'), ['email' => $user->email])
            ->assertRedirect()
            ->assertSessionHas('status', 'If that email is registered, a password reset link has been sent.');

        Notification::assertSentTo($user, PasswordResetNotification::class, function (PasswordResetNotification $notification) use ($user): bool {
            $mail = $notification->toMail($user);
            $html = view($mail->view['html'], $mail->viewData)->render();

            $this->assertStringContainsString('Reset your Cholavin ERP password', $mail->subject);
            $this->assertStringContainsString('Reset password', $html);
            $this->assertStringContainsString('/admin/reset-password/', $html);
            $this->assertStringContainsString('expires in', $html);

            return true;
        });

        $this->post(route('admin.password.email'), ['email' => 'missing@example.test'])
            ->assertRedirect()
            ->assertSessionHas('status', 'If that email is registered, a password reset link has been sent.');
    }

    public function test_password_reset_form_and_one_time_token_complete_the_flow(): void
    {
        $user = User::where('username', 'superadmin')->firstOrFail();
        $token = Password::broker()->createToken($user);

        $this->get(route('admin.password.reset', ['token' => $token, 'email' => $user->email]))
            ->assertOk()
            ->assertSee('action="'.route('admin.password.update').'"', false);

        $response = $this->post(route('admin.password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewSecure123',
            'password_confirmation' => 'NewSecure123',
        ])->assertRedirect(route('admin.auth.index'));

        $this->assertTrue(Hash::check('NewSecure123', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
        $this->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee('Your password has been reset.');
    }
}
