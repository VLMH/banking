<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Account;

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

    public function testListAccountsWithoutClosedAccount()
    {
        $user = factory(\App\User::class)->create();
        $account = factory(\App\Account::class)->create(['user_id' => $user->id]);
        $closedAccount = factory(\App\Account::class)
            ->create(['user_id' => $user->id])
            ->delete();
        $response = $this->get("/users/{$user->id}/accounts");

        $response->assertStatus(200);
        $response->assertJson([
            'id'       => $user->id,
            'hkid'     => $user->hkid,
            'accounts' => [$account->id],
        ]);
    }

    public function testListAccountsWithUserNotFound()
    {
        $this->get("/users/999/accounts")->assertStatus(404);
    }

    // === GET /users/{id}/accounts/{accountId}

    public function testShowAccount()
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

    public function testShowAccountWithUserNotFound()
    {
        $this->get("/users/999/accounts/999")->assertStatus(404);
    }

    public function testShowAccountWithAccountNotFound()
    {
        $user = factory(\App\User::class)->create();
        $this->get("/users/{$user->id}/accounts/999")->assertStatus(404);
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
        $this->post("/users/999/accounts")->assertStatus(404);
    }

    // === DELETE /users/{id}/accounts/{accountId}

    public function testDeleteAccount()
    {
        $user = factory(\App\User::class)->create();
        $account = factory(\App\Account::class)->create(['user_id' => $user->id]);
        $response = $this->delete("/users/{$user->id}/accounts/{$account->id}");

        $response->assertStatus(200);
        $this->assertNotNull(Account::onlyTrashed()->where('id', $account->id)->first());
    }

    public function testDeleteAccountWithUserNotFound()
    {
        $this->delete("/users/999/accounts/999")->assertStatus(404);
    }

    public function testDeleteAccountWithAccountNotFound()
    {
        $user = factory(\App\User::class)->create();
        $this->delete("/users/{$user->id}/accounts/999")->assertStatus(404);
    }

    // === POST /users/{id}/accounts/{accountId}/deposit

    public function testAccountDeposit()
    {
        $user = factory(\App\User::class)->create();
        $account = factory(\App\Account::class)->create(['user_id' => $user->id]);
        $response = $this->post("/users/{$user->id}/accounts/{$account->id}/deposit", ['amount' => 100.00]);

        $response->assertStatus(200);
        $this->assertEquals(200.00, $account->fresh()->balance());
    }

    public function testAccountDepositWithCents()
    {
        $user = factory(\App\User::class)->create();
        $account = factory(\App\Account::class)->create(['user_id' => $user->id]);
        $response = $this->post("/users/{$user->id}/accounts/{$account->id}/deposit", ['amount' => 123.45]);

        $response->assertStatus(200);
        $this->assertEquals(223.45, $account->fresh()->balance());
    }

    public function testAccountDepositWithoutAmount()
    {
        $user = factory(\App\User::class)->create();
        $account = factory(\App\Account::class)->create(['user_id' => $user->id]);
        $this->post("/users/{$user->id}/accounts/{$account->id}/deposit")->assertStatus(400);
    }

    public function testAccountDepositWithAmountIsNaN()
    {
        $user = factory(\App\User::class)->create();
        $account = factory(\App\Account::class)->create(['user_id' => $user->id]);
        $this->post("/users/{$user->id}/accounts/{$account->id}/deposit", ['amount' => 'abc'])->assertStatus(400);
    }

    public function testAccountDepositWithAmountIsLessThanZero()
    {
        $user = factory(\App\User::class)->create();
        $account = factory(\App\Account::class)->create(['user_id' => $user->id]);
        $this->post("/users/{$user->id}/accounts/{$account->id}/deposit", ['amount' => -1.23])->assertStatus(400);
    }

    public function testAccountDepositWithUserNotFound()
    {
        $this->post("/users/999/accounts/999/deposit", ['amount' => 100.00])->assertStatus(404);
    }

    public function testAccountDepositWithAccountNotFound()
    {
        $user = factory(\App\User::class)->create();
        $this->post("/users/{$user->id}/accounts/999/deposit", ['amount' => 100.00])->assertStatus(404);
    }
}
