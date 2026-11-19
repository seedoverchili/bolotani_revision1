<?php

namespace Tests\Feature;

pembeli
use Illuminate\Foundation\Testing\RefreshDatabase;
// use Illuminate\Foundation\Testing\RefreshDatabase;
main
use Tests\TestCase;

class ExampleTest extends TestCase
{
 pembeli
    use RefreshDatabase;
 main

    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
