<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\LoyaltyCard;
use App\Models\LoyaltyProgram;
use App\Models\LoyaltyQrToken;
use App\Models\LoyaltyTransaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoyaltyController extends BaseApiController
{
    public function programs(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        $query = LoyaltyProgram::query()->where('status', 'active');

        if ($user->role === 'business') {
            $query->where('business_user_id', $user->id);
        }

        $items = $query
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (LoyaltyProgram $program): array => [
                'id' => $program->id,
                'business_user_id' => (int) $program->business_user_id,
                'name' => $program->name,
                'description' => $program->description,
                'stamps_required' => (int) $program->stamps_required,
                'reward' => $program->reward,
                'start_date' => optional($program->start_date)->toDateString(),
                'expires_at' => optional($program->expires_at)->toDateString(),
                'status' => $program->status,
            ])
            ->values()
            ->all();

        return $this->ok(['items' => $items]);
    }

    public function cards(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }
        if ($user->role !== 'customer') {
            abort(403, 'Customer access required.');
        }

        $items = LoyaltyCard::query()
            ->with(['program:id,name,reward,stamps_required'])
            ->where('customer_user_id', $user->id)
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (LoyaltyCard $card): array => [
                'id' => $card->id,
                'program_id' => $card->program_id,
                'program_name' => $card->program?->name,
                'reward' => $card->program?->reward,
                'stamps_current' => (int) $card->stamps_current,
                'stamps_required' => (int) $card->stamps_required,
                'status' => $card->status,
                'completed_at' => optional($card->completed_at)->toIso8601String(),
                'redeemed_at' => optional($card->redeemed_at)->toIso8601String(),
                'created_at' => optional($card->created_at)->toIso8601String(),
            ])
            ->values()
            ->all();

        return $this->ok(['items' => $items]);
    }

    public function issueQrToken(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }
        if ($user->role !== 'customer') {
            abort(403, 'Customer access required.');
        }

        $validated = $request->validate([
            'program_id' => ['required', 'string', 'exists:loyalty_programs,id'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        $program = LoyaltyProgram::query()
            ->where('id', $validated['program_id'])
            ->where('status', 'active')
            ->firstOrFail();

        $expiresAt = now()->addMinutes(2);
        $tokenValue = hash('sha256', Str::uuid() . '|' . $user->id . '|' . microtime(true));

        LoyaltyQrToken::query()
            ->where('program_id', $program->id)
            ->where('customer_user_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->update(['invalidated_at' => now()]);

        $token = LoyaltyQrToken::query()->create([
            'program_id' => $program->id,
            'customer_user_id' => $user->id,
            'token' => $tokenValue,
            'expires_at' => $expiresAt,
            'issued_device' => trim((string) ($validated['device_name'] ?? 'mobile')),
        ]);

        return $this->ok([
            'token' => $token->token,
            'program_id' => $program->id,
            'expires_at' => $expiresAt->toIso8601String(),
            'qr_payload' => [
                'type' => 'loyalty_stamp',
                'token' => $token->token,
                'program_id' => $program->id,
                'expires_at' => $expiresAt->toIso8601String(),
            ],
        ]);
    }

    public function scanAndStamp(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }
        if ($user->role !== 'business') {
            abort(403, 'Business access required.');
        }

        $validated = $request->validate([
            'token' => ['required', 'string', 'max:120'],
        ]);

        $payload = DB::transaction(function () use ($validated, $user): array {
            $token = LoyaltyQrToken::query()
                ->where('token', $validated['token'])
                ->lockForUpdate()
                ->first();

            if (! $token) {
                throw ValidationException::withMessages(['token' => 'Invalid QR token.']);
            }
            if ($token->used_at || $token->invalidated_at) {
                throw ValidationException::withMessages(['token' => 'QR token already used or invalidated.']);
            }
            if ($token->expires_at->lt(now())) {
                throw ValidationException::withMessages(['token' => 'QR token expired.']);
            }

            $program = LoyaltyProgram::query()
                ->where('id', $token->program_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $program->business_user_id !== (int) $user->id) {
                throw ValidationException::withMessages(['token' => 'This QR token is not for your business.']);
            }
            if ($program->status !== 'active') {
                throw ValidationException::withMessages(['token' => 'Program is not active.']);
            }

            $card = LoyaltyCard::query()
                ->where('program_id', $program->id)
                ->where('customer_user_id', $token->customer_user_id)
                ->lockForUpdate()
                ->first();

            $justCreated = false;
            if (! $card) {
                $card = LoyaltyCard::query()->create([
                    'program_id' => $program->id,
                    'business_user_id' => $user->id,
                    'customer_user_id' => $token->customer_user_id,
                    'stamps_current' => 0,
                    'stamps_required' => (int) $program->stamps_required,
                    'status' => 'active',
                ]);
                $justCreated = true;

                LoyaltyTransaction::query()->create([
                    'card_id' => $card->id,
                    'program_id' => $program->id,
                    'business_user_id' => $user->id,
                    'customer_user_id' => $token->customer_user_id,
                    'action' => 'card_created',
                    'stamp_delta' => 0,
                    'stamps_before' => 0,
                    'stamps_after' => 0,
                    'meta' => 'Created via QR scan',
                ]);
            }

            $before = (int) $card->stamps_current;
            $after = min((int) $card->stamps_required, $before + 1);
            $card->stamps_current = $after;

            if ($after >= (int) $card->stamps_required) {
                $card->status = 'completed';
                $card->completed_at = $card->completed_at ?: now();
            } elseif ($card->status === 'inactive') {
                $card->status = 'active';
            }

            $card->save();

            LoyaltyTransaction::query()->create([
                'card_id' => $card->id,
                'program_id' => $program->id,
                'business_user_id' => $user->id,
                'customer_user_id' => $token->customer_user_id,
                'action' => 'stamp_added',
                'stamp_delta' => 1,
                'stamps_before' => $before,
                'stamps_after' => $after,
                'meta' => 'Added via QR scan',
            ]);

            if ($after >= (int) $card->stamps_required && $before < (int) $card->stamps_required) {
                LoyaltyTransaction::query()->create([
                    'card_id' => $card->id,
                    'program_id' => $program->id,
                    'business_user_id' => $user->id,
                    'customer_user_id' => $token->customer_user_id,
                    'action' => 'card_completed',
                    'stamp_delta' => 0,
                    'stamps_before' => $after,
                    'stamps_after' => $after,
                    'meta' => 'Card completed',
                ]);
            }

            $token->used_at = now();
            $token->save();

            $customer = User::query()->find($token->customer_user_id);

            return [
                'program_id' => $program->id,
                'program_name' => $program->name,
                'card_id' => $card->id,
                'customer_id' => (int) $token->customer_user_id,
                'customer_name' => $customer?->name,
                'stamps_current' => (int) $card->stamps_current,
                'stamps_required' => (int) $card->stamps_required,
                'status' => $card->status,
                'reward_available' => $card->status === 'completed',
                'card_created' => $justCreated,
            ];
        });

        return $this->ok($payload);
    }

    public function redeem(Request $request, LoyaltyCard $card): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }
        if ($user->role !== 'business') {
            abort(403, 'Business access required.');
        }
        if ((int) $card->business_user_id !== (int) $user->id) {
            abort(403);
        }

        $updated = DB::transaction(function () use ($card, $user): LoyaltyCard {
            $card = LoyaltyCard::query()->where('id', $card->id)->lockForUpdate()->firstOrFail();

            if (! in_array($card->status, ['completed', 'redeemed'], true)) {
                throw ValidationException::withMessages([
                    'card' => 'Reward is not available yet.',
                ]);
            }

            $before = (int) $card->stamps_current;

            $card->status = 'redeemed';
            $card->redeemed_at = now();
            $card->save();

            LoyaltyTransaction::query()->create([
                'card_id' => $card->id,
                'program_id' => $card->program_id,
                'business_user_id' => $user->id,
                'customer_user_id' => $card->customer_user_id,
                'action' => 'reward_redeemed',
                'stamp_delta' => 0,
                'stamps_before' => $before,
                'stamps_after' => $before,
                'meta' => 'Redeemed by business',
            ]);

            return $card;
        });

        return $this->ok([
            'card_id' => $updated->id,
            'status' => $updated->status,
            'redeemed_at' => optional($updated->redeemed_at)->toIso8601String(),
        ]);
    }
}

