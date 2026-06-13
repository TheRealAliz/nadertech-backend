<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lottery;
use App\Models\LotteryEntry;
use App\Models\LotteryWinner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
    public function index(Request $request): JsonResponse
    {
        $query = Lottery::query()->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $lotteries = $query->paginate(15);

        return response()->json($lotteries);
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
    public function show(Lottery $lottery): JsonResponse
    {
        $lottery->loadCount(['entries', 'winners']);

        return response()->json($lottery);
    }

    #[OA\Post(
        path: '/api/admin/lotteries',
        tags: ['Lottery'],
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
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'winner_count' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:draft,active,closed,drawn'],
        ]);

        $lottery = Lottery::query()->create($validated);

        return response()->json([
            'message' => 'قرعه‌کشی با موفقیت ایجاد شد.',
            'lottery' => $lottery,
        ], 201);
    }

    #[OA\Put(
        path: '/api/admin/lotteries/{lottery}',
        tags: ['Lottery'],
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
    public function update(Request $request, Lottery $lottery): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'winner_count' => ['sometimes', 'required', 'integer', 'min:1'],
            'status' => ['sometimes', 'required', 'in:draft,active,closed,drawn'],
        ]);

        $lottery->update($validated);

        return response()->json([
            'message' => 'قرعه‌کشی با موفقیت ویرایش شد.',
            'lottery' => $lottery->fresh(),
        ]);
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

        if (! $user) {
            return response()->json([
                'message' => 'کاربر احراز هویت نشده است.',
            ], 401);
        }

        if ($lottery->status !== 'active') {
            return response()->json([
                'message' => 'این قرعه‌کشی در حال حاضر فعال نیست.',
            ], 409);
        }

        if ($lottery->starts_at && now()->lt($lottery->starts_at)) {
            return response()->json([
                'message' => 'زمان ثبت‌نام این قرعه‌کشی هنوز شروع نشده است.',
            ], 409);
        }

        if ($lottery->ends_at && now()->gt($lottery->ends_at)) {
            return response()->json([
                'message' => 'مهلت ثبت‌نام این قرعه‌کشی به پایان رسیده است.',
            ], 409);
        }

        $alreadyRegistered = LotteryEntry::query()
            ->where('lottery_id', $lottery->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyRegistered) {
            return response()->json([
                'message' => 'شما قبلاً در این قرعه‌کشی ثبت‌نام کرده‌اید.',
            ], 409);
        }

        if ($lottery->capacity !== null) {
            $entriesCount = LotteryEntry::query()
                ->where('lottery_id', $lottery->id)
                ->count();

            if ($entriesCount >= $lottery->capacity) {
                return response()->json([
                    'message' => 'ظرفیت این قرعه‌کشی تکمیل شده است.',
                ], 409);
            }
        }

        $entry = LotteryEntry::query()->create([
            'lottery_id' => $lottery->id,
            'user_id' => $user->id,
            'registered_at' => now(),
        ]);

        return response()->json([
            'message' => 'ثبت‌نام شما در قرعه‌کشی با موفقیت انجام شد.',
            'entry' => $entry,
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
    public function myStatus(Request $request, Lottery $lottery): JsonResponse
    {
        $user = $request->user();

        $entry = LotteryEntry::query()
            ->where('lottery_id', $lottery->id)
            ->where('user_id', $user->id)
            ->first();

        $winner = LotteryWinner::query()
            ->where('lottery_id', $lottery->id)
            ->where('user_id', $user->id)
            ->first();

        return response()->json([
            'registered' => (bool) $entry,
            'is_winner' => (bool) $winner,
            'winner_position' => $winner?->position,
            'entry' => $entry,
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
    public function myLotteries(Request $request): JsonResponse
    {
        $user = $request->user();

        $entries = LotteryEntry::query()
            ->with(['lottery'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(15);

        return response()->json($entries);
    }

    #[OA\Get(
        path: '/api/admin/lotteries/{lottery}/entries',
        tags: ['Lottery'],
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

    #[OA\Post(
        path: '/api/admin/lotteries/{lottery}/draw',
        tags: ['Lottery'],
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

    #[OA\Get(
        path: '/api/admin/lotteries/{lottery}/winners',
        tags: ['Lottery'],
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
}

