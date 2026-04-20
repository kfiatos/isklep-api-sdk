<?php

declare(strict_types=1);

namespace ISklep\Api\Http;

enum Operation: string
{
    case List = 'list';
    case Get = 'get';
    case Create = 'create';
    case Update = 'update';
    case Delete = 'delete';
}
