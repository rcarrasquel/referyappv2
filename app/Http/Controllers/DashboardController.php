<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\BillingTransaction;
use App\Models\Card;
use App\Models\CardLinkClick;
use App\Models\CardVisit;
use App\Models\Lead;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $isAdmin = $user?->role === 'admin';
        $cardsCount = $isAdmin
            ? Card::query()->count()
            : ($user?->cards()->count() ?? 0);
        $needsFirstCard = $user && $user->role === 'business' && $cardsCount === 0;
        $cardIds = $isAdmin
            ? Card::query()->pluck('id')
            : ($user?->cards()->pluck('id') ?? collect());

        $now = now();
        $windowStart = $now->copy()->subDays(30);
        $previousWindowStart = $now->copy()->subDays(60);

        $visitsTotal = CardVisit::query()
            ->whereIn('card_id', $cardIds)
            ->count();

        $visitsLast30 = CardVisit::query()
            ->whereIn('card_id', $cardIds)
            ->where('visited_at', '>=', $windowStart)
            ->count();

        $visitsPrev30 = CardVisit::query()
            ->whereIn('card_id', $cardIds)
            ->whereBetween('visited_at', [$previousWindowStart, $windowStart])
            ->count();

        $uniqueVisitors30 = CardVisit::query()
            ->whereIn('card_id', $cardIds)
            ->where('visited_at', '>=', $windowStart)
            ->whereNotNull('fingerprint')
            ->distinct('fingerprint')
            ->count('fingerprint');

        $linkClicksTotal = CardLinkClick::query()
            ->whereIn('card_id', $cardIds)
            ->count();

        $linkClicks30 = CardLinkClick::query()
            ->whereIn('card_id', $cardIds)
            ->where('clicked_at', '>=', $windowStart)
            ->count();

        $linkClicksPrev30 = CardLinkClick::query()
            ->whereIn('card_id', $cardIds)
            ->whereBetween('clicked_at', [$previousWindowStart, $windowStart])
            ->count();

        $ctr30 = $visitsLast30 > 0 ? round(($linkClicks30 / $visitsLast30) * 100, 1) : 0.0;

        $topCards = Card::query()
            ->when(! $isAdmin, fn ($query) => $query->where('user_id', $user?->id))
            ->select(['id', 'name', 'username'])
            ->withCount([
                'visits as visits_last_30' => fn ($query) => $query->where('visited_at', '>=', $windowStart),
                'linkClicks as clicks_last_30' => fn ($query) => $query->where('clicked_at', '>=', $windowStart),
            ])
            ->orderByDesc('visits_last_30')
            ->limit(5)
            ->get()
            ->map(function ($card): array {
                $visits = (int) $card->visits_last_30;
                $clicks = (int) $card->clicks_last_30;
                $ctr = $visits > 0 ? round(($clicks / $visits) * 100, 1) : 0.0;

                return [
                    'id' => $card->id,
                    'name' => $card->name,
                    'username' => $card->username,
                    'visits' => $visits,
                    'clicks' => $clicks,
                    'ctr' => $ctr,
                ];
            })
            ->values()
            ->all();

        $browsers = $this->breakdown(
            CardVisit::query()
                ->selectRaw('browser as label, count(*) as total')
                ->whereIn('card_id', $cardIds)
                ->where('visited_at', '>=', $windowStart)
                ->groupBy('browser')
                ->orderByDesc('total')
                ->limit(6)
                ->get()
        );

        $devices = $this->breakdown(
            CardVisit::query()
                ->selectRaw('device_type as label, count(*) as total')
                ->whereIn('card_id', $cardIds)
                ->where('visited_at', '>=', $windowStart)
                ->groupBy('device_type')
                ->orderByDesc('total')
                ->limit(6)
                ->get()
        );

        $adminBasics = null;
        if ($isAdmin) {
            $paidRevenueQuery = BillingTransaction::query()
                ->where(function ($query): void {
                    $query
                        ->whereNotNull('paid_at')
                        ->orWhereIn('status', ['paid', 'succeeded']);
                });

            $currentMonthStart = now()->startOfMonth();
            $currentMonthEnd = now()->endOfMonth();
            $previousMonthStart = now()->subMonthNoOverflow()->startOfMonth();
            $previousMonthEnd = now()->subMonthNoOverflow()->endOfMonth();

            $monthlyRows = BillingTransaction::query()
                ->selectRaw("DATE_FORMAT(COALESCE(paid_at, created_at), '%Y-%m') as ym, SUM(amount_cents) as total")
                ->where(function ($query): void {
                    $query
                        ->whereNotNull('paid_at')
                        ->orWhereIn('status', ['paid', 'succeeded']);
                })
                ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
                ->groupBy('ym')
                ->pluck('total', 'ym');

            $monthSeries = collect(range(5, 0))
                ->map(function (int $offset) use ($monthlyRows): array {
                    $date = now()->subMonths($offset);
                    $key = $date->format('Y-m');

                    return [
                        'key' => $key,
                        'label' => $date->format('M Y'),
                        'total_cents' => (int) ($monthlyRows[$key] ?? 0),
                    ];
                })
                ->values();

            $currentMonthRevenueCents = (int) (clone $paidRevenueQuery)
                ->whereBetween(DB::raw('COALESCE(paid_at, created_at)'), [$currentMonthStart, $currentMonthEnd])
                ->sum('amount_cents');
            $previousMonthRevenueCents = (int) (clone $paidRevenueQuery)
                ->whereBetween(DB::raw('COALESCE(paid_at, created_at)'), [$previousMonthStart, $previousMonthEnd])
                ->sum('amount_cents');
            $totalRevenueCents = (int) (clone $paidRevenueQuery)->sum('amount_cents');

            $paidBusinessUsersCount = User::query()
                ->where('role', 'business')
                ->whereHas('billingTransactions', function ($query): void {
                    $query
                        ->whereNotNull('paid_at')
                        ->orWhereIn('status', ['paid', 'succeeded']);
                })
                ->count();

            $adminBasics = [
                'total_users' => User::query()->count(),
                'total_business_users' => User::query()->where('role', 'business')->count(),
                'paid_business_users' => $paidBusinessUsersCount,
                'total_admin_users' => User::query()->where('role', 'admin')->count(),
                'total_cards' => Card::query()->count(),
                'total_products' => Product::query()->count(),
                'total_appointments' => Appointment::query()->count(),
                'total_leads' => Lead::query()->count(),
                'revenue_total_cents' => $totalRevenueCents,
                'revenue_month_cents' => $currentMonthRevenueCents,
                'revenue_previous_month_cents' => $previousMonthRevenueCents,
                'revenue_month_delta_percent' => $this->percentageDelta($currentMonthRevenueCents, $previousMonthRevenueCents),
                'monthly_revenue_series' => $monthSeries->all(),
                'recent_users' => User::query()
                    ->latest('created_at')
                    ->limit(8)
                    ->get(['id', 'name', 'email', 'role', 'plan', 'created_at'])
                    ->map(fn (User $u): array => [
                        'id' => $u->id,
                        'name' => $u->name,
                        'email' => $u->email,
                        'role' => $u->role,
                        'plan' => $u->plan,
                        'created_at' => optional($u->created_at)->toIso8601String(),
                    ])
                    ->values()
                    ->all(),
            ];
        }

        return Inertia::render('Dashboard/Index', [
            'stats' => [
                [
                    'label' => 'Card Views (30d)',
                    'value' => number_format($visitsLast30),
                    'trend' => $this->trendLabel($visitsLast30, $visitsPrev30),
                    'status' => $this->trendStatus($visitsLast30, $visitsPrev30),
                    'accent' => 'default',
                ],
                [
                    'label' => 'Unique Visitors (30d)',
                    'value' => number_format($uniqueVisitors30),
                    'trend' => 'Distinct visitors',
                    'status' => 'positive',
                    'accent' => 'default',
                ],
                [
                    'label' => 'Link Clicks (30d)',
                    'value' => number_format($linkClicks30),
                    'trend' => $this->trendLabel($linkClicks30, $linkClicksPrev30),
                    'status' => $this->trendStatus($linkClicks30, $linkClicksPrev30),
                    'accent' => 'default',
                ],
                [
                    'label' => 'CTR (30d)',
                    'value' => $ctr30 . '%',
                    'trend' => number_format($linkClicksTotal) . ' total clicks',
                    'status' => $ctr30 >= 5 ? 'positive' : 'neutral',
                    'accent' => 'default',
                ],
            ],
            'analytics' => [
                'totals' => [
                    'visits' => $visitsTotal,
                    'clicks' => $linkClicksTotal,
                ],
                'topCards' => $topCards,
                'browsers' => $browsers,
                'devices' => $devices,
            ],
            'cardsCount' => $cardsCount,
            'needsFirstCard' => $needsFirstCard,
            'isAdmin' => $isAdmin,
            'adminBasics' => $adminBasics,
        ]);
    }

    private function trendLabel(int $current, int $previous): string
    {
        if ($previous <= 0) {
            return $current > 0 ? 'New activity' : 'No change';
        }

        $delta = (($current - $previous) / $previous) * 100;
        $prefix = $delta >= 0 ? '+' : '';

        return $prefix . number_format($delta, 1) . '%';
    }

    private function trendStatus(int $current, int $previous): string
    {
        if ($current === $previous) {
            return 'neutral';
        }

        return $current > $previous ? 'positive' : 'negative';
    }

    private function breakdown(Collection $rows): array
    {
        return $rows
            ->map(fn ($row): array => [
                'label' => $row->label ?: 'Unknown',
                'total' => (int) $row->total,
            ])
            ->values()
            ->all();
    }

    private function percentageDelta(int $current, int $previous): float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
