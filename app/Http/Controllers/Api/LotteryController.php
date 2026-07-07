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
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class LotteryController extends Controller
{
    #[OA\Get(
        path: '/api/lotteries',
        tags: ['Lottery'],
        summary: 'List lotteries',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lotteries list'
            )
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
        parameters: [
            new OA\Parameter(
                name: 'lottery',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lottery details'),
            new OA\Response(response: 404, description: 'Lottery not found'),
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
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'lottery',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Registered successfully'),
            new OA\Response(response: 409, description: 'Already registered or invalid state'),
            new OA\Response(response: 422, description: 'Validation error'),
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
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'lottery',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Status returned')
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
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'My lotteries')
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

