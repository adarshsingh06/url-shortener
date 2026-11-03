<?php

namespace App\Http\Controllers;

use App\Models\ShortUrl; 
use Illuminate\Http\RedirectResponse;

class PublicRedirectController extends Controller
{
    public function __invoke(string $code): RedirectResponse
    {
        $shortUrl = ShortUrl::where('code', $code)->firstOrFail();
        $shortUrl->increment('clicks');

        return redirect()->away($shortUrl->original_url, 302);
    }
}
