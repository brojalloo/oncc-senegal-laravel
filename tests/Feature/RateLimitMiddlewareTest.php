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
