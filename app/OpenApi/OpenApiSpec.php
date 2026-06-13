<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Work3 API Documentation',
    version: '1.0.0',
    description: 'API documentation for Work3 Laravel project'
)]
#[OA\Server(
    url: 'http://127.0.0.1:8000',
    description: 'Local development server'
)]
#[OA\Tag(
    name: 'Auth',
    description: 'Authentication APIs'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Token'
)]
#[OA\Schema(
    schema: "UserResource",
    title: "User Resource",
    description: "User resource representation",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "full_name", type: "string", example: "علی احمدی"),
        new OA\Property(property: "username", type: "string", example: "ali_ahmadi"),
        new OA\Property(property: "email", type: "string", format: "email", example: "ali@example.com"),
        new OA\Property(property: "mobile", type: "string", example: "09123456789"),
        new OA\Property(property: "mobile_verified_at", type: "string", format: "date-time", nullable: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
    ],
    type: "object"
)]
class OpenApiSpec
{
}
