# Code Quality & Testing Fundamentals Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Give the ONCC Sénégal Laravel app a real test suite, replace the three ad hoc root PHP scripts with tested Artisan commands (fixing a silent bug along the way), and make CI actually test the app it runs (SQLite, not the unused MySQL service) with a style gate that blocks on Pint violations.

**Architecture:** No new architectural layers. Tests are added under `tests/Feature` using Laravel's HTTP test client (`RefreshDatabase` + in-memory SQLite, already configured in `phpunit.xml`). The three root scripts become one Artisan command each under `app/Console/Commands`, auto-discovered by `app/Console/Kernel.php` (already does `$this->load(__DIR__.'/Commands')`). `database/factories/UserFactory.php` is fixed to match the real `users` schema (it currently sets a `name` field that doesn't exist on the model) and gets `admin()`/`inactive()` states so tests can build fixtures declaratively.

Because most of this work adds tests for **already-implemented** behavior (login, registration, admin middleware, rate limiting), those tasks aren't strict red→green TDD — there's no bug to fix, so the test is written and then run once to confirm it **passes**, which pins the behavior against future regressions. The two exceptions are the new Artisan commands (genuinely new code: write test → confirm it fails because the command doesn't exist yet → implement → confirm it passes) and `users:activate-all`, which intentionally fixes a bug found during design (see Task 9).

**Tech Stack:** Laravel 10.48, PHPUnit 10.4, SQLite (`:memory:` in tests), Laravel Pint.

---

## Important context for the engineer

- Run all commands from the Laravel project root: `C:\Users\broto\OneDrive\Bureau\oncc\oncc-senegal-laravel`.
- This repo currently has ~40 files of **unrelated, in-progress work** (controllers, views, seeders, routes) sitting uncommitted in the working tree. **Do not touch, stage, or commit those files.** Every `git add` in this plan names exact file paths — never use `git add -A` or `git add .`.
- `database/database.sqlite` was already removed from git tracking and purged from history in a prior session; it's gitignored now. It still exists on disk locally (needed to run the app) — that's expected, leave it alone.
- Test database is in-memory SQLite per `phpunit.xml` (`DB_DATABASE=:memory:`), so tests never touch the local `database/database.sqlite` file.

## File Structure

**Create:**
- `app/Console/Commands/ListAdmins.php` — `users:list-admins`
- `app/Console/Commands/ActivateAllUsers.php` — `users:activate-all`
- `app/Console/Commands/PromoteUserToAdmin.php` — `users:promote {email}`
- `tests/Feature/AuthTest.php` — registration, login, email verification, password reset, logout
- `tests/Feature/AdminMiddlewareTest.php` — `AdminMiddleware` access control
- `tests/Feature/RateLimitMiddlewareTest.php` — `RateLimitMiddleware` throttling
- `tests/Feature/UserCommandsTest.php` — the three new Artisan commands

**Modify:**
- `database/factories/UserFactory.php` — fix schema mismatch, add `admin()`/`inactive()` states
- `.github/workflows/ci.yml` — drop dead MySQL service, create the SQLite file, enforce Pint

**Delete:**
- `app/Console/Commands/ActivateUser.php` (empty, unimplemented stub — superseded by `ActivateAllUsers`)
- `activate-users.php`, `check-admin.php`, `promote-to-admin.php` (root scripts)
- `tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php` (default stubs)

---

### Task 1: Fix `UserFactory` to match the real schema

**Files:**
- Modify: `database/factories/UserFactory.php`

The factory currently sets a `name` field (Laravel's default stub) but the `users` table has `nom`/`prenom`, and there's no `role`/`statut` control for tests that need an admin or inactive user.

- [ ] **Step 1: Replace the factory definition**

Read the current file first, then replace its contents with:

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => fake()->lastName(),
            'prenom' => fake()->firstName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'public',
            'statut' => 'actif',
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user has the admin role.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    /**
     * Indicate that the user's account is deactivated.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'inactif',
        ]);
    }
}
```

- [ ] **Step 2: Verify the factory works**

Run: `php artisan tinker --execute="echo App\Models\User::factory()->admin()->make()->role;"`
Expected output: `admin`

- [ ] **Step 3: Commit**

```bash
git add database/factories/UserFactory.php
git commit -m "fix: align UserFactory with the real users schema

Replaces the default 'name' field (which doesn't exist on the users
table) with nom/prenom, and adds admin()/inactive() states so tests
can build role- and status-specific fixtures."
```

---

### Task 2: `AuthTest` — registration & email verification

**Files:**
- Create: `tests/Feature/AuthTest.php`

- [ ] **Step 1: Write the tests**

```php
<?php

namespace Tests\Feature;

use App\Mail\ResetPasswordEmail;
use App\Mail\VerifyEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_an_inactive_unverified_user_and_sends_verification_email(): void
    {
        Mail::fake();

        $response = $this->post('/register', [
            'email' => 'nouveau@test.sn',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nom' => 'Ndiaye',
            'prenom' => 'Awa',
            'role' => 'chercheur',
        ]);

        $response->assertRedirect(route('login'));

        $user = User::where('email', 'nouveau@test.sn')->firstOrFail();
        $this->assertSame('inactif', $user->statut);
        $this->assertNull($user->email_verified_at);
        $this->assertNotNull($user->verification_token);

        Mail::assertSent(VerifyEmail::class, fn ($mail) => $mail->hasTo($user->email));
    }

    public function test_verify_email_activates_the_account_with_a_valid_token(): void
    {
        $user = User::factory()->unverified()->create([
            'statut' => 'inactif',
            'verification_token' => 'valid-token',
            'verification_token_expires' => now()->addHour(),
        ]);

        $response = $this->get('/verify-email/valid-token');

        $response->assertRedirect(route('login'));

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame('actif', $user->statut);
        $this->assertNull($user->verification_token);
    }

    public function test_verify_email_rejects_an_expired_token(): void
    {
        $user = User::factory()->unverified()->create([
            'verification_token' => 'expired-token',
            'verification_token_expires' => now()->subHour(),
        ]);

        $response = $this->get('/verify-email/expired-token');

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertNull($user->refresh()->email_verified_at);
    }
}
```

- [ ] **Step 2: Run the tests**

Run: `php artisan test --filter=AuthTest`
Expected: `3 passed` (this pins existing registration/verification behavior — no implementation change needed)

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/AuthTest.php
git commit -m "test: cover registration and email verification"
```

---

### Task 3: `AuthTest` — login scenarios

**Files:**
- Modify: `tests/Feature/AuthTest.php`

- [ ] **Step 1: Add login tests**

Insert these methods inside the `AuthTest` class, after `test_verify_email_rejects_an_expired_token`:

```php

    public function test_login_succeeds_with_valid_credentials_for_an_active_verified_user(): void
    {
        $user = User::factory()->create([
            'email' => 'ok@test.sn',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'ok@test.sn',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'ok@test.sn',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'ok@test.sn',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_blocks_an_unverified_account(): void
    {
        User::factory()->unverified()->create([
            'email' => 'nonverifie@test.sn',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'nonverifie@test.sn',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_blocks_an_inactive_account(): void
    {
        User::factory()->inactive()->create([
            'email' => 'inactif@test.sn',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'inactif@test.sn',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
```

- [ ] **Step 2: Run the tests**

Run: `php artisan test --filter=AuthTest`
Expected: `7 passed`

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/AuthTest.php
git commit -m "test: cover login success/failure/unverified/inactive cases"
```

---

### Task 4: `AuthTest` — password reset flow

**Files:**
- Modify: `tests/Feature/AuthTest.php`

- [ ] **Step 1: Add password reset tests**

Insert these methods after `test_login_blocks_an_inactive_account`:

```php

    public function test_forgot_password_sends_a_reset_email_for_a_known_address(): void
    {
        Mail::fake();
        $user = User::factory()->create(['email' => 'connu@test.sn']);

        $response = $this->post('/forgot-password', ['email' => 'connu@test.sn']);

        $response->assertSessionHas('success');
        $this->assertNotNull($user->refresh()->reset_token);
        Mail::assertSent(ResetPasswordEmail::class);
    }

    public function test_forgot_password_shows_a_generic_message_for_an_unknown_address(): void
    {
        Mail::fake();

        $response = $this->post('/forgot-password', ['email' => 'inconnu@test.sn']);

        $response->assertSessionHas('success');
        Mail::assertNothingSent();
    }

    public function test_reset_password_updates_the_password_with_a_valid_token(): void
    {
        $user = User::factory()->create([
            'reset_token' => 'valid-reset-token',
            'reset_token_expires' => now()->addHour(),
        ]);

        $response = $this->post('/reset-password', [
            'token' => 'valid-reset-token',
            'email' => $user->email,
            'password' => 'nouveaumdp123',
            'password_confirmation' => 'nouveaumdp123',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('nouveaumdp123', $user->refresh()->password));
        $this->assertNull($user->reset_token);
    }

    public function test_reset_password_rejects_an_expired_token(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('ancienmdp123'),
            'reset_token' => 'expired-reset-token',
            'reset_token_expires' => now()->subHour(),
        ]);

        $response = $this->post('/reset-password', [
            'token' => 'expired-reset-token',
            'email' => $user->email,
            'password' => 'nouveaumdp123',
            'password_confirmation' => 'nouveaumdp123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('ancienmdp123', $user->refresh()->password));
    }
```

- [ ] **Step 2: Run the tests**

Run: `php artisan test --filter=AuthTest`
Expected: `11 passed`

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/AuthTest.php
git commit -m "test: cover password reset request and confirmation"
```

---

### Task 5: `AuthTest` — logout

**Files:**
- Modify: `tests/Feature/AuthTest.php`

- [ ] **Step 1: Add the logout test**

Insert after `test_reset_password_rejects_an_expired_token`:

```php

    public function test_logout_ends_the_session(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
```

- [ ] **Step 2: Run the full file**

Run: `php artisan test --filter=AuthTest`
Expected: `12 passed`

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/AuthTest.php
git commit -m "test: cover logout"
```

---

### Task 6: `AdminMiddlewareTest`

**Files:**
- Create: `tests/Feature/AdminMiddlewareTest.php`

- [ ] **Step 1: Write the tests**

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_user_is_forbidden(): void
    {
        $user = User::factory()->create(['role' => 'chercheur']);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertForbidden();
    }

    public function test_inactive_admin_is_logged_out_and_redirected(): void
    {
        $user = User::factory()->admin()->inactive()->create();

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_active_admin_can_access_the_dashboard(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertOk();
    }
}
```

- [ ] **Step 2: Run the tests**

Run: `php artisan test --filter=AdminMiddlewareTest`
Expected: `4 passed`

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/AdminMiddlewareTest.php
git commit -m "test: cover AdminMiddleware access control"
```

---

### Task 7: `RateLimitMiddlewareTest`

**Files:**
- Create: `tests/Feature/RateLimitMiddlewareTest.php`

`RateLimitMiddleware` exists but isn't attached to any route in `routes/web.php`, so this test registers a temporary route bound to the middleware to exercise it over HTTP — it doesn't touch `routes/web.php`.

- [ ] **Step 1: Write the tests**

```php
<?php

namespace Tests\Feature;

use App\Http\Middleware\RateLimitMiddleware;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RateLimitMiddlewareTest extends TestCase
{
    protected function defineRateLimitedTestRoute(int $maxAttempts, int $decayMinutes): void
    {
        Route::middleware(RateLimitMiddleware::class.":{$maxAttempts},{$decayMinutes}")
            ->get('/__test-rate-limit', fn () => response('ok'));
    }

    public function test_it_allows_requests_under_the_limit(): void
    {
        $this->defineRateLimitedTestRoute(maxAttempts: 2, decayMinutes: 1);

        $this->get('/__test-rate-limit')->assertOk();
        $this->get('/__test-rate-limit')->assertOk();
    }

    public function test_it_blocks_requests_once_the_limit_is_exceeded(): void
    {
        $this->defineRateLimitedTestRoute(maxAttempts: 2, decayMinutes: 1);

        $this->get('/__test-rate-limit')->assertOk();
        $this->get('/__test-rate-limit')->assertOk();
        $response = $this->get('/__test-rate-limit');

        $response->assertStatus(429);
        $response->assertJson(['error' => 'Trop de tentatives. Veuillez réessayer plus tard.']);
    }
}
```

- [ ] **Step 2: Run the tests**

Run: `php artisan test --filter=RateLimitMiddlewareTest`
Expected: `2 passed`

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/RateLimitMiddlewareTest.php
git commit -m "test: cover RateLimitMiddleware throttling behavior"
```

---

### Task 8: `users:list-admins` command

**Files:**
- Create: `app/Console/Commands/ListAdmins.php`
- Create: `tests/Feature/UserCommandsTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_admins_shows_only_admin_users(): void
    {
        User::factory()->admin()->create(['email' => 'admin@test.sn']);
        User::factory()->create(['role' => 'public', 'email' => 'public@test.sn']);

        $this->artisan('users:list-admins')
            ->assertExitCode(0)
            ->expectsOutputToContain('admin@test.sn')
            ->expectsOutputToContain("Nombre d'administrateurs : 1");
    }

    public function test_list_admins_falls_back_to_all_users_when_none_are_admin(): void
    {
        User::factory()->create(['role' => 'public', 'email' => 'public@test.sn']);

        $this->artisan('users:list-admins')
            ->assertExitCode(0)
            ->expectsOutputToContain('Aucun administrateur trouvé')
            ->expectsOutputToContain('public@test.sn');
    }
}
```

- [ ] **Step 2: Run it to verify it fails**

Run: `php artisan test --filter=UserCommandsTest`
Expected: FAIL — `There are no commands defined in the "users" namespace.`

- [ ] **Step 3: Implement the command**

```php
<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ListAdmins extends Command
{
    protected $signature = 'users:list-admins';

    protected $description = 'Liste les utilisateurs ayant le rôle administrateur';

    public function handle(): int
    {
        $admins = User::where('role', 'admin')->get();

        if ($admins->isEmpty()) {
            $this->warn('Aucun administrateur trouvé dans la base de données.');
            $this->line('');
            $this->info('Tous les utilisateurs :');

            $allUsers = User::all();

            if ($allUsers->isEmpty()) {
                $this->line('Aucun utilisateur trouvé.');

                return self::SUCCESS;
            }

            $this->table(
                ['ID', 'Nom', 'Email', 'Rôle', 'Statut'],
                $allUsers->map(fn (User $user) => [
                    $user->id, $user->nom, $user->email, $user->role, $user->statut,
                ])->all()
            );

            return self::SUCCESS;
        }

        $this->info("Nombre d'administrateurs : {$admins->count()}");

        $this->table(
            ['ID', 'Nom', 'Email', 'Rôle', 'Statut', 'Email vérifié', 'Créé le'],
            $admins->map(fn (User $admin) => [
                $admin->id,
                $admin->nom,
                $admin->email,
                $admin->role,
                $admin->statut,
                $admin->email_verified_at ? 'Oui' : 'Non',
                $admin->created_at->format('d/m/Y H:i'),
            ])->all()
        );

        return self::SUCCESS;
    }
}
```

- [ ] **Step 4: Run it to verify it passes**

Run: `php artisan test --filter=UserCommandsTest`
Expected: `2 passed`

- [ ] **Step 5: Commit**

```bash
git add app/Console/Commands/ListAdmins.php tests/Feature/UserCommandsTest.php
git commit -m "feat: add users:list-admins Artisan command

Replaces check-admin.php with a testable command."
```

---

### Task 9: `users:activate-all` command (fixes the silent activation bug)

**Files:**
- Create: `app/Console/Commands/ActivateAllUsers.php`
- Delete: `app/Console/Commands/ActivateUser.php`
- Modify: `tests/Feature/UserCommandsTest.php`

`activate-users.php` calls `$user->update(['email_verified' => true, ...])`, but the `users` table column is `email_verified_at` and `email_verified` isn't in `User::$fillable` — Eloquent silently drops that key, so the script never actually verifies anyone despite printing a success message. The test below asserts `email_verified_at` gets set, which only passes because the new command uses the correct column.

- [ ] **Step 1: Write the failing tests**

Add to `tests/Feature/UserCommandsTest.php`, after `test_list_admins_falls_back_to_all_users_when_none_are_admin`:

```php

    public function test_activate_all_activates_unverified_users_with_the_force_flag(): void
    {
        $user = User::factory()->unverified()->create([
            'statut' => 'inactif',
            'verification_token' => 'abc123',
            'verification_token_expires' => now()->addDay(),
        ]);

        $this->artisan('users:activate-all', ['--force' => true])
            ->assertExitCode(0);

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame('actif', $user->statut);
        $this->assertNull($user->verification_token);
    }

    public function test_activate_all_does_nothing_when_the_user_declines_confirmation(): void
    {
        $user = User::factory()->unverified()->create();

        $this->artisan('users:activate-all')
            ->expectsConfirmation('Activer ces 1 compte(s) ?', 'no')
            ->assertExitCode(1);

        $this->assertNull($user->refresh()->email_verified_at);
    }

    public function test_activate_all_reports_when_there_is_nothing_to_do(): void
    {
        User::factory()->create();

        $this->artisan('users:activate-all')
            ->assertExitCode(0)
            ->expectsOutputToContain("Aucun compte en attente d'activation.");
    }
```

- [ ] **Step 2: Run it to verify it fails**

Run: `php artisan test --filter=UserCommandsTest`
Expected: FAIL — `There are no commands defined in the "users" namespace` for `users:activate-all`

- [ ] **Step 3: Implement the command**

```php
<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ActivateAllUsers extends Command
{
    protected $signature = 'users:activate-all {--force : Ne pas demander de confirmation}';

    protected $description = "Active tous les comptes utilisateurs dont l'email n'est pas encore vérifié";

    public function handle(): int
    {
        $users = User::whereNull('email_verified_at')->get();

        if ($users->isEmpty()) {
            $this->info("Aucun compte en attente d'activation.");

            return self::SUCCESS;
        }

        $this->table(
            ['Email', 'Nom', 'Statut'],
            $users->map(fn (User $user) => [$user->email, "{$user->nom} {$user->prenom}", $user->statut])->all()
        );

        if (! $this->option('force') && ! $this->confirm("Activer ces {$users->count()} compte(s) ?")) {
            $this->warn('Opération annulée.');

            return self::FAILURE;
        }

        foreach ($users as $user) {
            $user->update([
                'email_verified_at' => now(),
                'statut' => 'actif',
                'verification_token' => null,
                'verification_token_expires' => null,
            ]);

            $this->line("✓ Compte activé : {$user->email}");
        }

        $this->info('Tous les comptes ont été activés.');

        return self::SUCCESS;
    }
}
```

- [ ] **Step 4: Run it to verify it passes**

Run: `php artisan test --filter=UserCommandsTest`
Expected: `5 passed`

- [ ] **Step 5: Delete the superseded empty stub**

```bash
git rm app/Console/Commands/ActivateUser.php
```

- [ ] **Step 6: Commit**

```bash
git add app/Console/Commands/ActivateAllUsers.php tests/Feature/UserCommandsTest.php
git commit -m "feat: add users:activate-all Artisan command

Replaces activate-users.php and fixes a bug where it updated a
nonexistent 'email_verified' field instead of 'email_verified_at',
so accounts were never actually verified despite the success message.
Also removes the empty, unimplemented ActivateUser stub command."
```

---

### Task 10: `users:promote` command

**Files:**
- Create: `app/Console/Commands/PromoteUserToAdmin.php`
- Modify: `tests/Feature/UserCommandsTest.php`

- [ ] **Step 1: Write the failing tests**

Add to `tests/Feature/UserCommandsTest.php`, after `test_activate_all_reports_when_there_is_nothing_to_do`:

```php

    public function test_promote_sets_the_users_role_to_admin(): void
    {
        $user = User::factory()->create(['role' => 'public', 'email' => 'user@test.sn']);

        $this->artisan('users:promote', ['email' => 'user@test.sn'])
            ->assertExitCode(0);

        $this->assertSame('admin', $user->refresh()->role);
    }

    public function test_promote_fails_for_an_unknown_email(): void
    {
        $this->artisan('users:promote', ['email' => 'inconnu@test.sn'])
            ->assertExitCode(1)
            ->expectsOutputToContain('Utilisateur non trouvé');
    }
```

- [ ] **Step 2: Run it to verify it fails**

Run: `php artisan test --filter=UserCommandsTest`
Expected: FAIL — `There are no commands defined in the "users" namespace` for `users:promote`

- [ ] **Step 3: Implement the command**

```php
<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class PromoteUserToAdmin extends Command
{
    protected $signature = "users:promote {email : L'email de l'utilisateur à promouvoir}";

    protected $description = 'Promeut un utilisateur au rôle administrateur';

    public function handle(): int
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("Utilisateur non trouvé : {$email}");

            return self::FAILURE;
        }

        $previousRole = $user->role;
        $user->update(['role' => 'admin']);

        $this->info("{$user->nom} {$user->prenom} ({$user->email}) est maintenant administrateur (rôle précédent : {$previousRole}).");

        return self::SUCCESS;
    }
}
```

- [ ] **Step 4: Run it to verify it passes**

Run: `php artisan test --filter=UserCommandsTest`
Expected: `7 passed`

- [ ] **Step 5: Commit**

```bash
git add app/Console/Commands/PromoteUserToAdmin.php tests/Feature/UserCommandsTest.php
git commit -m "feat: add users:promote Artisan command

Replaces promote-to-admin.php, taking the email as an argument
instead of a hardcoded address."
```

---

### Task 11: Remove obsolete root scripts and stub tests

**Files:**
- Delete: `activate-users.php`, `check-admin.php`, `promote-to-admin.php`
- Delete: `tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php`

All three root scripts are now fully replaced by tested Artisan commands (Tasks 8–10), and the default PHPUnit stub tests add no value now that real tests exist.

- [ ] **Step 1: Delete the files**

```bash
git rm activate-users.php check-admin.php promote-to-admin.php tests/Feature/ExampleTest.php tests/Unit/ExampleTest.php
```

- [ ] **Step 2: Commit**

```bash
git commit -m "chore: remove root ad hoc scripts and default test stubs

activate-users.php, check-admin.php, and promote-to-admin.php are
replaced by users:activate-all, users:list-admins, and users:promote
(see previous commits). The default ExampleTest stubs are superseded
by the real Feature test suite added in this branch."
```

---

### Task 12: Run the full suite

**Files:** none (verification only)

- [ ] **Step 1: Run every test**

Run: `php artisan test`
Expected: All tests pass (25 total: 12 in `AuthTest`, 4 in `AdminMiddlewareTest`, 2 in `RateLimitMiddlewareTest`, 7 in `UserCommandsTest`, plus any pre-existing tests outside this plan's scope). If any pre-existing test outside this plan fails, stop and investigate before continuing — do not proceed with a red suite.

---

### Task 13: Format the new/modified files with Pint

**Files:**
- Modify (formatting only): the 8 files created/modified in Tasks 1–10

Only the files this plan touched are formatted — the ~40 files of unrelated in-progress work in the working tree are deliberately left alone so this plan doesn't interfere with them.

- [ ] **Step 1: Run Pint on exactly these files**

```bash
./vendor/bin/pint \
  database/factories/UserFactory.php \
  app/Console/Commands/ListAdmins.php \
  app/Console/Commands/ActivateAllUsers.php \
  app/Console/Commands/PromoteUserToAdmin.php \
  tests/Feature/AuthTest.php \
  tests/Feature/AdminMiddlewareTest.php \
  tests/Feature/RateLimitMiddlewareTest.php \
  tests/Feature/UserCommandsTest.php
```

Expected: Pint reports `0 files with style issues` or lists a few auto-fixed files (the code above already follows Pint's default Laravel preset, so little to no diff is expected).

- [ ] **Step 2: Re-run the suite to confirm formatting didn't break anything**

Run: `php artisan test`
Expected: same pass count as Task 12.

- [ ] **Step 3: Commit if Pint changed anything**

```bash
git status --short database/factories/UserFactory.php app/Console/Commands/ListAdmins.php app/Console/Commands/ActivateAllUsers.php app/Console/Commands/PromoteUserToAdmin.php tests/Feature/AuthTest.php tests/Feature/AdminMiddlewareTest.php tests/Feature/RateLimitMiddlewareTest.php tests/Feature/UserCommandsTest.php
```

If that shows any modified files:

```bash
git add database/factories/UserFactory.php app/Console/Commands/ListAdmins.php app/Console/Commands/ActivateAllUsers.php app/Console/Commands/PromoteUserToAdmin.php tests/Feature/AuthTest.php tests/Feature/AdminMiddlewareTest.php tests/Feature/RateLimitMiddlewareTest.php tests/Feature/UserCommandsTest.php
git commit -m "style: format new files with Pint"
```

If it shows nothing, skip the commit — there's nothing to do.

---

### Task 14: Fix CI to test the real environment and enforce style

**Files:**
- Modify: `.github/workflows/ci.yml`

The MySQL service is dead weight (`.env.example` forces `DB_CONNECTION=sqlite`, so MySQL is never used), and the workflow never creates `database/database.sqlite`, which is required now that the file is gitignored (Task done in a prior session). Pint currently runs with `|| true`, so style violations never fail the build.

- [ ] **Step 1: Replace the workflow**

Read the current file first, then replace its contents with:

```yaml
name: CI

on:
  push:
    branches: [ main ]
  pull_request:
    branches: [ main ]

jobs:
  build:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: mbstring, pdo_sqlite, gd

      - name: Setup Node
        uses: actions/setup-node@v4
        with:
          node-version: '18'

      - name: Install Composer deps
        run: composer install --no-progress --no-suggest --prefer-dist

      - name: Install NPM deps
        run: npm ci

      - name: Copy env
        run: cp .env.example .env

      - name: Create SQLite database file
        run: mkdir -p database && touch database/database.sqlite

      - name: Generate key & migrate
        run: |
          php artisan key:generate
          php artisan migrate --force

      - name: Check code style (Pint)
        run: ./vendor/bin/pint --test

      - name: Run tests
        run: php artisan test --no-coverage

      - name: Run composer audit
        run: composer audit || true

      - name: Run npm audit
        run: npm audit --audit-level=moderate || true
```

- [ ] **Step 2: Commit**

```bash
git add .github/workflows/ci.yml
git commit -m "fix(ci): test the real SQLite setup and enforce Pint

Removes the unused MySQL service, creates database/database.sqlite
before migrating (required now that it's gitignored), and makes the
Pint step block the build on style violations instead of always
passing."
```

Note for the engineer: this repo has other files (outside this plan's scope) sitting uncommitted with in-progress work. Once those are committed, if they aren't Pint-clean, this CI job will fail on them — that's expected and intentional now that a style gate exists; run `./vendor/bin/pint` on them at that point.

---

### Task 15: Final local verification

**Files:** none (verification only)

- [ ] **Step 1: Mirror the CI job locally**

```bash
./vendor/bin/pint --test
php artisan test
```

Expected: the test suite passes. `pint --test` is read-only (it makes no changes) — it may also report style issues in the ~40 unrelated in-progress files still uncommitted in the working tree; that's expected per the note at the end of Task 14, not something to fix here. What matters is that the 8 files this plan touched (listed in Task 13) report clean.

- [ ] **Step 2: Review the full set of commits from this plan**

```bash
git log --oneline -15
```

Confirm it shows, in order: the `UserFactory` fix, the four test-writing commits for `AuthTest`, `AdminMiddlewareTest`, `RateLimitMiddlewareTest`, the three Artisan command commits, the cleanup commit, the optional Pint formatting commit, and the CI fix — with nothing from the unrelated in-progress work mixed in.
