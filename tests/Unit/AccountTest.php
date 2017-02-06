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
        $this->assertEquals(100, factory(Account::class)->make()->balance);
    }

    public function testDeposit()
    {
        $account = factory(Account::class)->make();
        $this->assertEquals(223.45, $account->deposit(123.45)->balance);
    }
}
