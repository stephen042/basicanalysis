<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WithdrawalValidationTest extends TestCase
{
    protected $usdtMethodId;
    protected $bankMethodId;
    protected $disabledMethodId;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->decimal('account_bal', 15, 2)->default(0);
            $table->string('account_verify')->default('Verified');
            $table->string('sendotpemail')->default('No');
            $table->string('withdrawotp')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('currency')->nullable();
            $table->string('s_currency')->nullable();
            $table->string('enable_kyc')->default('no');
            $table->string('withdrawal_option')->default('manual');
            $table->string('auto_merchant_option')->nullable();
            $table->string('contact_email')->nullable();
            $table->timestamps();
        });

        Schema::create('wdmethods', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('currency', 20)->nullable();
            $table->string('network')->nullable();
            $table->string('methodtype')->nullable();
            $table->string('type')->nullable();
            $table->string('status')->nullable();
            $table->string('minimum')->nullable();
            $table->string('maximum')->nullable();
            $table->string('charges_amount')->nullable();
            $table->string('charges_type')->nullable();
            $table->timestamps();
        });

        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->string('txn_id')->nullable();
            $table->integer('user')->nullable();
            $table->string('amount')->nullable();
            $table->string('columns')->nullable();
            $table->string('to_deduct')->nullable();
            $table->string('status')->nullable();
            $table->string('payment_mode')->nullable();
            $table->text('paydetails')->nullable();
            $table->unsignedBigInteger('method_id')->nullable();
            $table->string('asset', 50)->nullable();
            $table->string('currency', 20)->nullable();
            $table->string('network', 50)->nullable();
            $table->decimal('charges', 15, 2)->nullable();
            $table->string('submission_key', 64)->nullable();
            $table->timestamps();
        });

        DB::table('settings')->insert([
            'id' => 1,
            'currency' => '$',
            's_currency' => 'USD',
            'enable_kyc' => 'no',
            'withdrawal_option' => 'manual',
            'contact_email' => 'admin@example.test',
        ]);

        $this->usdtMethodId = DB::table('wdmethods')->insertGetId([
            'name' => 'USDT',
            'network' => 'TRC20',
            'methodtype' => 'crypto',
            'type' => 'both',
            'status' => 'enabled',
            'minimum' => '100',
            'maximum' => '1000000',
            'charges_amount' => '0',
            'charges_type' => 'percentage',
        ]);

        $this->bankMethodId = DB::table('wdmethods')->insertGetId([
            'name' => 'Bank Transfer',
            'currency' => 'USD',
            'methodtype' => 'currency',
            'type' => 'withdrawal',
            'status' => 'enabled',
            'minimum' => '50',
            'maximum' => '100000',
            'charges_amount' => '5',
            'charges_type' => 'fixed',
        ]);

        $this->disabledMethodId = DB::table('wdmethods')->insertGetId([
            'name' => 'Solana',
            'network' => 'SOL',
            'methodtype' => 'crypto',
            'type' => 'both',
            'status' => 'disabled',
            'minimum' => '100',
            'maximum' => '1000000',
            'charges_amount' => '0',
            'charges_type' => 'percentage',
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('withdrawals');
        Schema::dropIfExists('wdmethods');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    private function makeUser(float $balance = 1000): User
    {
        $user = User::create(['name' => 'Client', 'email' => 'client@example.test']);
        $user->account_bal = $balance;
        $user->save();

        return $user;
    }

    public function test_crypto_withdrawal_deducts_balance_and_records_details(): void
    {
        $this->withoutMiddleware();
        $user = $this->makeUser(1000);

        $this->actingAs($user)
            ->from('/dashboard/withdraw-funds')
            ->post(route('completewithdrawal'), [
                'method_id' => $this->usdtMethodId,
                'amount' => 200,
                'submission_key' => 'crypto-key-1',
                'wallet_address' => 'TW8VUJNp6nNTRzmiP5phHUrZavnnj9M6Do',
            ])
            ->assertRedirect(route('withdrawals'));

        $this->assertSame('800.00', $user->fresh()->account_bal);

        $withdrawal = Withdrawal::where('submission_key', 'crypto-key-1')->firstOrFail();
        $this->assertSame('Pending', $withdrawal->status);
        $this->assertSame('USDT', $withdrawal->payment_mode);
        $this->assertSame('USDT', $withdrawal->asset);
        $this->assertSame('TRC20', $withdrawal->network);
        $this->assertSame((string) $this->usdtMethodId, (string) $withdrawal->method_id);

        $details = json_decode($withdrawal->paydetails, true);
        $this->assertSame('TW8VUJNp6nNTRzmiP5phHUrZavnnj9M6Do', $details['wallet_address']);
        $this->assertSame('TRC20', $details['network']);
    }

    public function test_withdrawal_above_balance_is_rejected(): void
    {
        $this->withoutMiddleware();
        $user = $this->makeUser(500);

        $this->actingAs($user)
            ->from('/dashboard/withdraw-funds')
            ->post(route('completewithdrawal'), [
                'method_id' => $this->usdtMethodId,
                'amount' => 5000,
                'submission_key' => 'crypto-key-2',
                'wallet_address' => 'TW8VUJNp6nNTRzmiP5phHUrZavnnj9M6Do',
            ])
            ->assertSessionHas('message');

        $this->assertSame('500.00', $user->fresh()->account_bal);
        $this->assertSame(0, Withdrawal::count());
    }

    public function test_withdrawal_below_minimum_is_rejected(): void
    {
        $this->withoutMiddleware();
        $user = $this->makeUser(1000);

        $this->actingAs($user)
            ->from('/dashboard/withdraw-funds')
            ->post(route('completewithdrawal'), [
                'method_id' => $this->usdtMethodId,
                'amount' => 50,
                'submission_key' => 'crypto-key-3',
                'wallet_address' => 'TW8VUJNp6nNTRzmiP5phHUrZavnnj9M6Do',
            ])
            ->assertSessionHas('message');

        $this->assertSame('1000.00', $user->fresh()->account_bal);
        $this->assertSame(0, Withdrawal::count());
    }

    public function test_disabled_method_is_rejected(): void
    {
        $this->withoutMiddleware();
        $user = $this->makeUser(1000);

        $this->actingAs($user)
            ->from('/dashboard/withdraw-funds')
            ->post(route('completewithdrawal'), [
                'method_id' => $this->disabledMethodId,
                'amount' => 200,
                'submission_key' => 'crypto-key-4',
                'wallet_address' => 'sol-wallet',
            ])
            ->assertSessionHas('message');

        $this->assertSame('1000.00', $user->fresh()->account_bal);
        $this->assertSame(0, Withdrawal::count());
    }

    public function test_currency_withdrawal_requires_bank_details(): void
    {
        $this->withoutMiddleware();
        $user = $this->makeUser(1000);

        $this->actingAs($user)
            ->from('/dashboard/withdraw-funds')
            ->post(route('completewithdrawal'), [
                'method_id' => $this->bankMethodId,
                'amount' => 200,
                'submission_key' => 'bank-key-1',
                // no bank fields
            ])
            ->assertSessionHas('message');

        $this->assertSame('1000.00', $user->fresh()->account_bal);
        $this->assertSame(0, Withdrawal::count());
    }

    public function test_currency_withdrawal_calculates_fixed_fee_and_records_details(): void
    {
        $this->withoutMiddleware();
        $user = $this->makeUser(1000);

        $this->actingAs($user)
            ->from('/dashboard/withdraw-funds')
            ->post(route('completewithdrawal'), [
                'method_id' => $this->bankMethodId,
                'amount' => 200,
                'submission_key' => 'bank-key-2',
                'bank_name' => 'MyBank',
                'account_name' => 'John Doe',
                'account_number' => '123456789',
                'swift_code' => 'ABC123',
            ])
            ->assertRedirect(route('withdrawals'));

        // Fixed charge = 5, total deducted = 205.
        $this->assertSame('795.00', $user->fresh()->account_bal);

        $withdrawal = Withdrawal::where('submission_key', 'bank-key-2')->firstOrFail();
        $this->assertSame(205.0, (float) $withdrawal->to_deduct);
        $this->assertSame(5.0, (float) $withdrawal->charges);
        $this->assertSame('USD', $withdrawal->currency);
        $this->assertNull($withdrawal->network);

        $details = json_decode($withdrawal->paydetails, true);
        $this->assertSame('MyBank', $details['bank_name']);
        $this->assertSame('John Doe', $details['account_name']);
        $this->assertSame('123456789', $details['account_number']);
    }

    public function test_duplicate_submission_key_is_rejected(): void
    {
        $this->withoutMiddleware();
        $user = $this->makeUser(1000);

        $payload = [
            'method_id' => $this->usdtMethodId,
            'amount' => 200,
            'submission_key' => 'duplicate-key',
            'wallet_address' => 'TW8VUJNp6nNTRzmiP5phHUrZavnnj9M6Do',
        ];

        $this->actingAs($user)
            ->from('/dashboard/withdraw-funds')
            ->post(route('completewithdrawal'), $payload)
            ->assertRedirect(route('withdrawals'));

        $this->assertSame('800.00', $user->fresh()->account_bal);
        $this->assertSame(1, Withdrawal::count());

        // Replaying the same submission must not deduct again.
        $this->actingAs($user)
            ->from('/dashboard/withdraw-funds')
            ->post(route('completewithdrawal'), $payload)
            ->assertSessionHas('message');

        $this->assertSame('800.00', $user->fresh()->account_bal);
        $this->assertSame(1, Withdrawal::count());
    }
}
