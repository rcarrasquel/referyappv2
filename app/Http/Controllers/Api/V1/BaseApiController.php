<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class BaseApiController extends Controller
{
    protected function requireApiUser(Request $request): User
    {
        /** @var User|null $user */
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        if (! in_array($user->role, ['business', 'admin'], true)) {
            abort(403, 'Only business or admin users can access this API.');
        }

        return $user;
    }

    protected function requireBusinessUser(Request $request): User
    {
        return $this->requireApiUser($request);
    }

    protected function requireBusinessOnly(Request $request): User
    {
        /** @var User|null $user */
        $user = $this->requireApiUser($request);

        if ($user->role !== 'business') {
            abort(403, 'Admin role has read-only access for this module.');
        }

        return $user;
    }

    protected function requireAdminUser(Request $request): User
    {
        /** @var User|null $user */
        $user = $this->requireApiUser($request);
        if ($user->role !== 'admin') {
            abort(403, 'Admin access required.');
        }

        return $user;
    }

    protected function ok(array $data = [], int $status = 200): JsonResponse
    {
        return response()->json(['data' => $data], $status);
    }

    protected function message(string $message, int $status = 200): JsonResponse
    {
        return response()->json(['message' => $message], $status);
    }
}
