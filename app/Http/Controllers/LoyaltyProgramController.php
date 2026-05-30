<?php

namespace App\Http\Controllers;

use App\Models\LoyaltyProgram;
use App\Models\LoyaltyTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoyaltyProgramController extends Controller
{
    private const STATUS_OPTIONS = ['draft', 'active', 'inactive', 'expired'];

    public function index(Request $request): Response
    {
        $user = $request->user();

        $programs = LoyaltyProgram::query()
            ->where('business_user_id', $user->id)
            ->withCount(['cards'])
            ->withCount(['cards as completed_cards_count' => fn ($q) => $q->whereIn('status', ['completed', 'redeemed'])])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (LoyaltyProgram $program): array => [
                'id' => $program->id,
                'name' => $program->name,
                'description' => $program->description,
                'stamps_required' => (int) $program->stamps_required,
                'reward' => $program->reward,
                'start_date' => optional($program->start_date)->toDateString(),
                'expires_at' => optional($program->expires_at)->toDateString(),
                'status' => $program->status,
                'cards_count' => (int) $program->cards_count,
                'completed_cards_count' => (int) $program->completed_cards_count,
            ])
            ->values();

        $selectedProgramId = (string) $request->query('program_id', ($programs->first()['id'] ?? ''));

        $selectedProgram = $programs->firstWhere('id', $selectedProgramId);
        $enrolledCards = [];
        $transactions = [];

        if ($selectedProgram) {
            $enrolledCards = LoyaltyProgram::query()
                ->where('id', $selectedProgramId)
                ->where('business_user_id', $user->id)
                ->firstOrFail()
                ->cards()
                ->with(['customer:id,name,email'])
                ->latest('updated_at')
                ->get()
                ->map(fn ($card): array => [
                    'id' => $card->id,
                    'customer_name' => $card->customer?->name,
                    'customer_email' => $card->customer?->email,
                    'stamps_current' => (int) $card->stamps_current,
                    'stamps_required' => (int) $card->stamps_required,
                    'status' => $card->status,
                    'completed_at' => optional($card->completed_at)->toIso8601String(),
                    'redeemed_at' => optional($card->redeemed_at)->toIso8601String(),
                    'created_at' => optional($card->created_at)->toIso8601String(),
                ])
                ->values()
                ->all();

            $transactions = LoyaltyTransaction::query()
                ->where('program_id', $selectedProgramId)
                ->latest('created_at')
                ->limit(200)
                ->get()
                ->map(fn (LoyaltyTransaction $tx): array => [
                    'id' => $tx->id,
                    'card_id' => $tx->card_id,
                    'action' => $tx->action,
                    'stamp_delta' => (int) $tx->stamp_delta,
                    'stamps_before' => (int) $tx->stamps_before,
                    'stamps_after' => (int) $tx->stamps_after,
                    'meta' => $tx->meta,
                    'created_at' => optional($tx->created_at)->toIso8601String(),
                ])
                ->values()
                ->all();
        }

        return Inertia::render('Modules/LoyaltyPrograms', [
            'programs' => $programs->all(),
            'selectedProgramId' => $selectedProgramId !== '' ? $selectedProgramId : null,
            'enrolledCards' => $enrolledCards,
            'transactions' => $transactions,
            'statusOptions' => self::STATUS_OPTIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:255'],
            'stamps_required' => ['required', 'integer', 'min:1', 'max:200'],
            'reward' => ['required', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'status' => ['required', 'in:' . implode(',', self::STATUS_OPTIONS)],
        ]);

        LoyaltyProgram::query()->create([
            'business_user_id' => $user->id,
            ...$validated,
        ]);

        return back()->with('status', 'Loyalty program created successfully.');
    }

    public function update(Request $request, LoyaltyProgram $loyaltyProgram): RedirectResponse
    {
        $user = $request->user();
        if ((int) $loyaltyProgram->business_user_id !== (int) $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:255'],
            'stamps_required' => ['required', 'integer', 'min:1', 'max:200'],
            'reward' => ['required', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'status' => ['required', 'in:' . implode(',', self::STATUS_OPTIONS)],
        ]);

        $loyaltyProgram->update($validated);

        return back()->with('status', 'Loyalty program updated successfully.');
    }

    public function destroy(Request $request, LoyaltyProgram $loyaltyProgram): RedirectResponse
    {
        $user = $request->user();
        if ((int) $loyaltyProgram->business_user_id !== (int) $user->id) {
            abort(403);
        }

        $loyaltyProgram->delete();

        return back()->with('status', 'Loyalty program deleted successfully.');
    }
}

