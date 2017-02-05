<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AccountControllerTest extends TestCase
{
    use DatabaseMigrations;

    // === GET /users/{id}/accounts

    public function testListAccounts()
    {
        $user = factory(\App\User::class)->create();
        $account = factory(\App\Account::class)->create(['user_id' => $user->id]);
        $response = $this->get("/users/{$user->id}/accounts");

        $response->assertStatus(200);
        $response->assertJson([
            'id'       => $user->id,
            'hkid'     => $user->hkid,
            'accounts' => [$account->id],
        ]);
    }

    public function testListAccountsWithNoRecords()
    {
        $user = factory(\App\User::class)->create();
        $response = $this->get("/users/{$user->id}/accounts");

        $response->assertStatus(200);
        $response->assertJson([
            'id'       => $user->id,
            'hkid'     => $user->hkid,
            'accounts' => [],
        ]);
    }

    public function testListAccountsWithMultipleRecords()
    {
        $user = factory(\App\User::class)->create();
        $accounts = factory(\App\Account::class, 3)->create(['user_id' => $user->id]);
        $response = $this->get("/users/{$user->id}/accounts");

        $response->assertStatus(200);
        $response->assertJson([
            'id'       => $user->id,
            'hkid'     => $user->hkid,
            'accounts' => $accounts->pluck('id')->toArray(),
        ]);
    }

    // === POST /users/{id}/accounts

    public function testCreateAccount()
    {
        $user = factory(\App\User::class)->create();
        $response = $this->post("/users/{$user->id}/accounts");

        $response->assertStatus(201);
        $accounts = $user->accounts();
        $this->assertEquals(1, $accounts->count());
        $response->assertJson(['id' => $accounts->first()->id]);
    }
}
