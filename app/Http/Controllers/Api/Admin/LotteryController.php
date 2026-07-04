<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Lottery\StoreLotteryRequest;
use App\Http\Requests\Admin\Lottery\UpdateLotteryRequest;
use App\Http\Resources\Admin\Lottery\LotteryListResource;
use App\Http\Resources\Admin\Lottery\LotteryResource;
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
        path: '/api/admin/lotteries',
        tags: ['Admin - Lottery'],
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

        $lotteries = Lottery::latest();

        if ($request->filled('status')) {
            $lotteries->where('status', $request->string('status')->toString());
        }

        $lotteries->paginate($perPage);

        return LotteryListResource::collection($lotteries);
    }

    #[OA\Get(
        path: '/api/admin/lotteries/{lottery}',
        tags: ['Admin - Lottery'],
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
        $lottery->load(['entries', 'winners']);

        return new LotteryResource($lottery);
    }

    #[OA\Post(
        path: '/api/admin/lotteries',
        tags: ['Admin - Lottery'],
        summary: 'Create lottery',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'winner_count', 'status'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'قرعه کشی تابستان'),
                    new OA\Property(property: 'description', type: 'string', example: 'قرعه کشی ویژه کاربران'),
                    new OA\Property(property: 'starts_at', type: 'string', format: 'date-time', example: '2026-06-08T10:00:00+03:30'),
                    new OA\Property(property: 'ends_at', type: 'string', format: 'date-time', example: '2026-06-15T23:59:59+03:30'),
                    new OA\Property(property: 'capacity', type: 'integer', example: 1000, nullable: true),
                    new OA\Property(property: 'winner_count', type: 'integer', example: 3),
                    new OA\Property(property: 'status', type: 'string', example: 'active'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Lottery created'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreLotteryRequest $request): JsonResponse
    {
        $data = $request->validated();

        $lottery = Lottery::create($data);

        return response()->json([
            'message' => 'قرعه‌کشی با موفقیت ایجاد شد.',
            'lottery' => new LotteryResource($lottery),
        ], 201);
    }

    #[OA\Put(
        path: '/api/admin/lotteries/{lottery}',
        tags: ['Admin - Lottery'],
        summary: 'Update lottery',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'lottery',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'قرعه کشی جدید'),
                    new OA\Property(property: 'description', type: 'string'),
                    new OA\Property(property: 'starts_at', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'ends_at', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'capacity', type: 'integer', nullable: true),
                    new OA\Property(property: 'winner_count', type: 'integer', example: 1),
                    new OA\Property(property: 'status', type: 'string', example: 'active'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Lottery updated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateLotteryRequest $request, Lottery $lottery): JsonResponse
    {
        $validated = $request->validated();

        $lottery->update($validated);

        return response()->json([
            'message' => 'قرعه‌کشی با موفقیت ویرایش شد.',
            'lottery' => new LotteryResource($lottery->load(['entries', 'winners'])->fresh()),
        ]);
    }

    #[OA\Get(
        path: '/api/admin/lotteries/{lottery}/entries',
        tags: ['Admin - Lottery'],
        summary: 'List lottery entries',
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
            new OA\Response(response: 200, description: 'Entries list')
        ]
    )]
    public function entries(Lottery $lottery): JsonResponse
    {
        $entries = LotteryEntry::query()
            ->with('user')
            ->where('lottery_id', $lottery->id)
            ->latest()
            ->paginate(50);

        return response()->json($entries);
    }

    #[OA\Get(
        path: '/api/admin/lotteries/{lottery}/winners',
        tags: ['Admin - Lottery'],
        summary: 'List lottery winners',
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
            new OA\Response(response: 200, description: 'Winners list')
        ]
    )]
    public function winners(Lottery $lottery): JsonResponse
    {
        $winners = LotteryWinner::query()
            ->with('user')
            ->where('lottery_id', $lottery->id)
            ->orderBy('position')
            ->get();

        return response()->json($winners);
    }


    #[OA\Post(
        path: '/api/admin/lotteries/{lottery}/draw',
        tags: ['Admin - Lottery'],
        summary: 'Draw lottery winners',
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
            new OA\Response(response: 200, description: 'Lottery drawn'),
            new OA\Response(response: 409, description: 'Invalid lottery state'),
        ]
    )]
    public function draw(Lottery $lottery): JsonResponse
    {
        if ($lottery->status === 'drawn') {
            return response()->json([
                'message' => 'این قرعه‌کشی قبلاً انجام شده است.',
            ], 409);
        }

        if ($lottery->status === 'draft') {
            return response()->json([
                'message' => 'قرعه‌کشی در وضعیت draft قابل اجرا نیست.',
            ], 409);
        }

        if ($lottery->ends_at && now()->lt($lottery->ends_at)) {
            return response()->json([
                'message' => 'هنوز زمان پایان قرعه‌کشی نرسیده است.',
            ], 409);
        }

        return DB::transaction(function () use ($lottery) {
            LotteryWinner::query()
                ->where('lottery_id', $lottery->id)
                ->delete();

            $entries = LotteryEntry::query()
                ->where('lottery_id', $lottery->id)
                ->inRandomOrder()
                ->limit($lottery->winner_count)
                ->get();

            if ($entries->isEmpty()) {
                return response()->json([
                    'message' => 'هیچ شرکت‌کننده‌ای برای این قرعه‌کشی وجود ندارد.',
                ], 409);
            }

            foreach ($entries->values() as $index => $entry) {
                LotteryWinner::query()->create([
                    'lottery_id' => $lottery->id,
                    'user_id' => $entry->user_id,
                    'position' => $index + 1,
                ]);
            }

            $lottery->update([
                'status' => 'drawn',
                'drawn_at' => now(),
            ]);

            $winners = LotteryWinner::query()
                ->with('user')
                ->where('lottery_id', $lottery->id)
                ->orderBy('position')
                ->get();

            return response()->json([
                'message' => 'قرعه‌کشی با موفقیت انجام شد.',
                'winners' => $winners,
            ]);
        });
    }
}
