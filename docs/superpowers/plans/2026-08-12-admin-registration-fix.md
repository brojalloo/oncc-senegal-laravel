# Admin Self-Registration Fix Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Close a privilege-escalation hole where the public registration endpoint accepts `role=admin`, letting any visitor self-register as an administrator.

**Architecture:** One-line validation change in `AuthController::register()` restricting the allowed `role` values to the three non-privileged roles, verified by a new, self-contained Feature test that doesn't depend on any fixture infrastructure beyond what already exists on `main`.

**Tech Stack:** Laravel 10.48, PHPUnit 10.4, SQLite (`:memory:` in tests, already configured in `phpunit.xml` on `main`).

---

## Important context for the engineer

- Base branch: `main` (not `feature/code-quality-fundamentals`, which has an unrelated, larger, not-yet-merged PR #8). This branch and its test are fully independent of that PR.
- `main` does **not** have `tests/Feature/AuthTest.php`, `database/factories/UserFactory.php`'s schema fix, or the Pint-formatted controllers from PR #8 — don't assume any of that exists. The new test in this plan is self-contained and doesn't use `User::factory()`.
- Run all commands from the Laravel project root.

## File Structure

**Create:**
- `tests/Feature/RegistrationRoleTest.php` — verifies the public registration endpoint rejects `role=admin`

**Modify:**
- `app/Http/Controllers/AuthController.php` — tighten the `role` validation rule in `register()`

---

### Task 1: Restrict registration to non-admin roles

**Files:**
- Create: `tests/Feature/RegistrationRoleTest.php`
- Modify: `app/Http/Controllers/AuthController.php:76`

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_rejects_the_admin_role(): void
    {
        $response = $this->post('/register', [
            'email' => 'wouldbeadmin@test.sn',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nom' => 'Sarr',
            'prenom' => 'Moussa',
            'role' => 'admin',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertSame(0, User::where('email', 'wouldbeadmin@test.sn')->count());
    }

    public function test_registration_still_accepts_the_three_public_roles(): void
    {
        foreach (['chercheur', 'collectivite', 'public'] as $role) {
            $response = $this->post('/register', [
                'email' => "{$role}@test.sn",
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'nom' => 'Sarr',
                'prenom' => 'Moussa',
                'role' => $role,
            ]);

            $response->assertSessionDoesntHaveErrors('role');
            $this->assertSame(1, User::where('email', "{$role}@test.sn")->count());
        }
    }
}
```

- [ ] **Step 2: Run it to verify the first test fails**

Run: `php artisan test --filter=RegistrationRoleTest`
Expected: `test_registration_rejects_the_admin_role` FAILS (the current validation rule still allows `role=admin`, so no session error is raised and a user *is* created). `test_registration_still_accepts_the_three_public_roles` should already PASS (current behavior doesn't reject these roles).

- [ ] **Step 3: Apply the fix**

In `app/Http/Controllers/AuthController.php`, in `register()`:

```php
// Before
'role' => 'required|in:admin,chercheur,collectivite,public',

// After
'role' => 'required|in:chercheur,collectivite,public',
```

- [ ] **Step 4: Run it to verify both tests pass**

Run: `php artisan test --filter=RegistrationRoleTest`
Expected: `2 passed`

- [ ] **Step 5: Run the full suite to confirm no regressions**

Run: `php artisan test`
Expected: all tests pass (this repo's `main` branch currently only has the two default `ExampleTest` stubs plus this new file — expect 4 + 2 = 6 passed total; if the count differs, investigate before proceeding rather than assuming it's fine).

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/AuthController.php tests/Feature/RegistrationRoleTest.php
git commit -m "fix(security): reject admin role at public registration

AuthController::register() accepted role=admin from the public
registration form, letting any visitor self-register as an
administrator. Restrict the validation rule to the three
non-privileged roles; promotion to admin is still available via
php artisan users:promote (see the code-quality-fundamentals branch)."
```
