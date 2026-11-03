<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ShortUrlAccessTest extends TestCase
{
    use RefreshDatabase;

    private Company $northCompany;
    private Company $southCompany;
    private User $northAdmin;
    private User $northMember;
    private User $southAdmin;
    private User $southMember;
    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->northCompany = Company::create(['name' => 'North Works', 'slug' => 'north-works']);
        $this->southCompany = Company::create(['name' => 'South Labs', 'slug' => 'south-labs']);

        $this->northAdmin = $this->makeUser($this->northCompany, 'admin.north@example.com', 'Admin');
        $this->northMember = $this->makeUser($this->northCompany, 'member.north@example.com', 'Member');
        $this->southAdmin = $this->makeUser($this->southCompany, 'admin.south@example.com', 'Admin');
        $this->southMember = $this->makeUser($this->southCompany, 'member.south@example.com', 'Member');
        $this->superAdmin = $this->makeUser(null, 'root@example.com', 'SuperAdmin');
    }

    public function test_admin_can_create_a_short_url_for_their_company(): void
    {
        $response = $this->actingAs($this->northAdmin)->post(route('short-urls.store'), [
            'original_url' => 'https://example.com/some/path',
        ]);

        $response->assertRedirect(route('dashboard', ['filter' => 'this_month']));
        $this->assertDatabaseHas('short_urls', [
            'company_id' => $this->northCompany->id,
            'user_id' => $this->northAdmin->id,
            'original_url' => 'https://example.com/some/path',
        ]);
    }

    public function test_member_can_create_a_short_url(): void
    {
        $this->actingAs($this->northMember)->post(route('short-urls.store'), [
            'original_url' => 'https://example.com/member-link',
        ])->assertRedirect();

        $this->assertDatabaseHas('short_urls', [
            'company_id' => $this->northCompany->id,
            'user_id' => $this->northMember->id,
            'original_url' => 'https://example.com/member-link',
        ]);
    }

    public function test_super_admin_cannot_create_short_urls(): void
    {
        $this->actingAs($this->superAdmin)->post(route('short-urls.store'), [
            'original_url' => 'https://example.com/not-allowed',
        ])->assertForbidden();

        $this->assertDatabaseMissing('short_urls', ['original_url' => 'https://example.com/not-allowed']);
    }

    public function test_admin_only_sees_urls_for_their_own_company(): void
    {
        $northUrl = $this->makeUrl($this->northCompany, $this->northMember, 'northab', 'https://north.example.com');
        $southUrl = $this->makeUrl($this->southCompany, $this->southMember, 'southab', 'https://south.example.com');

        $response = $this->actingAs($this->northAdmin)->get(route('dashboard'));

        $response->assertOk()->assertSee($northUrl->code)->assertDontSee($southUrl->code);
    }

    public function test_member_only_sees_urls_created_by_themselves(): void
    {
        $ownUrl = $this->makeUrl($this->northCompany, $this->northMember, 'ownlink', 'https://member.example.com/own');
        $otherUrl = $this->makeUrl($this->northCompany, $this->northAdmin, 'otherln', 'https://member.example.com/other');

        $response = $this->actingAs($this->northMember)->get(route('dashboard'));

        $response->assertOk()->assertSee($ownUrl->code)->assertDontSee($otherUrl->code);
    }

    public function test_super_admin_can_see_urls_across_companies(): void
    {
        $northUrl = $this->makeUrl($this->northCompany, $this->northMember, 'north01', 'https://north.example.com/all');
        $southUrl = $this->makeUrl($this->southCompany, $this->southMember, 'south01', 'https://south.example.com/all');

        $this->actingAs($this->superAdmin)->get(route('dashboard'))
            ->assertOk()->assertSee($northUrl->code)->assertSee($southUrl->code);
    }

    public function test_short_url_redirects_publicly_and_increments_clicks(): void
    {
        $url = $this->makeUrl($this->northCompany, $this->northMember, 'pubtest', 'https://destination.example.com/target');

        $this->get('/'.$url->code)
            ->assertRedirect('https://destination.example.com/target');

        $this->assertDatabaseHas('short_urls', ['id' => $url->id, 'clicks' => 1]);
    }

    private function makeUser(?Company $company, string $email, string $role): User
    {
        return User::create([
            'company_id' => $company?->id,
            'name' => ucfirst(strtok($email, '@')),
            'email' => $email,
            'password' => Hash::make('Example-password-123'),
            'role' => $role,
        ]);
    }

    private function makeUrl(Company $company, User $user, string $code, string $originalUrl): ShortUrl
    {
        return ShortUrl::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'code' => $code,
            'original_url' => $originalUrl,
        ]);
    }
}
