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
        new OA\Property(property: "avatar", type: "string", example: "/avatars/avatar_5_1734567890.webp"),
        new OA\Property(property: "mobile_verified_at", type: "string", format: "date-time", nullable: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
    ],
    type: "object"
)]
#[OA\Schema(
    schema: "PageItemResource",
    title: "Page Item Resource",
    description: "Page item resource representation",
    properties: [
        new OA\Property(property: "key", type: "string", example: "title", description: "Item key identifier"),
        new OA\Property(property: "value", type: "string", example: "نادر تکنولوژی فقط یک نام نیست؛ یک نگاه است.", description: "Item content value"),
        new OA\Property(property: "type", type: "string", enum: ["text", "html", "image_path", "json", "number", "boolean"], example: "text", description: "Content type"),
        new OA\Property(property: "page", type: "string", example: "about", description: "Page name"),
    ],
    type: "object"
)]
class OpenApiSpec
{
}
