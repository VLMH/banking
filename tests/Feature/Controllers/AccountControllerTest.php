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
            'balance' => '$' . number_format($account->balance, 2),
            'transfer_quota' => '$' . number_format($account->transfer_quota, 2),
            'last_transfered_at' => $account->last_transfered_at ? $account->last_transfered_at->toDateTimeString() : null,
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
        $this->assertEquals(200.00, $account->fresh()->balance);
    }

    public function testAccountDepositWithCents()
    {
        $user = factory(\App\User::class)->create();
        $account = factory(\App\Account::class)->create(['user_id' => $user->id]);
        $response = $this->post("/users/{$user->id}/accounts/{$account->id}/deposit", ['amount' => 123.45]);

        $response->assertStatus(200);
        $this->assertEquals(223.45, $account->fresh()->balance);
    }

    public function testInvalidAmount()
    {
        $user = factory(\App\User::class)->create();
        $account = factory(\App\Account::class)->create(['user_id' => $user->id]);

        $basePath = "/users/{$user->id}/accounts/{$account->id}";
        $actions = ["deposit", "withdraw"];
        $invalidAmountParams = [
            [],
            ['amount' => 'abc'],
            ['amount' => -1.23],
        ];
        foreach ($actions as $action) {
            foreach ($invalidAmountParams as $param) {
                $this->post("{$basePath}/{$action}", $param)->assertStatus(400);
            }
        }
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

    // === POST /users/{id}/accounts/{accountId}/withdraw

    public function testAccountWithdraw()
    {
        $user = factory(\App\User::class)->create();
        $account = factory(\App\Account::class)->create(['user_id' => $user->id]);
        $response = $this->post("/users/{$user->id}/accounts/{$account->id}/withdraw", ['amount' => 50.00]);

        $response->assertStatus(200);
        $this->assertEquals(50.00, $account->fresh()->balance);
    }

    public function testAccountWithdrawWithNotEnoughBalance()
    {
        $user = factory(\App\User::class)->create();
        $account = factory(\App\Account::class)->create(['user_id' => $user->id]);
        $response = $this->post("/users/{$user->id}/accounts/{$account->id}/withdraw", ['amount' => 200.00]);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'Not enough balance']);
    }

    public function testAccountWithdrawWithUserNotFound()
    {
        $this->post("/users/999/accounts/999/withdraw", ['amount' => 100.00])->assertStatus(404);
    }

    public function testAccountWithdrawWithAccountNotFound()
    {
        $user = factory(\App\User::class)->create();
        $this->post("/users/{$user->id}/accounts/999/withdraw", ['amount' => 100.00])->assertStatus(404);
    }

    // === POST /users/{id}/accounts/{accountId}/transfer

    public function testAccountTransferToAccountOfSameOwner()
    {
        $user = factory(\App\User::class)->create();
        $account = factory(\App\Account::class)->create(['user_id' => $user->id]);
        $targetAccount = factory(\App\Account::class)->create(['user_id' => $user->id]);

        $response = $this->post("/users/{$user->id}/accounts/{$account->id}/transfer", ['targetAccountId' => $targetAccount->id, 'amount' => 50.00]);

        $response->assertStatus(200);
        $account = $account->fresh();
        $this->assertEquals(50.00, $account->balance);
        $this->assertEquals(9950.00, $account->transfer_quota);
        $this->assertEquals(150.00, $targetAccount->fresh()->balance);
    }

    public function testAccountTransferToAccountOfDiffOwner()
    {
        $user = factory(\App\User::class)->create();
        $account = factory(\App\Account::class)->create(['user_id' => $user->id, 'balance' => 50000]);
        $anotherUser = factory(\App\User::class)->create();
        $targetAccount = factory(\App\Account::class)->create(['user_id' => $anotherUser->id]);

        $response = $this->post("/users/{$user->id}/accounts/{$account->id}/transfer", ['targetAccountId' => $targetAccount->id, 'amount' => 50.00]);

        $response->assertStatus(200);
        $this->assertEquals(350.00, $account->fresh()->balance);
        $this->assertEquals(150.00, $targetAccount->fresh()->balance);
    }

    public function testAccountTransferWithExceedLimit()
    {
        $user = factory(\App\User::class)->create();
        $account = factory(\App\Account::class)->create([
            'user_id' => $user->id,
            'transfer_quota' => 0,
            'last_transfered_at' => \Carbon\Carbon::now(),
        ]);

        $response = $this->post("/users/{$user->id}/accounts/{$account->id}/transfer", ['targetAccountId' => 1, 'amount' => 50.00]);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'Exceed daily transfer limit']);
    }

    public function testAccountTransferToSameAccount()
    {
        $user = factory(\App\User::class)->create();
        $account = factory(\App\Account::class)->create(['user_id' => $user->id]);

        $response = $this->post("/users/{$user->id}/accounts/{$account->id}/transfer", ['targetAccountId' => $account->id, 'amount' => 50.00]);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'Cannot transfer to same account']);
    }

    public function testAccountTransferWithUserNotFound()
    {
        $this->post("/users/999/accounts/999/transfer")->assertStatus(404);
    }

    public function testAccountTransferWithAccountNotFound()
    {
        $user = factory(\App\User::class)->create();
        $this->post("/users/{$user->id}/accounts/999/transfer")->assertStatus(404);
    }
}
