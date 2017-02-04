<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserControllerTest extends TestCase
{
    use DatabaseMigrations;

    // === GET /users

    public function testListUsers()
    {
        $user = factory(\App\User::class)->create();
        $this->assertGetUsers([$user->id => $user->hkid]);
    }

    public function testListUsersWithNoRecords()
    {
        $this->assertGetUsers([]);
    }

    public function testListUsersWithMulitpleRecords()
    {
        $user1 = factory(\App\User::class)->create();
        $user2 = factory(\App\User::class)->create(['hkid' => 'B6789']);
        $this->assertGetUsers([$user1->id => $user1->hkid, $user2->id => $user2->hkid]);
    }

    // === POST /users

    public function testCreateUser()
    {
        $hkid = 'A1234';
        $response = $this->post('/users', ['hkid' => $hkid]);

        $response->assertStatus(201);
        $user = \App\User::where('hkid', $hkid)->first();
        $response->assertJson(['id' => $user->id]);
    }

    public function testCreateUserWithExistingHkid()
    {
        $hkid = 'A1234';
        $user1 = factory(\App\User::class)->create(['hkid' => $hkid]);
        $this->assertCreateUserError(
            ['hkid' => $hkid],
            400,
            ['message' => 'hkid has already in use']
        );
    }

    public function testCreateUserWithMissingHkid()
    {
        $this->assertCreateUserError([], 400, ['message' => 'Invalid hkid']);
    }

    private function assertGetUsers($expectedResponse)
    {
        $response = $this->get('/users');
        $response->assertStatus(200);
        $response->assertJson($expectedResponse);
    }

    private function assertCreateUserError($requestBody, $code, $expectedResponse)
    {
        $response = $this->post('/users', $requestBody);
        $response->assertStatus($code);
        $response->assertJson($expectedResponse);
    }
}
