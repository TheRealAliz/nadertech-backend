<?php

namespace App\Http\Controllers\Api\Admin;

use App\Exceptions\LotteryException;
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
        description: 'Get paginated list of all lotteries with optional status filter',
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
                schema: new OA\Schema(type: 'integer', example: 10, minimum: 1, maximum: 100)
            ),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                description: 'Filter by lottery status',
                schema: new OA\Schema(type: 'string', enum: ['draft', 'active', 'inactive', 'drawn'])
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
                            items: new OA\Items(ref: '#/components/schemas/AdminLotteryListResource')
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
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
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
        description: 'Get detailed information about a specific lottery with entries and winners',
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
                description: 'Lottery details',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/AdminLotteryResource'
                        )
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Lottery not found'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
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
        description: 'Create a new lottery',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'winner_count', 'status'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', maxLength: 255, example: 'قرعه کشی تابستان'),
                    new OA\Property(property: 'description', type: 'string', nullable: true, example: 'قرعه کشی ویژه کاربران فعال'),
                    new OA\Property(property: 'starts_at', type: 'string', format: 'date-time', nullable: true, example: '2026-06-08T10:00:00+03:30'),
                    new OA\Property(property: 'ends_at', type: 'string', format: 'date-time', nullable: true, example: '2026-06-15T23:59:59+03:30'),
                    new OA\Property(property: 'capacity', type: 'integer', nullable: true, example: 1000, minimum: 1),
                    new OA\Property(property: 'winner_count', type: 'integer', example: 3, minimum: 1),
                    new OA\Property(property: 'status', type: 'string', enum: ['draft', 'active', 'inactive'], example: 'active'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Lottery created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'قرعه‌کشی با موفقیت ایجاد شد.'),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/AdminLotteryResource'
                        )
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function store(StoreLotteryRequest $request): JsonResponse
    {
        $data = $request->validated();

        $lottery = Lottery::create($data);

        return response()->json([
            'message' => 'قرعه‌کشی با موفقیت ایجاد شد.',
            'data' => new LotteryResource($lottery),
        ], 201);
    }

    #[OA\Put(
        path: '/api/admin/lotteries/{lottery}',
        tags: ['Admin - Lottery'],
        summary: 'Update lottery',
        description: 'Update an existing lottery',
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
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string', maxLength: 255, example: 'قرعه کشی جدید'),
                    new OA\Property(property: 'description', type: 'string', nullable: true, example: 'توضیحات به‌روز شده'),
                    new OA\Property(property: 'starts_at', type: 'string', format: 'date-time', nullable: true, example: '2026-06-08T10:00:00+03:30'),
                    new OA\Property(property: 'ends_at', type: 'string', format: 'date-time', nullable: true, example: '2026-06-15T23:59:59+03:30'),
                    new OA\Property(property: 'capacity', type: 'integer', nullable: true, example: 1000, minimum: 1),
                    new OA\Property(property: 'winner_count', type: 'integer', example: 1, minimum: 1),
                    new OA\Property(property: 'status', type: 'string', enum: ['draft', 'active', 'inactive'], example: 'active'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lottery updated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'قرعه‌کشی با موفقیت ویرایش شد.'),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/AdminLotteryResource'
                        )
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Lottery not found'),
        ]
    )]
    public function update(UpdateLotteryRequest $request, Lottery $lottery): JsonResponse
    {
        $validated = $request->validated();

        $lottery->update($validated);

        return response()->json([
            'message' => 'قرعه‌کشی با موفقیت ویرایش شد.',
            'data' => new LotteryResource($lottery->load(['entries', 'winners'])->fresh()),
        ]);
    }

    #[OA\Get(
        path: '/api/admin/lotteries/{lottery}/entries',
        tags: ['Admin - Lottery'],
        summary: 'List lottery entries',
        description: 'Get paginated list of all entries for a specific lottery',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'lottery',
                in: 'path',
                required: true,
                description: 'Lottery ID',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
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
                schema: new OA\Schema(type: 'integer', example: 50, minimum: 1, maximum: 100)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Entries list',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(property: 'lottery_id', type: 'integer'),
                                    new OA\Property(property: 'user_id', type: 'integer'),
                                    new OA\Property(property: 'registered_at', type: 'string', format: 'date-time'),
                                    new OA\Property(
                                        property: 'user',
                                        ref: '#/components/schemas/UserResource'
                                    ),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                                ]
                            )
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
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Lottery not found'),
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
        description: 'Get list of all winners for a specific lottery ordered by position',
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
                description: 'Winners list',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(property: 'lottery_id', type: 'integer'),
                                    new OA\Property(property: 'user_id', type: 'integer'),
                                    new OA\Property(property: 'position', type: 'integer'),
                                    new OA\Property(
                                        property: 'user',
                                        ref: '#/components/schemas/UserResource'
                                    ),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                                ]
                            )
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Lottery not found'),
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
        description: 'Select and save winners for a lottery. Only available for active lotteries after end date.',
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
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['winners'],
                properties: [
                    new OA\Property(
                        property: 'winners',
                        type: 'array',
                        description: 'Array of winners with user_id and position',
                        minItems: 1,
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'user_id', type: 'integer', example: 5),
                                new OA\Property(property: 'position', type: 'integer', example: 1),
                            ]
                        ),
                        example: [
                            ['user_id' => 5, 'position' => 1],
                            ['user_id' => 12, 'position' => 2],
                            ['user_id' => 8, 'position' => 3],
                        ]
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lottery drawn successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Lottery winners have been saved successfully.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(property: 'lottery_id', type: 'integer'),
                                    new OA\Property(property: 'user_id', type: 'integer'),
                                    new OA\Property(property: 'position', type: 'integer'),
                                    new OA\Property(
                                        property: 'user',
                                        ref: '#/components/schemas/UserResource'
                                    ),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                                ]
                            )
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 409,
                description: 'Conflict - Invalid lottery state',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Lottery has already been drawn'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Winners list is required'),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'errors', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Lottery not found'),
        ]
    )]
    public function draw(Request $request, Lottery $lottery): JsonResponse
    {
        if ($lottery->status === 'drawn') {
            LotteryException::alreadyDrawn();
        }

        if ($lottery->status === 'draft') {
            LotteryException::invalidDrawStatus();
        }

        if ($lottery->ends_at && now()->lt($lottery->ends_at)) {
            LotteryException::drawNotAvailableYet();
        }

        $winners = $request->input('winners');

        if (!is_array($winners) || empty($winners)) {
            LotteryException::winnersRequired();
        }

        if (count($winners) > $lottery->winner_count) {
            LotteryException::tooManyWinners();
        }

        return DB::transaction(function () use ($lottery, $winners) {

            LotteryWinner::query()
                ->where('lottery_id', $lottery->id)
                ->delete();

            foreach ($winners as $winner) {
                LotteryWinner::create([
                    'lottery_id' => $lottery->id,
                    'user_id' => $winner['user_id'],
                    'position' => $winner['position'],
                ]);
            }

            $lottery->update([
                'status' => 'drawn',
                'drawn_at' => now(),
            ]);

            return response()->json([
                'message' => 'Lottery winners have been saved successfully.',
                'data' => LotteryWinner::query()
                    ->with('user')
                    ->where('lottery_id', $lottery->id)
                    ->orderBy('position')
                    ->get(),
            ]);
        });
    }
}