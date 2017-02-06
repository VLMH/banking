<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Account;

class AccountTest extends TestCase
{
    public function testBalance()
    {
        $account = factory(Account::class)->make();
        $this->assertEquals(100, $account->balance());
    }

    public function testBalanceWithCents()
    {
        $account = factory(Account::class)->make(['balance' => 1234567]);
        $this->assertEquals(12345.67, $account->balance());
    }

    public function testDeposit()
    {
        $account = factory(Account::class)->make();
        $this->assertEquals(223.45, $account->deposit(123.45)->balance());
    }
}
