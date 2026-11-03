<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function inviteCompany(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless($user->isSuperAdmin(), 403);

        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
        
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
        ]);

        $email = Str::lower(trim($data['email']));
        $token = Str::random(64);

        [$company, $invitation] = DB::transaction(function () use ($user, $data, $email, $token): array {
            $baseSlug = Str::slug($data['company_name']) ?: 'company';
            $slug = $baseSlug;
            $suffix = 2;
            while (Company::where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$suffix++;
            }

            $company = Company::create(['name' => trim($data['company_name']), 'slug' => $slug]);
            $invitation = $company->invitations()->create([
                'invited_by' => $user->id,
                'email' => $email,
                'role' => User::ROLE_ADMIN,
                'token_hash' => hash('sha256', $token),
                'expires_at' => now()->addDays(7),
            ]);

            return [$company, $invitation];
        });

        $link = route('invitations.accept.show', ['token' => $token]);
        $this->sendInvitationEmail($email, $company->name, User::ROLE_ADMIN, $link);

        return redirect()->route('dashboard')
            ->with('status', "An Admin invitation for {$company->name} has been created.")
            ->with('invite_link', $link);
    }

    public function inviteTeamMember(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless($user->isAdmin() && $user->company_id !== null, 403);

        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_MEMBER])],
        ]);

        $email = Str::lower(trim($data['email']));
        $token = Str::random(64);
        $company = $user->company;

        $company->invitations()->create([
            'invited_by' => $user->id,
            'email' => $email,
            'role' => $data['role'],
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDays(7),
        ]);

        $link = route('invitations.accept.show', ['token' => $token]);
        $this->sendInvitationEmail($email, $company->name, $data['role'], $link);

        return redirect()->route('dashboard')
            ->with('status', 'Invitation created. Share the link below with the recipient if needed.')
            ->with('invite_link', $link);
    }

    public function showAccept(string $token): View
    {
        $invitation = Invitation::with('company')
            ->where('token_hash', hash('sha256', $token))
            ->firstOrFail();

        abort_unless($invitation->isUsable(), 410, 'This invitation has expired or has already been accepted.');

        return view('invitations.accept', compact('invitation', 'token'));
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        $user = DB::transaction(function () use ($data, $token): User {
            $invitation = Invitation::where('token_hash', hash('sha256', $token))
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($invitation->isUsable(), 410, 'This invitation has expired or has already been accepted.');

            if (User::where('email', $invitation->email)->exists()) {
                abort(409, 'An account with this email already exists.');
            }

            $newUser = User::create([
                'company_id' => $invitation->company_id,
                'name' => trim($data['name']),
                'email' => $invitation->email,
                'password' => Hash::make($data['password']),
                'role' => $invitation->role,
            ]);

            $invitation->update(['accepted_at' => now()]);

            return $newUser;
        });

        auth()->login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('status', 'Welcome. Your account is ready.');
    }

    private function sendInvitationEmail(string $email, string $companyName, string $role, string $link): void
    {
        try {
            Mail::raw(
                "You have been invited to join {$companyName} as {$role}.\n\nAccept the invitation within 7 days: {$link}\n",
                function ($message) use ($email, $companyName): void {
                    $message->to($email)->subject("Invitation to join {$companyName}");
                }
            );
        } catch (\Throwable $exception) {
            // The inviter still receives a usable link in the dashboard in local/dev setups.
            Log::warning('Invitation email could not be sent.', [
                'email' => $email,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
