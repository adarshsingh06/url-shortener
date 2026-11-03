<?php

namespace App\Http\Controllers;

use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str; 
use Illuminate\Validation\ValidationException;

class ShortUrlController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user->canCreateShortUrls()) {
            abort(403, 'Only company Admins and Members can create short URLs.');
        }

        $validated = $request->validate([
            'original_url' => [
                'required',
                'string',
                'max:2048',
                'url:http,https',
            ],
        ]);

        $code = null;
        for ($attempt = 0; $attempt < 8; $attempt++) {
            $candidate = Str::lower(Str::random(7));
            if (! ShortUrl::where('code', $candidate)->exists()) {
                $code = $candidate;
                break;
            }
        }

        if ($code === null) {
            throw ValidationException::withMessages([
                'original_url' => 'A short code could not be reserved. Please submit the URL again.',
            ]);
        }

        $shortUrl = ShortUrl::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'code' => $code,
            'original_url' => $validated['original_url'],
        ]);

        return redirect()->route('dashboard', ['filter' => $request->query('filter', 'this_month')])
            ->with('created_short_url', $shortUrl->short_url)
            ->with('status', 'Short URL created.');
    }
}
