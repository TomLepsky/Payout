<?php

namespace App;

class Config
{
    public const USER = 'ROLE_USER';
    public const ACCOUNTANT = 'ROLE_ACCOUNTANT';
    public const CHIEF_ACCOUNTANT = 'ROLE_CHIEF_ACCOUNTANT';
    public const ADMIN = 'ROLE_ADMIN';
    public const AUTHENTICATED = 'IS_AUTHENTICATED_FULLY';

    public const JWT_COOKIE_NAME = 'biscuit';

    public const AVAILABLE_CURRENCIES = ['RUB', 'USD', 'EUR', 'UAH'];

    public const CURRENCY_LIMIT = [
        'RUB' => 10_000_000, //100k
        'USD' => 140_000,
        'EUR' => 120_000,
        'UAH' => 3_600_000
    ];
}
