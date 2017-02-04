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
        $user = factory(\App\User::class)->create();
        $this->assertGetUsers([$user->id => $user->hkid]);
    }

    public function testListUsersWithNoRecords()
    {
        $this->assertGetUsers([]);
    }

    public function testCreateUser()
    {
        $hkid = 'A1234';
        $response = $this->post('/users', ['hkid' => $hkid]);

        $response->assertStatus(201);
        $this->assertEquals(1, \App\User::where('hkid', $hkid)->count());
    }

    private function assertGetUsers($expectedResponse)
    {
        $response = $this->get('/users');
        $response->assertStatus(200);
        $response->assertJson($expectedResponse);
    }
}
