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

    public function testCanWithdraw()
    {
        $account = factory(Account::class)->make();
        $this->assertTrue($account->canWithdraw(50));
    }

    public function testCanWithdrawWithAmountExceedBalance()
    {
        $account = factory(Account::class)->make();
        $this->assertFalse($account->canWithdraw(200));
    }

    public function testWithdraw()
    {
        $account = factory(Account::class)->make();
        $this->assertEquals(50, $account->withdraw(50)->balance);
    }

    public function testWithdrawWithAmountExceedBalance()
    {
        $this->expectException(\Exception::class);
        factory(Account::class)->make()->withdraw(200);
    }

    public function testIsSameOwner()
    {
        $account = factory(Account::class)->make();
        $anotherAccount = factory(Account::class)->make();
        $this->assertTrue($account->isSameOwner($anotherAccount));
    }

    public function testIsSameOwnerWithDiffOwner()
    {
        $account = factory(Account::class)->make();
        $anotherAccount = factory(Account::class)->make(['user_id' => 2]);
        $this->assertFalse($account->isSameOwner($anotherAccount));
    }
}
