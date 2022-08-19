<?php

namespace App\OpenApi;

use ApiPlatform\Core\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\Core\OpenApi\Model\Operation;
use ApiPlatform\Core\OpenApi\Model\PathItem;
use ApiPlatform\Core\OpenApi\Model\RequestBody;
use ApiPlatform\Core\OpenApi\OpenApi;
use ArrayObject;

class PaymentDecorator implements OpenApiFactoryInterface
{
    public const CARD_PAYOUT_TAG = 'Card Payout';

    public function __construct(private OpenApiFactoryInterface $openApiFactory) {}

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->openApiFactory)($context);
        $schemas = $openApi->getComponents()->getSchemas();

        $schemas['CardPayout'] = $this->createCardPayoutRequestSchema();

        $paths = $openApi->getPaths();
        $paths->addPath('/api/card-payout', $this->createCardPayoutEndpoint());

        return $openApi;
    }

    private function createCardPayoutRequestSchema() : ArrayObject
    {
        return new ArrayObject([
            'type' => 'object',
            'properties' => [
                'number' => [
                    'type' => 'string',
                ],
                'expiryMonth' => [
                    'type' => 'integer',
                ],
                'expiryYear' => [
                    'type' => 'integer',
                ],
                'firstName' => [
                    'type' => 'string',
                ],
                'lastName' => [
                    'type' => 'string',
                ],
                'amount' => [
                    'type' => 'integer',
                ],
                'currency' => [
                    'type' => 'string',
                ],
                'note' => [
                    'type' => 'string',
                ],
            ],
        ]);
    }

    private function createCardPayoutEndpoint() : PathItem
    {
        return new PathItem(
            ref: 'CardPayout',
            post: new Operation(
                operationId: 'postCardPayoutItem',
                tags: [self::CARD_PAYOUT_TAG],
                responses: [
                    '204' => [
                        'description' => 'Payment created'
                    ]
                ],
                requestBody: new RequestBody(
                    description: 'Make payout via card',
                    content: new ArrayObject([
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/CardPayout',
                            ],
                        ],
                    ])
                )
            )
        );
    }
}
