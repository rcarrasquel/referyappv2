<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Card;
use App\Models\CardLinkClick;
use App\Models\CardShareEvent;
use App\Models\CardVisit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends BaseApiController
{
    public function summary(Request $request): JsonResponse
    {
        $user = $this->requireApiUser($request);
        $windowDays = max(1, min((int) $request->query('window_days', 30), 90));

        $cardIds = Card::query()
            ->when($user->role !== 'admin', fn ($query) => $query->where('user_id', $user->id))
            ->pluck('id');

        $now = now();
        $windowStart = $now->copy()->subDays($windowDays);
        $previousWindowStart = $now->copy()->subDays($windowDays * 2);

        $viewsTotal = CardVisit::query()
            ->whereIn('card_id', $cardIds)
            ->count();

        $clicksTotal = CardLinkClick::query()
            ->whereIn('card_id', $cardIds)
            ->count();

        $sharesTotal = CardShareEvent::query()
            ->whereIn('card_id', $cardIds)
            ->count();

        $views = CardVisit::query()
            ->whereIn('card_id', $cardIds)
            ->where('visited_at', '>=', $windowStart)
            ->count();

        $uniqueVisitors = CardVisit::query()
            ->whereIn('card_id', $cardIds)
            ->where('visited_at', '>=', $windowStart)
            ->whereNotNull('fingerprint')
            ->distinct('fingerprint')
            ->count('fingerprint');

        $clicks = CardLinkClick::query()
            ->whereIn('card_id', $cardIds)
            ->where('clicked_at', '>=', $windowStart)
            ->count();

        $clicksPrevious = CardLinkClick::query()
            ->whereIn('card_id', $cardIds)
            ->whereBetween('clicked_at', [$previousWindowStart, $windowStart])
            ->count();

        $shares = CardShareEvent::query()
            ->whereIn('card_id', $cardIds)
            ->where('shared_at', '>=', $windowStart)
            ->count();

        $viewsPrevious = CardVisit::query()
            ->whereIn('card_id', $cardIds)
            ->whereBetween('visited_at', [$previousWindowStart, $windowStart])
            ->count();

        $sharesPrevious = CardShareEvent::query()
            ->whereIn('card_id', $cardIds)
            ->whereBetween('shared_at', [$previousWindowStart, $windowStart])
            ->count();

        $ctr = $views > 0 ? round(($clicks / $views) * 100, 2) : 0.0;
        $ctrPrevious = $viewsPrevious > 0 ? round(($clicksPrevious / $viewsPrevious) * 100, 2) : 0.0;

        $topCards = Card::query()
            ->whereIn('id', $cardIds)
            ->select(['id', 'name', 'username'])
            ->withCount([
                'visits as visits_window' => fn ($query) => $query->where('visited_at', '>=', $windowStart),
                'linkClicks as clicks_window' => fn ($query) => $query->where('clicked_at', '>=', $windowStart),
            ])
            ->orderByDesc('visits_window')
            ->limit(5)
            ->get()
            ->map(function (Card $card): array {
                $visits = (int) ($card->visits_window ?? 0);
                $clicks = (int) ($card->clicks_window ?? 0);
                $ctr = $visits > 0 ? round(($clicks / $visits) * 100, 2) : 0.0;

                return [
                    'id' => $card->id,
                    'name' => $card->name,
                    'username' => $card->username,
                    'views' => $visits,
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
                ->limit(8)
                ->get()
        );

        $devices = $this->breakdown(
            CardVisit::query()
                ->selectRaw('device_type as label, count(*) as total')
                ->whereIn('card_id', $cardIds)
                ->where('visited_at', '>=', $windowStart)
                ->groupBy('device_type')
                ->orderByDesc('total')
                ->limit(8)
                ->get()
        );

        $shareChannels = $this->breakdown(
            CardShareEvent::query()
                ->selectRaw('channel as label, count(*) as total')
                ->whereIn('card_id', $cardIds)
                ->where('shared_at', '>=', $windowStart)
                ->groupBy('channel')
                ->orderByDesc('total')
                ->limit(10)
                ->get()
        );

        return $this->ok([
            'window_days' => $windowDays,
            'cards_count' => (int) $cardIds->count(),
            'metrics' => [
                'views' => $views,
                'unique_visitors' => $uniqueVisitors,
                'clicks' => $clicks,
                'shares' => $shares,
                'ctr' => $ctr,
            ],
            'totals' => [
                'views' => $viewsTotal,
                'clicks' => $clicksTotal,
                'shares' => $sharesTotal,
            ],
            'comparisons' => [
                'views' => [
                    'current' => $views,
                    'previous' => $viewsPrevious,
                    'delta_percent' => $this->deltaPercent($views, $viewsPrevious),
                    'tone' => $this->trendTone($views, $viewsPrevious),
                ],
                'clicks' => [
                    'current' => $clicks,
                    'previous' => $clicksPrevious,
                    'delta_percent' => $this->deltaPercent($clicks, $clicksPrevious),
                    'tone' => $this->trendTone($clicks, $clicksPrevious),
                ],
                'shares' => [
                    'current' => $shares,
                    'previous' => $sharesPrevious,
                    'delta_percent' => $this->deltaPercent($shares, $sharesPrevious),
                    'tone' => $this->trendTone($shares, $sharesPrevious),
                ],
                'ctr' => [
                    'current' => $ctr,
                    'previous' => $ctrPrevious,
                    'delta_percent' => $this->deltaPercentFloat($ctr, $ctrPrevious),
                    'tone' => $this->trendToneFloat($ctr, $ctrPrevious),
                ],
            ],
            'top_cards' => $topCards,
            'breakdowns' => [
                'browsers' => $browsers,
                'devices' => $devices,
                'share_channels' => $shareChannels,
            ],
        ]);
    }

    private function breakdown(Collection $rows): array
    {
        return $rows
            ->map(fn ($row): array => [
                'label' => (string) ($row->label ?: 'Unknown'),
                'total' => (int) $row->total,
            ])
            ->values()
            ->all();
    }

    private function deltaPercent(int $current, int $previous): float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    private function deltaPercentFloat(float $current, float $previous): float
    {
        if ($previous <= 0.0) {
            return $current > 0.0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    private function trendTone(int $current, int $previous): string
    {
        if ($current === $previous) {
            return 'neutral';
        }

        return $current > $previous ? 'positive' : 'negative';
    }

    private function trendToneFloat(float $current, float $previous): string
    {
        if ($current === $previous) {
            return 'neutral';
        }

        return $current > $previous ? 'positive' : 'negative';
    }
}
