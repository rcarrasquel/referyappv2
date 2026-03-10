<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Card;
use App\Models\CardLinkClick;
use App\Models\CardShareEvent;
use App\Models\CardVisit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AnalyticsController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $this->requireBusinessUser($request);

        $cardsQuery = Card::query();
        if ($user->role !== 'admin') {
            $cardsQuery->where('user_id', $user->id);
        }

        $cards = $cardsQuery
            ->select(['id', 'name', 'username', 'slug'])
            ->orderBy('name')
            ->get();

        $cardIds = $cards->pluck('id');

        [$start, $end] = $this->resolveDateRange(
            (string) $request->query('start_date', ''),
            (string) $request->query('end_date', '')
        );

        $compareMode = (string) $request->query('compare', 'previous_period');
        if (! in_array($compareMode, ['none', 'previous_period', 'previous_year'], true)) {
            $compareMode = 'previous_period';
        }

        $selectedCardId = $request->query('card_id');
        if ($selectedCardId !== null && ! $cardIds->contains($selectedCardId)) {
            throw ValidationException::withMessages([
                'card_id' => 'Selected card is invalid.',
            ]);
        }

        $device = $request->query('device');
        $validDevices = ['desktop', 'mobile', 'tablet'];
        if ($device !== null && $device !== '' && ! in_array($device, $validDevices, true)) {
            throw ValidationException::withMessages([
                'device' => 'Device filter is invalid.',
            ]);
        }
        $device = $device ?: null;

        $browser = trim((string) $request->query('browser', ''));
        $browser = $browser !== '' ? mb_substr($browser, 0, 120) : null;

        [$previousStart, $previousEnd] = $this->resolveComparisonWindow($start, $end, $compareMode);

        $currentViewsQuery = CardVisit::query();
        $currentClicksQuery = CardLinkClick::query();
        $currentSharesQuery = CardShareEvent::query();
        $previousViewsQuery = CardVisit::query();
        $previousClicksQuery = CardLinkClick::query();
        $previousSharesQuery = CardShareEvent::query();

        $this->applyVisitFilters($currentViewsQuery, $cardIds, $selectedCardId, $device, $browser, $start, $end);
        $this->applyClickFilters($currentClicksQuery, $cardIds, $selectedCardId, $device, $browser, $start, $end);
        $this->applyShareFilters($currentSharesQuery, $cardIds, $selectedCardId, $device, $browser, $start, $end);

        if ($previousStart && $previousEnd) {
            $this->applyVisitFilters($previousViewsQuery, $cardIds, $selectedCardId, $device, $browser, $previousStart, $previousEnd);
            $this->applyClickFilters($previousClicksQuery, $cardIds, $selectedCardId, $device, $browser, $previousStart, $previousEnd);
            $this->applyShareFilters($previousSharesQuery, $cardIds, $selectedCardId, $device, $browser, $previousStart, $previousEnd);
        }

        $views = (int) $currentViewsQuery->count();
        $clicks = (int) $currentClicksQuery->count();
        $shares = (int) $currentSharesQuery->count();
        $uniqueVisitors = (int) (clone $currentViewsQuery)
            ->distinct(DB::raw("COALESCE(fingerprint, CONCAT('ip:', IFNULL(ip_address, 'na'), '|ua:', IFNULL(user_agent, 'na')))"))
            ->count(DB::raw("COALESCE(fingerprint, CONCAT('ip:', IFNULL(ip_address, 'na'), '|ua:', IFNULL(user_agent, 'na')))"));
        $ctr = $views > 0 ? round(($clicks / $views) * 100, 2) : 0.0;

        $previousViews = $previousStart && $previousEnd ? (int) $previousViewsQuery->count() : 0;
        $previousClicks = $previousStart && $previousEnd ? (int) $previousClicksQuery->count() : 0;
        $previousShares = $previousStart && $previousEnd ? (int) $previousSharesQuery->count() : 0;
        $previousCtr = $previousViews > 0 ? round(($previousClicks / $previousViews) * 100, 2) : 0.0;

        $topCards = $this->topCards($cards, $cardIds, $selectedCardId, $device, $browser, $start, $end);
        $topLinks = $this->topLinks($cardIds, $selectedCardId, $device, $browser, $start, $end);
        $browsers = $this->breakdownVisits('browser', $cardIds, $selectedCardId, $device, $browser, $start, $end);
        $devices = $this->breakdownVisits('device_type', $cardIds, $selectedCardId, $device, $browser, $start, $end);
        $operatingSystems = $this->breakdownVisits('os', $cardIds, $selectedCardId, $device, $browser, $start, $end);
        $referrers = $this->breakdownVisits('referer', $cardIds, $selectedCardId, $device, $browser, $start, $end, 8);
        $shareChannels = $this->breakdownShares($cardIds, $selectedCardId, $device, $browser, $start, $end);

        $series = $this->timeSeries($cardIds, $selectedCardId, $device, $browser, $start, $end);
        $comparisonSeries = ($previousStart && $previousEnd)
            ? $this->timeSeries($cardIds, $selectedCardId, $device, $browser, $previousStart, $previousEnd)
            : ['labels' => [], 'views' => [], 'clicks' => []];

        $availableBrowsers = CardVisit::query()
            ->whereIn('card_id', $cardIds)
            ->whereNotNull('browser')
            ->where('browser', '!=', '')
            ->select('browser')
            ->distinct()
            ->orderBy('browser')
            ->pluck('browser')
            ->values()
            ->all();

        return $this->ok([
            'filters' => [
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'compare' => $compareMode,
                'card_id' => $selectedCardId,
                'device' => $device,
                'browser' => $browser,
            ],
            'filter_options' => [
                'cards' => $cards->map(fn (Card $card): array => [
                    'id' => $card->id,
                    'name' => $card->name,
                    'username' => $card->username,
                    'slug' => $card->slug,
                ])->values()->all(),
                'devices' => $validDevices,
                'browsers' => $availableBrowsers,
            ],
            'summary' => [
                'views' => $views,
                'clicks' => $clicks,
                'shares' => $shares,
                'unique_visitors' => $uniqueVisitors,
                'ctr' => $ctr,
                'previous_views' => $previousViews,
                'previous_clicks' => $previousClicks,
                'previous_shares' => $previousShares,
                'previous_ctr' => $previousCtr,
            ],
            'comparisons' => [
                'views_delta' => $this->delta($views, $previousViews),
                'clicks_delta' => $this->delta($clicks, $previousClicks),
                'shares_delta' => $this->delta($shares, $previousShares),
                'ctr_delta' => $this->delta($ctr, $previousCtr),
            ],
            'ranges' => [
                'current' => [
                    'start' => $start->toDateString(),
                    'end' => $end->toDateString(),
                ],
                'previous' => $previousStart && $previousEnd ? [
                    'start' => $previousStart->toDateString(),
                    'end' => $previousEnd->toDateString(),
                ] : null,
            ],
            'charts' => [
                'current' => $series,
                'previous' => $comparisonSeries,
            ],
            'top_cards' => $topCards,
            'top_links' => $topLinks,
            'breakdowns' => [
                'browsers' => $browsers,
                'devices' => $devices,
                'os' => $operatingSystems,
                'referrers' => $referrers,
                'share_channels' => $shareChannels,
            ],
        ]);
    }

    private function resolveDateRange(string $startDate, string $endDate): array
    {
        try {
            $start = $startDate !== '' ? Carbon::parse($startDate)->startOfDay() : now()->subDays(29)->startOfDay();
            $end = $endDate !== '' ? Carbon::parse($endDate)->endOfDay() : now()->endOfDay();
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages([
                'date_range' => 'Invalid date range.',
            ]);
        }

        if ($start->gt($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        return [$start, $end];
    }

    private function resolveComparisonWindow(Carbon $start, Carbon $end, string $compareMode): array
    {
        if ($compareMode === 'none') {
            return [null, null];
        }

        if ($compareMode === 'previous_year') {
            return [$start->copy()->subYear(), $end->copy()->subYear()];
        }

        $days = $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1;
        $previousEnd = $start->copy()->subDay()->endOfDay();
        $previousStart = $previousEnd->copy()->subDays($days - 1)->startOfDay();

        return [$previousStart, $previousEnd];
    }

    private function applyVisitFilters(Builder $query, Collection $cardIds, ?string $selectedCardId, ?string $device, ?string $browser, Carbon $start, Carbon $end): void
    {
        $query->whereIn('card_id', $selectedCardId ? [$selectedCardId] : $cardIds)
            ->whereBetween('visited_at', [$start, $end]);

        if ($device) {
            $query->where('device_type', $device);
        }

        if ($browser) {
            $query->where('browser', $browser);
        }
    }

    private function applyClickFilters(Builder $query, Collection $cardIds, ?string $selectedCardId, ?string $device, ?string $browser, Carbon $start, Carbon $end): void
    {
        $query->whereIn('card_id', $selectedCardId ? [$selectedCardId] : $cardIds)
            ->whereBetween('clicked_at', [$start, $end]);

        if ($device) {
            $query->where('device_type', $device);
        }

        if ($browser) {
            $query->where('browser', $browser);
        }
    }

    private function applyShareFilters(Builder $query, Collection $cardIds, ?string $selectedCardId, ?string $device, ?string $browser, Carbon $start, Carbon $end): void
    {
        $query->whereIn('card_id', $selectedCardId ? [$selectedCardId] : $cardIds)
            ->whereBetween('shared_at', [$start, $end]);

        if ($device) {
            $query->where('device_type', $device);
        }

        if ($browser) {
            $query->where('browser', $browser);
        }
    }

    private function topCards(Collection $cards, Collection $cardIds, ?string $selectedCardId, ?string $device, ?string $browser, Carbon $start, Carbon $end): array
    {
        $byViews = CardVisit::query()
            ->selectRaw('card_id, count(*) as total')
            ->whereIn('card_id', $selectedCardId ? [$selectedCardId] : $cardIds)
            ->whereBetween('visited_at', [$start, $end]);

        if ($device) {
            $byViews->where('device_type', $device);
        }

        if ($browser) {
            $byViews->where('browser', $browser);
        }

        $viewsRows = $byViews->groupBy('card_id')->get()->keyBy('card_id');

        $byClicks = CardLinkClick::query()
            ->selectRaw('card_id, count(*) as total')
            ->whereIn('card_id', $selectedCardId ? [$selectedCardId] : $cardIds)
            ->whereBetween('clicked_at', [$start, $end]);

        if ($device) {
            $byClicks->where('device_type', $device);
        }

        if ($browser) {
            $byClicks->where('browser', $browser);
        }

        $clickRows = $byClicks->groupBy('card_id')->get()->keyBy('card_id');

        return $cards
            ->map(function (Card $card) use ($viewsRows, $clickRows): array {
                $views = (int) ($viewsRows[$card->id]->total ?? 0);
                $clicks = (int) ($clickRows[$card->id]->total ?? 0);

                return [
                    'card_id' => $card->id,
                    'name' => $card->name,
                    'username' => $card->username,
                    'views' => $views,
                    'clicks' => $clicks,
                    'ctr' => $views > 0 ? round(($clicks / $views) * 100, 2) : 0.0,
                ];
            })
            ->sortByDesc('views')
            ->values()
            ->take(10)
            ->all();
    }

    private function topLinks(Collection $cardIds, ?string $selectedCardId, ?string $device, ?string $browser, Carbon $start, Carbon $end): array
    {
        $query = CardLinkClick::query()
            ->selectRaw('COALESCE(link_title, link_url) as link_name, link_url, count(*) as total')
            ->whereIn('card_id', $selectedCardId ? [$selectedCardId] : $cardIds)
            ->whereBetween('clicked_at', [$start, $end]);

        if ($device) {
            $query->where('device_type', $device);
        }

        if ($browser) {
            $query->where('browser', $browser);
        }

        return $query
            ->groupBy('link_title', 'link_url')
            ->orderByDesc('total')
            ->limit(12)
            ->get()
            ->map(fn ($row): array => [
                'name' => $row->link_name ?: 'Untitled link',
                'url' => $row->link_url,
                'clicks' => (int) $row->total,
            ])
            ->values()
            ->all();
    }

    private function breakdownVisits(string $column, Collection $cardIds, ?string $selectedCardId, ?string $device, ?string $browser, Carbon $start, Carbon $end, int $limit = 10): array
    {
        $query = CardVisit::query()
            ->selectRaw("COALESCE(NULLIF({$column}, ''), 'Unknown') as label, count(*) as total")
            ->whereIn('card_id', $selectedCardId ? [$selectedCardId] : $cardIds)
            ->whereBetween('visited_at', [$start, $end]);

        if ($device) {
            $query->where('device_type', $device);
        }

        if ($browser) {
            $query->where('browser', $browser);
        }

        return $query
            ->groupBy($column)
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn ($row): array => [
                'label' => $row->label,
                'total' => (int) $row->total,
            ])
            ->values()
            ->all();
    }

    private function breakdownShares(Collection $cardIds, ?string $selectedCardId, ?string $device, ?string $browser, Carbon $start, Carbon $end): array
    {
        $query = CardShareEvent::query()
            ->selectRaw("COALESCE(NULLIF(channel, ''), 'Unknown') as label, count(*) as total")
            ->whereIn('card_id', $selectedCardId ? [$selectedCardId] : $cardIds)
            ->whereBetween('shared_at', [$start, $end]);

        if ($device) {
            $query->where('device_type', $device);
        }

        if ($browser) {
            $query->where('browser', $browser);
        }

        return $query
            ->groupBy('channel')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($row): array => [
                'label' => $row->label,
                'total' => (int) $row->total,
            ])
            ->values()
            ->all();
    }

    private function timeSeries(Collection $cardIds, ?string $selectedCardId, ?string $device, ?string $browser, Carbon $start, Carbon $end): array
    {
        $labels = [];
        $cursor = $start->copy()->startOfDay();
        $endDay = $end->copy()->startOfDay();
        while ($cursor->lte($endDay)) {
            $labels[] = $cursor->toDateString();
            $cursor->addDay();
        }

        $viewsQuery = CardVisit::query()
            ->selectRaw('DATE(visited_at) as day, count(*) as total')
            ->whereIn('card_id', $selectedCardId ? [$selectedCardId] : $cardIds)
            ->whereBetween('visited_at', [$start, $end]);

        $clicksQuery = CardLinkClick::query()
            ->selectRaw('DATE(clicked_at) as day, count(*) as total')
            ->whereIn('card_id', $selectedCardId ? [$selectedCardId] : $cardIds)
            ->whereBetween('clicked_at', [$start, $end]);

        if ($device) {
            $viewsQuery->where('device_type', $device);
            $clicksQuery->where('device_type', $device);
        }

        if ($browser) {
            $viewsQuery->where('browser', $browser);
            $clicksQuery->where('browser', $browser);
        }

        $viewsMap = $viewsQuery->groupBy('day')->pluck('total', 'day');
        $clicksMap = $clicksQuery->groupBy('day')->pluck('total', 'day');

        return [
            'labels' => $labels,
            'views' => collect($labels)->map(fn ($day): int => (int) ($viewsMap[$day] ?? 0))->all(),
            'clicks' => collect($labels)->map(fn ($day): int => (int) ($clicksMap[$day] ?? 0))->all(),
        ];
    }

    private function delta(float|int $current, float|int $previous): array
    {
        if ($previous === 0.0 || $previous === 0) {
            return [
                'value' => $current > 0 ? 100.0 : 0.0,
                'direction' => $current > 0 ? 'up' : 'flat',
            ];
        }

        $delta = (($current - $previous) / $previous) * 100;

        return [
            'value' => round($delta, 2),
            'direction' => $delta > 0 ? 'up' : ($delta < 0 ? 'down' : 'flat'),
        ];
    }
}
