<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\ShortUrl;
use App\Models\User; 
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    // data filters for filter url records
    private const FILTERS = ['this_month', 'last_month', 'last_week', 'today'];

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $filter = in_array($request->query('filter'), self::FILTERS, true)
            ? $request->query('filter')
            : 'this_month';

        $urlsQuery = $this->scopeUrls(ShortUrl::query(), $user)
            ->with(['user:id,name,email', 'company:id,name']);

        $this->applyDateFilter($urlsQuery, $filter);

        $urls = $urlsQuery->latest()->paginate(10)->withQueryString();

        $clients = collect();
        $teamMembers = collect();

        if ($user->isSuperAdmin()) {
            $clients = Company::query()
                ->withCount(['users', 'shortUrls'])
                ->withSum('shortUrls', 'clicks')
                ->orderBy('name')
                ->get();

            $overview = [
                'companies' => Company::count(),
                'users' => User::where('role', '!=', User::ROLE_SUPER_ADMIN)->count(),
                'urls' => ShortUrl::count(),
                'clicks' => (int) ShortUrl::sum('clicks'),
            ];
        } elseif ($user->isAdmin() && $user->company_id !== null) {
            $teamMembers = User::query()
                ->where('company_id', $user->company_id)
                ->withCount('shortUrls')
                ->withSum('shortUrls', 'clicks')
                ->orderBy('name')
                ->get();

            $overview = [
                'companies' => 1,
                'users' => User::where('company_id', $user->company_id)->count(),
                'urls' => ShortUrl::where('company_id', $user->company_id)->count(),
                'clicks' => (int) ShortUrl::where('company_id', $user->company_id)->sum('clicks'),
            ];
        } else {
            $overview = [
                'companies' => $user->company_id === null ? 0 : 1,
                'users' => 1,
                'urls' => ShortUrl::where('user_id', $user->id)->count(),
                'clicks' => (int) ShortUrl::where('user_id', $user->id)->sum('clicks'),
            ];
        }

        return view('dashboard.index', [
            'user' => $user,
            'filter' => $filter,
            'urls' => $urls,
            'clients' => $clients,
            'teamMembers' => $teamMembers,
            'overview' => $overview,
            'inviteLink' => session('invite_link'),
        ]);
    }

    public function download(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $filter = in_array($request->query('filter'), self::FILTERS, true)
            ? $request->query('filter')
            : 'this_month';

        $query = $this->scopeUrls(ShortUrl::query(), $user)
            ->with(['user:id,name,email', 'company:id,name']);
        $this->applyDateFilter($query, $filter);

        $filename = 'short-urls-'.$filter.'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Short URL', 'Original URL', 'Clicks', 'Creator', 'Company', 'Created At'], ',', '"', '');

            $query->orderByDesc('created_at')->chunk(500, function ($rows) use ($output): void {
                foreach ($rows as $url) {
                    fputcsv($output, [
                        $this->safeCsvValue($url->short_url),
                        $this->safeCsvValue($url->original_url),
                        $url->clicks,
                        $this->safeCsvValue($url->user?->name ?? ''),
                        $this->safeCsvValue($url->company?->name ?? ''),
                        $url->created_at?->toDateTimeString(),
                    ], ',', '"', '');
                }
            });

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function scopeUrls(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isAdmin() && $user->company_id !== null) {
            return $query->where('company_id', $user->company_id);
        }

        return $query->where('user_id', $user->id);
    }

    private function applyDateFilter(Builder $query, string $filter): void
    {
        $now = now();

        [$start, $end] = match ($filter) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'last_week' => [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()],
            'last_month' => [$now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth()],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };

        $query->whereBetween('created_at', [$start, $end]);
    }

    private function safeCsvValue(string $value): string
    {
        return preg_match('/^[\s]*[=+\-@]/', $value) ? "'".$value : $value;
    }
}
