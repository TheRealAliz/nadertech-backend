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

class OpenApiSpec
{
}
