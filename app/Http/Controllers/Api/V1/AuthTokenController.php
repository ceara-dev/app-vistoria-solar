<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IssueTokenRequest;
use App\Http\Resources\Api\V1\ApiSessionResource;
use App\Models\User;
use App\Services\AuthTokenService;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Login e logout do app.
 */
class AuthTokenController extends Controller
{
    public function __construct(
        protected AuthTokenService $service,
    ) {}

    public function store(IssueTokenRequest $request): JsonResponse
    {
        $session = $this->service->issueToken(
            $request->validated('email'),
            $request->validated('password'),
            $request->validated('device_name'),
            $request->validated('tenant_id'),
        );

        return (new ApiSessionResource($session))->response()->setStatusCode(201);
    }

    public function destroy(#[CurrentUser] User $user): Response
    {
        $this->service->revokeCurrentToken($user);

        return response()->noContent();
    }
}
