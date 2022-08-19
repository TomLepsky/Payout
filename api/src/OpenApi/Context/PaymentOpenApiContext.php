<?php

namespace App\OpenApi\Context;

use App\Config;

class PaymentOpenApiContext
{
    public const POST_PAYMENT_VIA_CARD = [
        'summary' => 'Create a payment via card',
        'requestBody' => [
            'content' => [
                'application/json' => [
                    'schema'  => [
                        'type' => 'object',
                        'properties' => [
                            'firstName' => [
                                'type' => 'string',
                                'minLength' => 1,
                                'maxLength' => 50,
                                'pattern' => '/^[a-zA-Z]{1,50}$/',
                                'required' => true
                            ],
                            'lastName' => [
                                'type' => 'string',
                                'minLength' => 1,
                                'maxLength' => 50,
                                'pattern' => '/^[a-zA-Z]{1,50}$/',
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
                            'amount' => ['type' => 'float', 'required' => true],
                            'currency' => [
                                'type' => 'string',
                                'enum' => Config::AVAILABLE_CURRENCIES,
                                'required' => true
                            ],
                            'note' => ['type' => 'string']
                        ],
                        'example' => [
                            'firstName' => 'Tom',
                            'lastName' => 'Tom',
                            'number' => 4002931234567895,
                            'expiryMonth' => 7,
                            'expiryYear' => 29,
                            'amount' => 21,
                            'currency' => 'RUB',
                            'note' => 'Awesome note!',
                        ],
                        'required' => [
                            'firstName',
                            'lastName',
                            'number',
                            'expiryMonth',
                            'expiryYear',
                            'amount',
                            'currency'
                        ]
                    ],
                ],
            ],
        ],
    ];

    public const POST_PAYMENT_VIA_SOURCE = [
        'summary' => 'Create a payment via source',
        'requestBody' => [
            'content' => [
                'application/json' => [
                    'schema'  => [
                        'type' => 'object',
                        'properties' => [
                            'source' => [
                                'type' => 'string',
                                'pattern' => '/^(src)_(\w{26})$/',
                                'required' => true,
                            ],
                            'firstName' => [
                                'type' => 'string',
                                'minLength' => 1,
                                'maxLength' => 50,
                                'pattern' => '/^[a-zA-Z]{1,50}$/',
                                'required' => true
                            ],
                            'lastName' => [
                                'type' => 'string',
                                'minLength' => 1,
                                'maxLength' => 50,
                                'pattern' => '/^[a-zA-Z]{1,50}$/',
                                'required' => true
                            ],
                            'amount' => ['type' => 'float', 'required' => true],
                            'currency' => [
                                'type' => 'string',
                                'enum' => Config::AVAILABLE_CURRENCIES,
                                'required' => true
                            ],
                            'note' => ['type' => 'string']
                        ],
                        'example' => [
                            'source' => 'src_woks33dx6hjuticbno6nlprqcy',
                            'firstName' => 'Tom',
                            'lastName' => 'Tom',
                            'amount' => 21,
                            'currency' => 'RUB',
                            'note' => 'Awesome note!',
                        ],
                        'required' => [
                            'source',
                            'firstName',
                            'lastName',
                            'amount',
                            'currency'
                        ]
                    ],
                ],
            ],
        ],
    ];
}
