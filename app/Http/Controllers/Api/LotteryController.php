<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\Lottery\LotteryException;
use App\Http\Controllers\Controller;
use App\Http\Resources\Lottery\EntryResource;
use App\Http\Resources\Lottery\LotteryResource;
use App\Http\Resources\Lottery\MyStatusResource;
use App\Models\Lottery;
use App\Models\LotteryEntry;
use App\Models\LotteryWinner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class LotteryController extends Controller
{
    #[OA\Get(
        path: '/api/lotteries',
        tags: ['Lottery'],
        summary: 'List lotteries',
        description: 'Get paginated list of active lotteries',
        parameters: [
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 10, minimum: 1, maximum: 100)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lotteries list',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/LotteryResource')
                        ),
                        new OA\Property(
                            property: 'links',
                            type: 'object'
                        ),
                        new OA\Property(
                            property: 'meta',
                            type: 'object'
                        ),
                    ]
                )
            ),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min($request->integer('per_page', 10), 100);

        $lotteries = Lottery::query()
            ->active()
            ->latest()
            ->paginate($perPage);

        return LotteryResource::collection($lotteries);
    }

    #[OA\Get(
        path: '/api/lotteries/{lottery}',
        tags: ['Lottery'],
        summary: 'Show lottery details',
        description: 'Get detailed information about a specific lottery',
        parameters: [
            new OA\Parameter(
                name: 'lottery',
                in: 'path',
                required: true,
                description: 'Lottery ID',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lottery details',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/LotteryResource'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Lottery not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Lottery not found'),
                    ]
                )
            ),
        ]
    )]
    public function show(Lottery $lottery): LotteryResource
    {
        return new LotteryResource($lottery);
    }

    #[OA\Post(
        path: '/api/lotteries/{lottery}/register',
        tags: ['Lottery'],
        summary: 'Register current user in lottery',
        description: 'Register the authenticated user for a specific lottery',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'lottery',
                in: 'path',
                required: true,
                description: 'Lottery ID',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Registered successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Successfully registered'),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/EntryResource'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'User not authenticated'),
                    ]
                )
            ),
            new OA\Response(
                response: 409,
                description: 'Already registered or invalid state',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'You have already registered for this lottery'),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Lottery is not active'),
                        new OA\Property(property: 'errors', type: 'object'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Lottery capacity has been reached'),
                    ]
                )
            ),
        ]
    )]
    public function register(Request $request, Lottery $lottery): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            LotteryException::unauthenticated();
        }

        if ($lottery->status !== 'active') {
            LotteryException::lotteryNotActive();
        }

        if ($lottery->starts_at && now()->lt($lottery->starts_at)) {
            LotteryException::registrationNotStarted();
        }

        if ($lottery->ends_at && now()->gt($lottery->ends_at)) {
            LotteryException::registrationEnded();
        }

        $alreadyRegistered = LotteryEntry::query()
            ->where('lottery_id', $lottery->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyRegistered) {
            LotteryException::alreadyRegistered();
        }

        if ($lottery->capacity !== null) {
            $entriesCount = LotteryEntry::query()
                ->where('lottery_id', $lottery->id)
                ->count();

            if ($entriesCount >= $lottery->capacity) {
                LotteryException::capacityReached();
            }
        }

        $entry = LotteryEntry::create([
            'lottery_id' => $lottery->id,
            'user_id' => $user->id,
            'registered_at' => now(),
        ]);

        $entry->load(['user', 'lottery']);

        return response()->json([
            'message' => 'Successfully registered',
            'data' => new EntryResource($entry),
        ]);
    }

    #[OA\Get(
        path: '/api/lotteries/{lottery}/my-status',
        tags: ['Lottery'],
        summary: 'Get current user lottery status',
        description: 'Get the authenticated user\'s registration status and winner status for a lottery',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'lottery',
                in: 'path',
                required: true,
                description: 'Lottery ID',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Status returned',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/MyStatusResource'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 404,
                description: 'Lottery not found'
            ),
        ]
    )]
    public function myStatus(Request $request, Lottery $lottery): MyStatusResource
    {
        $user = $request->user();

        $entry = LotteryEntry::query()
            ->with(['user', 'lottery'])
            ->where('lottery_id', $lottery->id)
            ->where('user_id', $user->id)
            ->first();

        $winner = LotteryWinner::query()
            ->where('lottery_id', $lottery->id)
            ->where('user_id', $user->id)
            ->first();

        return new MyStatusResource([
            'entry' => $entry,
            'winner' => $winner,
        ]);
    }

    #[OA\Get(
        path: '/api/my/lotteries',
        tags: ['Lottery'],
        summary: 'List current user lottery participations',
        description: 'Get paginated list of lotteries the authenticated user has participated in',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 15, minimum: 1, maximum: 100)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'My lotteries',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/EntryResource')
                        ),
                        new OA\Property(
                            property: 'links',
                            type: 'object'
                        ),
                        new OA\Property(
                            property: 'meta',
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
        ]
    )]
    public function myLotteries(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $entries = LotteryEntry::query()
            ->with(['lottery'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(15);

        return EntryResource::collection($entries);
    }
}