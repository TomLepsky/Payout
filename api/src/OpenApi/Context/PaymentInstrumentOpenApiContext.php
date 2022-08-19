<?php

namespace App\OpenApi\Context;

class PaymentInstrumentOpenApiContext
{
    public const POST_INSTRUMENT = [
        'summary'     => 'Create a payment instrument',
        'requestBody' => [
            'content' => [
                'application/json' => [
                    'schema'  => [
                        'type' => 'object',
                        'properties' => [
                            'type' => [
                                'type' => 'string',
                                'enum' => ['card']
                            ],
                            'title' => [
                                'type' => 'string',
                                'maxLength' => 255
                            ],
                            'firstName' => [
                                'type' => 'string',
                                'minLength' => 2,
                                'maxLength' => 21,
                                'pattern' => '/^[a-zA-Z]{2,21}$/',
                                'required' => true
                            ],
                            'lastName' => [
                                'type' => 'string',
                                'minLength' => 2,
                                'maxLength' => 21,
                                'pattern' => '/^[a-zA-Z]{2,21}$/',
                                'required' => true
                            ],
                            'number' => [
                                'type' => 'integer',
                                'pattern' => '/^[\d]{13}$|^[\d]{16}$|^[\d]{18,19}$/',
                                'required' => true
                            ],
                            'expiryMonth' => [
                                'type' => 'integer',
                                'minimum' => 1,
                                'maximum' => 12,
                                'required' => true
                            ],
                            'expiryYear' => ['type' => 'integer', 'required' => true],
                            'note' => [
                                'type' => 'string',
                                'maxLength' => 255
                            ],
                        ],
                        'example' => [
                            'type' => 'card',
                            'title' => 'Awesome title!',
                            'firstName' => 'Tom',
                            'lastName' => 'Tom',
                            'number' => 4002931234567895,
                            'expiryMonth' => 7,
                            'expiryYear' => 29,
                            'note' => 'Awesome note!',
                        ],
                        'required' => [
                            'firstName',
                            'lastName',
                            'number',
                            'expiryMonth',
                            'expiryYear'
                        ]
                    ],
                ],
            ],
        ],
    ];

}
