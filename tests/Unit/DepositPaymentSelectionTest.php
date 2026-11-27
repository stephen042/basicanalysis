<?php

namespace Tests\Unit;

use App\Http\Controllers\User\ViewsController;
use App\Http\Controllers\Admin\ManageDepositController;
use App\Models\Deposit;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DepositPaymentSelectionTest extends TestCase
{
    private $fiatMethodId;
    private $trc20MethodId;
    private $erc20MethodId;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('currency')->default('$');
            $table->string('cstatus')->nullable();
            $table->decimal('account_bal', 15, 2)->default(0);
            $table->decimal('roi', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('contact_email')->nullable();
            $table->string('credit_card_provider')->nullable();
            $table->string('s_s_k')->nullable();
            $table->string('s_currency')->nullable();
            $table->string('enable_kyc_registration')->nullable();
            $table->decimal('deposit_bonus', 8, 2)->nullable();
            $table->decimal('referral_commission', 8, 2)->nullable();
        });

        Schema::create('settings_conts', function (Blueprint $table) {
            $table->id();
            $table->decimal('minamt', 15, 2)->default(1);
            $table->string('purchase_code')->nullable();
        });

        Schema::create('wdmethods', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('methodtype')->nullable();
            $table->string('type')->nullable();
            $table->string('status')->nullable();
            $table->string('network')->nullable();
            $table->text('wallet_address')->nullable();
            $table->text('barcode')->nullable();
            $table->text('img_url')->nullable();
            $table->string('bankname')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('swift_code')->nullable();
            $table->text('duration')->nullable();
            $table->timestamps();
        });

        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->string('txn_id')->nullable();
            $table->unsignedBigInteger('user')->nullable();
            $table->string('amount')->nullable();
            $table->string('payment_mode')->nullable();
            $table->unsignedBigInteger('payment_method_id')->nullable();
            $table->string('payment_network')->nullable();
            $table->string('plan')->nullable();
            $table->string('status')->nullable();
            $table->string('proof')->nullable();
            $table->timestamps();
        });
        Schema::create('compound_investment_contributions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('compound_investment_id');
            $table->unsignedBigInteger('deposit_id')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        DB::table('settings')->insert([
            'id' => 1,
            'contact_email' => 'admin@example.test',
            'enable_kyc_registration' => 'no',
        ]);
        DB::table('settings_conts')->insert(['id' => 1, 'minamt' => 10]);
        $this->fiatMethodId = DB::table('wdmethods')->insertGetId([
            'name' => 'Bank Transfer',
            'methodtype' => 'currency',
            'type' => 'deposit',
            'status' => 'enabled',
            'bankname' => 'Example Bank',
            'account_name' => 'Example Account',
            'account_number' => '123456',
            'swift_code' => 'EXAMPLE',
            'duration' => 'Use your reference code.',
        ]);
        $this->trc20MethodId = DB::table('wdmethods')->insertGetId([
            'name' => 'USDT',
            'methodtype' => 'crypto',
            'type' => 'both',
            'status' => 'enabled',
            'network' => 'TRC20',
            'wallet_address' => 'T-trc20-address',
            'img_url' => 'https://assets.example.test/usdt.png',
        ]);
        $this->erc20MethodId = DB::table('wdmethods')->insertGetId([
            'name' => 'USDT',
            'methodtype' => 'crypto',
            'type' => 'deposit',
            'status' => 'enabled',
            'network' => 'ERC20',
            'wallet_address' => '0x-erc20-address',
            'img_url' => 'https://assets.example.test/usdt.png',
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('compound_investment_contributions');
        Schema::dropIfExists('deposits');
        Schema::dropIfExists('wdmethods');
        Schema::dropIfExists('settings_conts');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_deposit_page_only_exposes_configured_crypto_assets_and_networks(): void
    {
        DB::table('wdmethods')->insert([
            'name' => 'Bitcoin',
            'methodtype' => 'crypto',
            'type' => 'deposit',
            'status' => 'enabled',
            'network' => 'Bitcoin',
            'wallet_address' => 'bitcoin-address',
            'img_url' => 'https://assets.example.test/bitcoin.png?1696501400',
        ]);
        $user = User::create(['name' => 'Depositor', 'email' => 'depositor@example.test']);
        $this->actingAs($user);

        $view = (new ViewsController())->deposits();
        $data = $view->getData();

        $this->assertSame(['Bitcoin', 'USDT'], $data['cryptoAssets']->pluck('name')->all());
        $this->assertSame('BTC', $data['cryptoAssets']->first()['ticker']);
        $this->assertSame('https://assets.example.test/bitcoin.png?1696501400', $data['cryptoAssets']->first()['image']);
        $usdtAsset = $data['cryptoAssets']->firstWhere('name', 'USDT');
        $this->assertSame('USDT', $usdtAsset['ticker']);
        $this->assertSame('https://assets.example.test/usdt.png', $usdtAsset['image']);
        $this->assertSame(['ERC20', 'TRC20'], $usdtAsset['methods']->pluck('network')->all());
        $this->assertArrayNotHasKey('fiatMethods', $data);
        $view->with('errors', new \Illuminate\Support\ViewErrorBag());
        $html = str_replace('\\/', '/', $view->render());
        $this->assertStringContainsString('https://assets.example.test/bitcoin.png', $html);
        $this->assertStringContainsString('https://assets.example.test/usdt.png', $html);
        $this->assertStringContainsString('BTC', $html);
        $depositPage = File::get(resource_path('views/user/deposits.blade.php'));
        $this->assertStringContainsString('Select a cryptocurrency', $depositPage);
        $this->assertStringContainsString('asset.imageFailed = true', $depositPage);
        $this->assertStringContainsString('fas fa-coins', $depositPage);
        $this->assertStringNotContainsString('Currency / Fiat', $depositPage);
        $this->assertSame('T-trc20-address', $data['depositMethodDetails'][(string) $this->trc20MethodId]['address']);
        $this->assertSame('0x-erc20-address', $data['depositMethodDetails'][(string) $this->erc20MethodId]['address']);
    }

    public function test_selected_crypto_method_and_network_are_validated_and_saved_exactly(): void
    {
        Storage::fake('public');
        Mail::fake();
        $user = User::create([
            'name' => 'Depositor',
            'email' => 'depositor@example.test',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user, 'sanctum')
            ->post(route('newdeposit'), [
                'amount' => 50,
                'payment_method_id' => $this->erc20MethodId,
            ])
            ->assertRedirect(route('payment'))
            ->assertSessionHas('payment_method_id', $this->erc20MethodId)
            ->assertSessionHas('payment_network', 'ERC20');

        $this->actingAs($user, 'sanctum')
            ->post(route('savedeposit'), [
                'amount' => 50,
                'payment_method_id' => $this->erc20MethodId,
                'proof' => UploadedFile::fake()->image('receipt.png'),
            ])
            ->assertRedirect(route('deposits'));

        $deposit = Deposit::firstOrFail();
        $this->assertSame('USDT', $deposit->payment_mode);
        $this->assertSame((int) $this->erc20MethodId, (int) $deposit->payment_method_id);
        $this->assertSame('ERC20', $deposit->payment_network);
        $this->assertSame('0x-erc20-address', $deposit->paymentMethod->wallet_address);

        $adminDeposits = new class(app(\App\Services\NotificationService::class)) extends ManageDepositController {
            public function callServer($action, $url, $data = [])
            {
                return null;
            }
        };
        Mail::fake();
        $adminDeposits->pdeposit($deposit->id);

        $this->assertSame('Processed', $deposit->fresh()->status);
        $this->assertSame('50.00', $user->fresh()->account_bal);
        $this->assertSame('ERC20', $deposit->fresh()->payment_network);
    }

    public function test_disabled_or_non_deposit_methods_cannot_start_a_deposit(): void
    {
        $user = User::create([
            'name' => 'Depositor',
            'email' => 'depositor@example.test',
            'email_verified_at' => now(),
        ]);
        $withdrawalOnlyId = DB::table('wdmethods')->insertGetId([
            'name' => 'Withdrawal only',
            'methodtype' => 'crypto',
            'type' => 'withdrawal',
            'status' => 'enabled',
            'network' => 'Testnet',
        ]);

        $this->actingAs($user, 'sanctum')
            ->post(route('newdeposit'), [
                'amount' => 50,
                'payment_method_id' => $withdrawalOnlyId,
            ])
            ->assertNotFound();

        $this->actingAs($user, 'sanctum')
            ->post(route('newdeposit'), [
                'amount' => 50,
                'payment_method_id' => $this->fiatMethodId,
            ])
            ->assertNotFound();

        $this->assertSame(0, Deposit::count());
        $this->assertNull(session('payment_method_id'));
    }

    public function test_network_change_invalidates_a_stale_deposit_selection(): void
    {
        $user = User::create([
            'name' => 'Depositor',
            'email' => 'depositor@example.test',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user, 'sanctum')
            ->post(route('newdeposit'), [
                'amount' => 50,
                'payment_method_id' => $this->erc20MethodId,
            ])
            ->assertRedirect(route('payment'));

        DB::table('wdmethods')->where('id', $this->erc20MethodId)->update(['network' => 'TRC20']);

        $this->actingAs($user, 'sanctum')
            ->post(route('savedeposit'), [
                'amount' => 50,
                'payment_method_id' => $this->erc20MethodId,
                'proof' => UploadedFile::fake()->image('receipt.png'),
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(0, Deposit::count());
    }
}
