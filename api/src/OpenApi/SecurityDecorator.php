<?php

namespace App\OpenApi;

use ApiPlatform\Core\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\Core\OpenApi\Model\Operation;
use ApiPlatform\Core\OpenApi\Model\PathItem;
use ApiPlatform\Core\OpenApi\Model\RequestBody;
use ApiPlatform\Core\OpenApi\OpenApi;
use ArrayObject;

class SecurityDecorator implements OpenApiFactoryInterface
{
    public const SECURITY_TAG = 'Security';

    public function __construct(private OpenApiFactoryInterface $openApiFactory) {}

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->openApiFactory)($context);
        $schemas = $openApi->getComponents()->getSchemas();

        $schemas['Token'] = $this->createTokenSchema();
        $schemas['Credentials'] = $this->createCredentialsSchema();

        $paths = $openApi->getPaths();

        $paths->addPath('/api/login', $this->createLoginEndpoint());
        $paths->addPath('/api/logout', $this->createLogoutEndpoint());
        $paths->addPath('/api/check-auth', $this->createCheckAuthEndpoint());

        return $openApi;
    }

    private function createTokenSchema() : ArrayObject
    {
        return new ArrayObject([
            'type' => 'object',
            'properties' => [
                'token' => [
                    'type' => 'string',
                    'readOnly' => true,
                ],
            ],
        ]);
    }

    private function createCredentialsSchema() : ArrayObject
    {
        return new ArrayObject([
            'type' => 'object',
            'properties' => [
                'email' => [
                    'type' => 'string'
                ],
                'password' => [
                    'type' => 'string'
                ],
            ],
        ]);
    }

    private function createLoginEndpoint() : PathItem
    {
        return new PathItem(
            ref: 'Security',
            post: new Operation(
                operationId: 'postCredentialsItem',
                tags: [self::SECURITY_TAG],
                responses: [
                    '204' => [
                        'description' => 'Login'
                    ],
                    '401' => [
                        'description' => 'Bad credentials'
                    ]
                ],
                summary: 'Log in!',
                requestBody: new RequestBody(
                    description: 'Login',
                    content: new ArrayObject([
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/Credentials',
                            ],
                        ],
                    ]),
                ),
            ),
        );
    }

    private function createLogoutEndpoint() : PathItem
    {
        return new PathItem(
            ref: 'Security',
            get: new Operation(
                operationId: 'getLogout',
                tags: [self::SECURITY_TAG],
                responses: [
                    '204' => [
                        'description' => 'Successfully logout'
                    ],
                    '401' => [
                        'description' => 'Unauthorized'
                    ]
                ],
                summary: 'Get out!'
            )
        );
    }

    private function createCheckAuthEndpoint() : PathItem
    {
        return new PathItem(
            ref: 'Security',
            get: new Operation(
                operationId: 'getCheckAuth',
                tags: [self::SECURITY_TAG],
                responses: [
                '204' => [
                    'description' => 'User logged'
                ],
                '401' => [
                    'description' => 'Unauthorized'
                ]
            ],
                summary: 'Check user auth'
            )
        );
    }

}
