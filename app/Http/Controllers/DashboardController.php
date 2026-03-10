<?php

namespace App\Http\Controllers;

use App\Models\CardLinkClick;
use App\Models\CardVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $cardsCount = $user?->cards()->count() ?? 0;
        $needsFirstCard = $user && $user->role === 'business' && $cardsCount === 0;
        $cardIds = $user?->cards()->pluck('id') ?? collect();

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

        $topCards = $user?->cards()
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
            ->all() ?? [];

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
}
