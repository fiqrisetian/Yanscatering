<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_homepage_redirects_to_the_admin_dashboard(): void
    {
        $this->seed(DatabaseSeeder::class);
        $response = $this->get('/');

        $response->assertRedirect('/admin/dashboard');
    }
}
