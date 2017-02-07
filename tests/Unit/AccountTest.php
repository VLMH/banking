<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;

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

    public function testIsEnoughTransferQuota()
    {
        $account = factory(Account::class)->make();
        $this->assertTrue($account->isEnoughTransferQuota(100));
    }

    public function testIsEnoughTransferQuotaWithExceedLimit()
    {
        $account = factory(Account::class)->make([
            'transfer_quota' => 0,
            'last_transfered_at' => Carbon::now(),
        ]);
        $this->assertFalse($account->isEnoughTransferQuota(100));
    }

    public function testGetTransferQuotaAttribute()
    {
        $this->assertEquals(10000, factory(Account::class)->make()->transfer_quota);
    }

    public function testGetTransferQuotaAttributeWithTransferedToday()
    {
        $account = factory(Account::class)->make([
            'transfer_quota' => 10000,
            'last_transfered_at' => Carbon::now(),
        ]);
        $this->assertEquals(100, $account->transfer_quota);
    }

    public function testGetTransferQuotaAttributeWithTransferedYesterday()
    {
        $account = factory(Account::class)->make([
            'transfer_quota' => 10000,
            'last_transfered_at' => Carbon::now()->subDay(),
        ]);
        $this->assertEquals(10000, $account->transfer_quota);
    }
}
