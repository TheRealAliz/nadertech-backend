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
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "full_name", type: "string", example: "علی احمدی"),
        new OA\Property(property: "username", type: "string", example: "ali_ahmadi"),
        new OA\Property(property: "email", type: "string", format: "email", example: "ali@example.com"),
        new OA\Property(property: "mobile", type: "string", example: "09123456789"),
        new OA\Property(property: "birth_date", type: "string", format: "date", nullable: true, example: "2000-05-15"),
        new OA\Property(property: "national_code", type: "string", nullable: true, example: "1234567890"),
        new OA\Property(property: "postal_code", type: "string", nullable: true, example: "1234567890"),
        new OA\Property(property: "province", type: "string", nullable: true, example: "خراسان شمالی"),
        new OA\Property(property: "address", type: "string", nullable: true, example: "بجنورد، خیابان امام، پلاک ۱۲"),
        new OA\Property(property: "avatar", type: "string", nullable: true, example: "/avatars/avatar_5_1734567890.webp"),
        new OA\Property(property: "mobile_verified_at", type: "string", format: "date-time", nullable: true),
        new OA\Property(property: "email_verified_at", type: "string", format: "date-time", nullable: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time"),
    ]
)]
#[OA\Schema(
    schema: "AdminResource",
    title: "Admin Resource",
    description: "Admin resource representation",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "full_name", type: "string", example: "علی احمدی"),
        new OA\Property(property: "username", type: "string", example: "ali_ahmadi"),
        new OA\Property(property: "email", type: "string", format: "email", example: "ali@example.com"),
        new OA\Property(property: "mobile", type: "string", example: "09123456789"),
        new OA\Property(property: "avatar", type: "string", example: "/avatars/avatar_5_1734567890.webp"),
        new OA\Property(property: "is_active", type: "boolean", example: true),
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
#[OA\Schema(
    schema: "ProjectServiceResource",
    title: "Project Service Resource",
    description: "Project service resource representation with nested children",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "title", type: "string", example: "طراحی وب‌سایت"),
        new OA\Property(property: "slug", type: "string", example: "web-design"),
        new OA\Property(property: "description", type: "string", example: "طراحی و توسعه وب‌سایت‌های حرفه ای", nullable: true),
        new OA\Property(property: "sort_order", type: "integer", example: 1),
        new OA\Property(property: "is_active", type: "boolean", example: true),
        new OA\Property(
            property: "children",
            type: "array",
            description: "Nested child services",
            items: new OA\Items(ref: "#/components/schemas/ProjectServiceResource")
        ),
    ],
    type: "object"
)]
#[OA\Schema(
    schema: 'AdminBannerResource',
    title: 'Admin Banner Resource',
    description: 'Admin banner resource representation',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', nullable: true, example: 'بنر اصلی'),
        new OA\Property(property: 'image', type: 'string', example: 'http://localhost/storage/banners/banner-1.jpg'),
        new OA\Property(property: 'alt', type: 'string', nullable: true, example: 'توضیح تصویر'),
        new OA\Property(property: 'link', type: 'string', nullable: true, example: 'https://example.com'),
        new OA\Property(property: 'sort_order', type: 'integer', example: 1),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'datetime', example: '2026-06-20T10:30:00.000000Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'datetime', example: '2026-06-20T10:30:00.000000Z'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'BannerResource',
    title: 'Banner Resource',
    description: 'Banner resource representation',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', nullable: true, example: 'بنر اصلی'),
        new OA\Property(property: 'image', type: 'string', example: 'http://localhost/storage/banners/banner-1.jpg'),
        new OA\Property(property: 'alt', type: 'string', nullable: true, example: 'توضیح تصویر'),
        new OA\Property(property: 'link', type: 'string', nullable: true, example: 'https://example.com'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'AdminArticleResource',
    title: 'Admin Article Resource',
    description: 'Admin article resource representation',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'اولین مقاله'),
        new OA\Property(property: 'slug', type: 'string', example: 'first-article'),
        new OA\Property(property: 'content', type: 'string', example: 'متن مقاله...'),
        new OA\Property(property: 'thumbnail', type: 'string', nullable: true, example: 'http://nadertechnologyteam.ir/storage/images/articles/abc.jpg'),
        new OA\Property(property: 'thumbnail_alt', type: 'string', nullable: true, example: 'تصویر مقاله'),
        new OA\Property(property: 'meta_title', type: 'string', nullable: true, example: 'عنوان سئو'),
        new OA\Property(property: 'meta_description', type: 'string', nullable: true, example: 'توضیحات سئو'),
        new OA\Property(property: 'views_count', type: 'integer', example: 120),
        new OA\Property(property: 'status', type: 'string', description: 'Article status (draft, published, archived)', example: 'published'),
        new OA\Property(property: 'published_at', type: 'string', format: 'date-time', nullable: true, example: '2026-06-25T12:00:00.000000Z'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        new OA\Property(
            property: 'admin',
            ref: '#/components/schemas/AdminResource',
            nullable: true,
            description: 'Admin user who created/updated the article'
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'ArticleResource',
    title: 'Article Resource',
    description: 'Article resource representation',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'اولین مقاله'),
        new OA\Property(property: 'slug', type: 'string', example: 'first-article'),
        new OA\Property(property: 'content', type: 'string', example: 'متن مقاله...'),
        new OA\Property(property: 'thumbnail', type: 'string', nullable: true, example: 'http://nadertechnologyteam.ir/storage/images/articles/abc.jpg'),
        new OA\Property(property: 'thumbnail_alt', type: 'string', nullable: true, example: 'تصویر مقاله'),
        new OA\Property(property: 'views_count', type: 'integer', example: 120),
        new OA\Property(property: 'published_at', type: 'string', format: 'date-time', nullable: true, example: '2026-06-25T12:00:00.000000Z'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'ArticleListResource',
    title: 'Article List Resource',
    description: 'Article list resource representation',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'اولین مقاله'),
        new OA\Property(property: 'slug', type: 'string', example: 'first-article'),
        new OA\Property(property: 'thumbnail', type: 'string', nullable: true, example: 'http://nadertechnologyteam.ir/storage/images/articles/abc.jpg'),
        new OA\Property(property: 'thumbnail_alt', type: 'string', nullable: true, example: 'تصویر مقاله'),
        new OA\Property(property: 'views_count', type: 'integer', example: 120),
        new OA\Property(property: 'published_at', type: 'string', format: 'date-time', nullable: true, example: '2026-06-25T12:00:00.000000Z'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'ProjectRequestResource',
    title: 'Project Request Resource',
    description: 'Project request resource representation with service relationship',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'علی احمدی'),
        new OA\Property(property: 'mobile', type: 'string', example: '09123456789'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'ali@example.com'),
        new OA\Property(property: 'description', type: 'string', example: 'من به یک وب‌سایت فروشگاهی نیاز دارم'),
        new OA\Property(
            property: 'service',
            ref: '#/components/schemas/ProjectServiceResource',
            nullable: true,
            description: 'The requested service'
        ),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-06-25T10:00:00.000000Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-06-25T10:00:00.000000Z'),
    ],
    type: 'object'
)]
class OpenApiSpec
{
}
