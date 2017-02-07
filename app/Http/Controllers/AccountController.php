<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

use App\User;
use App\Account;
use App\ApprovalService;

class AccountController extends Controller
{

    protected $approvalService;

    public function __construct()
    {
        $this->approvalService = new ApprovalService();
    }

    /**
     * GET /users/{user}/accounts
     * List out accounts of a user
     */
    public function index(Request $req, User $user)
    {
        // find user with active accounts
        $accounts = $user->accounts()->get();

        // response
        $resp = [
            'id'       => $user->id,
            'hkid'     => $user->hkid,
            'accounts' => $accounts->pluck('id'),
        ];
        return response($resp, 200);
    }

    /**
     * GET /users/{userId}/accounts/{accountId}
     * Retrieve an account with balance
     */
    public function show(Request $req, User $user, Account $account)
    {
        // response
        return response([
            'id' => $account->id,
            'balance' => '$' . number_format($account->balance, 2),
            'transfer_quota' => '$' . number_format($account->transfer_quota, 2),
            'last_transfered_at' => $account->last_transfered_at ? $account->last_transfered_at->toDateTimeString() : null,
        ], 200);
    }

    /**
     * POST /users/{id}/accounts
     * Create account for a user
     */
    public function create(Request $req, User $user)
    {
        // create account
        $account = $user->accounts()->create([]);

        // response
        return response(['id' => $account->id], 201);
    }

    /**
     * DELETE /users/{userId}/accounts/{accountId}
     * Close an account
     */
    public function destroy(Request $req, User $user, Account $account)
    {
        $account->delete();

        // response
        return response(null, 200);
    }

    /**
     * POST /users/{userId}/accounts/{accountId}/deposit
     * Account deposit
     */
    public function deposit(Request $req, User $user, Account $account)
    {
        // validation
        $validator = Validator::make($req->all(), ['amount' => 'required|numeric|min:0.01']);
        if ($validator->fails()) {
            abort(400);
        }

        $account->deposit($req->amount)->save();

        return response(null, 200);
    }

    /**
     * POST /users/{userId}/accounts/{accountId}/withdraw
     * Account withdraw
     */
    public function withdraw(Request $req, User $user, Account $account)
    {
        // validation
        $validator = Validator::make($req->all(), ['amount' => 'required|numeric|min:0.01']);
        if ($validator->fails()) {
            abort(400);
        }

        if (!$account->canWithdraw($req->amount)) {
            return response()->json(['message' => 'Not enough balance'], 400);
        }

        $account->withdraw($req->amount)->save();

        return response(null, 200);
    }

    /**
     * POST /users/{userId}/accounts/{accountId}/transfer
     * Account transfer amount to another account
     */
    public function transfer(Request $req, User $user, Account $account)
    {
        // validation
        $validator = Validator::make($req->all(), [
            'targetAccountId' => 'required|integer',
            'amount' => 'required|numeric|min:0.01'
        ]);
        if ($validator->fails()) {
            abort(400);
        }

        if (!$account->isEnoughTransferQuota($req->amount)) {
            return response()->json(['message' => 'Exceed daily transfer limit'], 400);
        }

        $targetAccount = $this->findAccount($req->targetAccountId);
        if ($account->id == $targetAccount->id) {
            return response()->json(['message' => 'Cannot transfer to same account'], 400);
        }

        $serviceCharge = $this->getTransferServiceCharge($account, $targetAccount);

        if (!$account->canWithdraw($req->amount + $serviceCharge)) {
            return response()->json(['message' => 'Not enough balance'], 400);
        }

        if (!$this->approvalService->canTransfer()) {
            return response()->json(['message' => 'Rejected'], 400);
        }

        $this->doTransfer($account, $targetAccount, $req->amount, $serviceCharge);

        return response(null, 200);
    }

    private function findAccount($accountId)
    {
        if (!$account = Account::find($accountId)) {
            abort(400);
        }

        return $account;
    }

    private function doTransfer(Account $transferer, Account $transferee, $amount, $serviceCharge)
    {
        DB::transaction(function() use ($transferer, $transferee, $amount, $serviceCharge) {
            $transferer->withdrawByTransfer($amount, $serviceCharge)->save();
            $transferee->deposit($amount)->save();
        });
    }

    private function getTransferServiceCharge(Account $transferer, Account $transferee)
    {
        return $transferer->isSameOwner($transferee) ? 0 : Account::TRANSFER_SERVICE_FEE;
    }
}
