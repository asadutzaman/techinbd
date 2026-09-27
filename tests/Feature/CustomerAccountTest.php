<?php

namespace Tests\Feature;

use App\Mail\Welcome;
use App\Models\User;
use Database\Seeders\CustomerSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CustomerAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_customer_can_register_and_is_welcomed_by_email(): void
    {
        Mail::fake();

        $this->get('/register')->assertOk()->assertSee('autocomplete="new-password"', false);
        $this->post('/register', [
            'name' => 'Rahim Uddin',
            'email' => 'rahim@example.com',
            'phone' => '01711000001',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ])->assertRedirect(route('home'));

        $user = User::where('email', 'rahim@example.com')->sole();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('12345678', $user->password));
        Mail::assertSent(Welcome::class, fn (Welcome $mail) => $mail->hasTo('rahim@example.com'));

        $html = (new Welcome($user))->render();
        $this->assertStringContainsString('Welcome to MultiShop, Rahim', $html);
        $this->assertStringContainsString(route('shop'), $html);
    }

    public function test_registration_needs_a_new_email_and_an_8_character_password(): void
    {
        Mail::fake();
        User::factory()->create(['email' => 'taken@example.com']);

        $this->from('/register')->post('/register', [
            'name' => 'Someone',
            'email' => 'taken@example.com',
            'password' => 'short1',
            'password_confirmation' => 'short1',
        ])->assertRedirect('/register')->assertSessionHasErrors(['email', 'password']);

        $this->assertGuest();
        Mail::assertNothingSent();
    }

    public function test_customers_sign_in_and_out(): void
    {
        $user = User::factory()->create(['password' => '12345678']);

        $this->get('/login')->assertOk()
            ->assertSee('href="' . route('password.request') . '"', false)
            ->assertSee('autocomplete="current-password"', false);

        $this->from('/login')->post('/login', ['email' => $user->email, 'password' => 'wrong-pass'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/login', ['email' => $user->email, 'password' => '12345678'])->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);

        // Signed-in visitors don't get the sign-in pages
        $this->get('/login')->assertRedirect(route('home'));
        $this->get('/register')->assertRedirect(route('home'));

        $this->post('/logout')->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_a_password_shorter_than_the_new_minimum_still_signs_in(): void
    {
        $user = User::factory()->create(['password' => 'abc123']);

        $this->post('/login', ['email' => $user->email, 'password' => 'abc123'])->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_a_forgotten_password_is_reset_through_an_emailed_link(): void
    {
        Notification::fake();
        $user = User::factory()->create(['name' => 'Nusrat Jahan', 'email' => 'nusrat@example.com', 'password' => 'old-password']);

        $this->from('/forgot-password')->post('/forgot-password', ['email' => 'nusrat@example.com'])
            ->assertRedirect('/forgot-password')
            ->assertSessionHas('status');

        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user, &$token) {
            $token = $notification->token;
            $mail = $notification->toMail($user);

            // The link is built on APP_URL, whichever host the request came in on
            return $mail->subject === 'Reset your MultiShop password'
                && $mail->greeting === 'Hi Nusrat,'
                && $mail->actionUrl === 'http://localhost/reset-password/' . $token . '?email=nusrat%40example.com';
        });

        $this->get('/reset-password/' . $token . '?email=nusrat@example.com')
            ->assertOk()
            ->assertSee('value="nusrat@example.com"', false);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => 'nusrat@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect(route('login'))->assertSessionHas('status');

        $this->post('/login', ['email' => 'nusrat@example.com', 'password' => 'new-password'])->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_the_reset_form_does_not_reveal_who_has_an_account(): void
    {
        Notification::fake();

        $this->from('/forgot-password')->post('/forgot-password', ['email' => 'nobody@example.com'])
            ->assertRedirect('/forgot-password')
            ->assertSessionHas('status', "If there's an account for nobody@example.com, we've emailed it a link to set a new password.");

        Notification::assertNothingSent();
    }

    public function test_an_invalid_reset_link_is_refused(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);

        $this->from('/reset-password/made-up')->post('/reset-password', [
            'token' => 'made-up',
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect('/reset-password/made-up')->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    public function test_demo_customers_get_plus_addresses_of_the_shop_inbox(): void
    {
        config(['mail.from.address' => 'shop@example.com']);

        $this->seed(CustomerSeeder::class);
        $this->seed(CustomerSeeder::class);

        $customers = User::with('defaultShippingAddress')->orderBy('id')->get();
        $this->assertSame(
            ['shop+rahim@example.com', 'shop+nusrat@example.com', 'shop+tanvir@example.com', 'shop+farhana@example.com', 'shop+karim@example.com'],
            $customers->pluck('email')->all(),
            'Five customers, and running the seeder again adds none'
        );
        $this->assertSame('Dhaka', $customers[0]->defaultShippingAddress->city);
        $this->assertSame('Bangladesh', $customers[0]->defaultShippingAddress->country);

        $this->post('/login', ['email' => 'shop+nusrat@example.com', 'password' => '12345678'])->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($customers[1]);
    }
}
