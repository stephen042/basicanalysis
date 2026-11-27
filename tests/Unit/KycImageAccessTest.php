<?php

namespace Tests\Unit;

use App\Models\Admin;
use App\Models\Kyc;
use App\Http\Requests\KycApplicationRequest;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class KycImageAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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
            $table->string('frontimg')->nullable();
            $table->string('backimg')->nullable();
            $table->timestamps();
        });

        Storage::fake('local');
        Storage::fake('public');
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('kycs');
        Schema::dropIfExists('admins');
        parent::tearDown();
    }

    public function test_existing_public_kyc_image_is_served_only_through_admin_route(): void
    {
        $png = UploadedFile::fake()->image('kyc-image.png')->getContent();
        Storage::disk('public')->put('uploads/legacy-front.png', $png);
        Storage::disk('public')->put('uploads/legacy-back.png', $png);
        $kycId = DB::table('kycs')->insertGetId([
            'user_id' => 1,
            'frontimg' => 'uploads/legacy-front.png',
            'backimg' => 'uploads/legacy-back.png',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $kyc = Kyc::findOrFail($kycId);

        $this->get('/admin/dashboard/kyc-application/' . $kyc->id . '/image/front')
            ->assertRedirect(route('adminloginform'));

        $adminId = DB::table('admins')->insertGetId([
            'email' => 'admin@example.test',
            'password' => bcrypt('secret'),
            'firstName' => 'Admin',
            'enable_2fa' => 'disabled',
            'pass_2fa' => 'true',
        ]);
        $admin = Admin::findOrFail($adminId);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/dashboard/kyc-application/' . $kyc->id . '/image/front')
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('Pragma', 'no-cache');
        $cacheControl = $response->baseResponse->headers->get('Cache-Control');
        $this->assertStringContainsString('private', $cacheControl);
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringNotContainsString('public', $cacheControl);

        $this->actingAs($admin, 'admin')
            ->get('/admin/dashboard/kyc-application/' . $kyc->id . '/image/back')
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');
    }

    public function test_new_private_kyc_image_is_served_through_admin_route(): void
    {
        $png = UploadedFile::fake()->image('kyc-image.png')->getContent();
        Storage::disk('local')->put('kyc-documents/7/private-front.png', $png);
        $kyc = $this->makeKyc([
            'user_id' => 7,
            'frontimg' => 'kyc-documents/7/private-front.png',
        ]);

        $admin = $this->makeAdmin();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.kyc.image', [$kyc->id, 'front']))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');
    }

    public function test_missing_kyc_file_returns_404_without_a_broken_image_url(): void
    {
        $kyc = $this->makeKyc([
            'user_id' => 8,
            'frontimg' => 'uploads/does-not-exist.png',
        ]);

        $this->assertFalse($kyc->hasDocument('front'));
        $this->assertNull($kyc->documentUrl('front'));

        $admin = $this->makeAdmin();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.kyc.image', [$kyc->id, 'front']))
            ->assertNotFound();
    }

    public function test_legacy_storage_app_public_url_is_resolved(): void
    {
        $png = UploadedFile::fake()->image('kyc-image.png')->getContent();
        Storage::disk('public')->put('uploads/legacy-url-front.png', $png);

        $kyc = $this->makeKyc([
            'user_id' => 9,
            'frontimg' => 'http://basicsanalysis.test/storage/app/public/uploads/legacy-url-front.png',
        ]);

        $this->assertTrue($kyc->hasDocument('front'));

        $admin = $this->makeAdmin();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.kyc.image', [$kyc->id, 'front']))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');
    }

    private function makeKyc(array $attributes): Kyc
    {
        $id = DB::table('kycs')->insertGetId(array_merge([
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes));

        return Kyc::findOrFail($id);
    }

    private function makeAdmin(): Admin
    {
        $adminId = DB::table('admins')->insertGetId([
            'email' => 'admin@example.test',
            'password' => bcrypt('secret'),
            'firstName' => 'Admin',
            'enable_2fa' => 'disabled',
            'pass_2fa' => 'true',
        ]);

        return Admin::findOrFail($adminId);
    }

    public function test_passport_can_be_front_only_but_identity_cards_require_a_back_image(): void
    {
        $backImageRules = (new KycApplicationRequest())->rules()['backimg'];

        $passport = Validator::make(
            ['document_type' => "Int'l Passport"],
            ['document_type' => 'required', 'backimg' => $backImageRules]
        );
        $this->assertTrue($passport->passes());

        $nationalId = Validator::make(
            ['document_type' => 'National ID'],
            ['document_type' => 'required', 'backimg' => $backImageRules]
        );
        $this->assertFalse($nationalId->passes());
    }
}
