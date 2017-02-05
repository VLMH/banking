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

    public function testListAccountsWithUserNotFound()
    {
        $this->assertUserNotFound($this->get("/users/999/accounts"));
    }

    // === GET /users/{id}/accounts/{accountId}

    public function testGetAccount()
    {
        $user = factory(\App\User::class)->create();
        $account = factory(\App\Account::class)->create(['user_id' => $user->id]);
        $response = $this->get("/users/{$user->id}/accounts/{$account->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $account->id,
            'balance' => '$' . number_format($account->balance(), 2),
        ]);
    }

    public function testGetAccountWithUserNotFound()
    {
        $this->assertUserNotFound($this->get("/users/999/accounts/999"));
    }

    public function testGetAccountWithAccountNotFound()
    {
        $user = factory(\App\User::class)->create();
        $this->assertAccountNotFound($this->get("/users/{$user->id}/accounts/999"));
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

    public function testCreateAccountWithUserNotFound()
    {
        $this->assertUserNotFound($this->post("/users/999/accounts"));
    }

    // === DELETE /users/{id}/accounts/{accountId}

    public function testDeleteAccount()
    {
        $user = factory(\App\User::class)->create();
        $account = factory(\App\Account::class)->create(['user_id' => $user->id]);
        $response = $this->delete("/users/{$user->id}/accounts/{$account->id}");

        $response->assertStatus(200);
        $this->assertNotNull(Account::find($account->id)->deleted_at);
    }

    private function assertUserNotFound($response)
    {
        $this->assertNotFoundResponse($response, 'User not found');
    }

    private function assertAccountNotFound($response)
    {
        $this->assertNotFoundResponse($response, 'Account not found');
    }

    private function assertNotFoundResponse($response, $message)
    {
        $response->assertStatus(404);
        $response->assertJson(['message' => $message]);
    }
}
