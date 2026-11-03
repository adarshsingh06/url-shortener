<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InvitationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_a_company_admin_invitation(): void
    {
        Mail::fake();
        $superAdmin = $this->makeUser(null, 'root@example.com', 'SuperAdmin');

        $response = $this->actingAs($superAdmin)->post(route('invitations.company'), [
            'company_name' => 'Example Company',
            'email' => 'admin@example.com',
        ]);

        $response->assertRedirect(route('dashboard'))
            ->assertSessionHas('invite_link');
        $this->assertDatabaseHas('companies', ['name' => 'Example Company']);
        $this->assertDatabaseHas('invitations', ['email' => 'admin@example.com', 'role' => 'Admin']);
    }

    public function test_company_admin_can_invite_a_member_but_member_cannot_invite(): void
    {
        Mail::fake();
        $company = Company::create(['name' => 'Example Company', 'slug' => 'example-company']);
        $admin = $this->makeUser($company, 'admin@example.com', 'Admin');
        $member = $this->makeUser($company, 'member@example.com', 'Member');

        $this->actingAs($admin)->post(route('invitations.team'), [
            'email' => 'new.member@example.com',
            'role' => 'Member',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('invitations', [
            'company_id' => $company->id,
            'email' => 'new.member@example.com',
            'role' => 'Member',
        ]);

        $this->actingAs($member)->post(route('invitations.team'), [
            'email' => 'another.member@example.com',
            'role' => 'Member',
        ])->assertForbidden();
    }

    public function test_invitation_recipient_can_create_an_account(): void
    {
        $company = Company::create(['name' => 'Invited Company', 'slug' => 'invited-company']);
        $inviter = $this->makeUser(null, 'root@example.com', 'SuperAdmin');
        $token = 'stable-test-invitation-token';
        $invitation = Invitation::create([
            'company_id' => $company->id,
            'invited_by' => $inviter->id,
            'email' => 'new.admin@example.com',
            'role' => 'Admin',
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDay(),
        ]);

        $this->get(route('invitations.accept.show', $token))->assertOk()->assertSee('Invited Company');
        $this->post(route('invitations.accept', $token), [
            'name' => 'New Admin',
            'password' => 'A-very-long-password-12',
            'password_confirmation' => 'A-very-long-password-12',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'company_id' => $company->id,
            'email' => 'new.admin@example.com',
            'role' => 'Admin',
        ]);
        $this->assertNotNull($invitation->fresh()->accepted_at);
    }

    private function makeUser(?Company $company, string $email, string $role): User
    {
        return User::create([
            'company_id' => $company?->id,
            'name' => 'Test User',
            'email' => $email,
            'password' => Hash::make('Example-password-123'),
            'role' => $role,
        ]);
    }
}
