<?php

namespace Tests\Unit;

use App\Models\Admin;
use App\Models\Kyc;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KycUploadFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->unsignedBigInteger('kyc_id')->nullable();
            $table->string('account_verify')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('firstName')->nullable();
            $table->string('enable_2fa')->nullable();
            $table->string('pass_2fa')->nullable();
            $table->timestamp('token_2fa_expiry')->nullable();
            $table->timestamps();
        });

        Schema::create('kycs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('dob')->nullable();
            $table->string('social_media')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('document_type')->nullable();
            $table->string('frontimg')->nullable();
            $table->string('backimg')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('contact_email')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->string('action_url')->nullable();
            $table->string('icon')->nullable();
            $table->string('priority')->nullable();
            $table->timestamps();
        });

        DB::table('settings')->insert([
            'id' => 1,
            'contact_email' => 'admin@example.test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Storage::fake('local');
        Storage::fake('public');
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('kycs');
        Schema::dropIfExists('admins');
        Schema::dropIfExists('users');
        parent::tearDown();
    }

    public function test_real_kyc_upload_is_stored_privately_and_viewable_by_admin_only(): void
    {
        $user = User::create([
            'name' => 'Kyc User',
            'email' => 'kyc-user@example.test',
            'password' => bcrypt('secret'),
            'email_verified_at' => now(),
        ]);

        $front = UploadedFile::fake()->image('front.png', 320, 200);
        $back = UploadedFile::fake()->image('back.png', 320, 200);

        $this->actingAs($user, 'sanctum')
            ->post(route('kycsubmit'), [
                'first_name' => 'Kyc',
                'last_name' => 'User',
                'email' => 'kyc-user@example.test',
                'phone_number' => '+10000000000',
                'dob' => '1990-01-01',
                'social_media' => '@kycuser',
                'address' => '1 Example Street',
                'city' => 'Example City',
                'state' => 'Example State',
                'country' => 'Example Country',
                'document_type' => 'National ID',
                'frontimg' => $front,
                'backimg' => $back,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $kyc = Kyc::firstOrFail();

        $this->assertSame($user->id, (int) $kyc->user_id);
        $this->assertStringStartsWith('kyc-documents/' . $user->id . '/', $kyc->frontimg);
        $this->assertStringStartsWith('kyc-documents/' . $user->id . '/', $kyc->backimg);
        $this->assertTrue(Storage::disk('local')->exists($kyc->frontimg));
        $this->assertTrue(Storage::disk('local')->exists($kyc->backimg));
        $this->assertSame('image/png', mime_content_type(Storage::disk('local')->path($kyc->frontimg)));
        $this->assertSame('Under review', $kyc->status);
        $this->assertSame((string) $kyc->id, (string) $user->fresh()->kyc_id);
        $this->assertSame('Under review', $user->fresh()->account_verify);

        // The file is not reachable through an unrestricted public URL.
        $this->assertFalse(Storage::disk('public')->exists($kyc->frontimg));

        $adminId = DB::table('admins')->insertGetId([
            'email' => 'admin@example.test',
            'password' => bcrypt('secret'),
            'firstName' => 'Admin',
            'enable_2fa' => 'disabled',
            'pass_2fa' => 'true',
        ]);
        $admin = Admin::findOrFail($adminId);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.kyc.image', [$kyc->id, 'front']))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.kyc.image', [$kyc->id, 'back']))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');

        Auth::guard('admin')->logout();
        $this->get(route('admin.kyc.image', [$kyc->id, 'front']))
            ->assertRedirect();
    }
}
