<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Appointment;
use App\Models\Card;
use App\Models\CardLinkClick;
use App\Models\CardVisit;
use App\Models\Lead;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $this->requireAdminUser($request);

        $search = trim((string) $request->query('search', ''));
        $perPage = max(1, min((int) $request->query('per_page', 20), 100));

        $query = User::query()->latest('id');

        if ($search !== '') {
            $query->where(function ($sub) use ($search): void {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $items = $query
            ->select('id', 'name', 'email', 'role', 'plan', 'language', 'created_at')
            ->paginate($perPage);

        return $this->ok([
            'items' => $items->getCollection()->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'plan' => $user->plan,
                'language' => $user->language,
                'created_at' => optional($user->created_at)->toIso8601String(),
            ])->values()->all(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
            'totals' => [
                'users' => User::query()->count(),
                'business_users' => User::query()->where('role', 'business')->count(),
                'admin_users' => User::query()->where('role', 'admin')->count(),
            ],
        ]);
    }

    public function show(Request $request, User $user): JsonResponse
    {
        $this->requireAdminUser($request);

        $cards = Card::query()
            ->where('user_id', $user->id)
            ->orderBy('name')
            ->get(['id', 'name', 'username', 'created_at']);

        $cardIds = $cards->pluck('id');
        $windowStart = now()->subDays(30);

        $visitsByCard = CardVisit::query()
            ->selectRaw('card_id, count(*) as total')
            ->whereIn('card_id', $cardIds)
            ->groupBy('card_id')
            ->get()
            ->keyBy('card_id');

        $clicksByCard = CardLinkClick::query()
            ->selectRaw('card_id, count(*) as total')
            ->whereIn('card_id', $cardIds)
            ->groupBy('card_id')
            ->get()
            ->keyBy('card_id');

        $visits30ByCard = CardVisit::query()
            ->selectRaw('card_id, count(*) as total')
            ->whereIn('card_id', $cardIds)
            ->where('visited_at', '>=', $windowStart)
            ->groupBy('card_id')
            ->get()
            ->keyBy('card_id');

        $clicks30ByCard = CardLinkClick::query()
            ->selectRaw('card_id, count(*) as total')
            ->whereIn('card_id', $cardIds)
            ->where('clicked_at', '>=', $windowStart)
            ->groupBy('card_id')
            ->get()
            ->keyBy('card_id');

        $cardsMetrics = $cards->map(function (Card $card) use ($visitsByCard, $clicksByCard, $visits30ByCard, $clicks30ByCard): array {
            $visits = (int) ($visitsByCard[$card->id]->total ?? 0);
            $clicks = (int) ($clicksByCard[$card->id]->total ?? 0);
            $visits30 = (int) ($visits30ByCard[$card->id]->total ?? 0);
            $clicks30 = (int) ($clicks30ByCard[$card->id]->total ?? 0);
            $ctr30 = $visits30 > 0 ? round(($clicks30 / $visits30) * 100, 2) : 0.0;

            return [
                'id' => $card->id,
                'name' => $card->name,
                'username' => $card->username,
                'created_at' => optional($card->created_at)->toIso8601String(),
                'visits_total' => $visits,
                'clicks_total' => $clicks,
                'visits_30d' => $visits30,
                'clicks_30d' => $clicks30,
                'ctr_30d' => $ctr30,
            ];
        })->values()->all();

        return $this->ok([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'plan' => $user->plan,
                'language' => $user->language,
                'created_at' => optional($user->created_at)->toIso8601String(),
            ],
            'summary' => [
                'cards_total' => Card::query()->where('user_id', $user->id)->count(),
                'products_total' => Product::query()->where('user_id', $user->id)->count(),
                'appointments_total' => Appointment::query()->where('user_id', $user->id)->count(),
                'leads_total' => Lead::query()->where('user_id', $user->id)->count(),
                'visits_total' => CardVisit::query()->whereIn('card_id', $cardIds)->count(),
                'clicks_total' => CardLinkClick::query()->whereIn('card_id', $cardIds)->count(),
            ],
            'cards_metrics' => $cardsMetrics,
        ]);
    }
}

