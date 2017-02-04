<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function testListUsers()
    {
        $response = $this->get('/users');

        $response->assertStatus(200);
        $response->assertJson([]);
    }

    // TODO: public function testListUsersWithRecords
    
    public function testCreateUser()
    {
        $response = $this->post('/users', ['hkid' => 'A1234']);

        $response->assertStatus(201);
    }
}
