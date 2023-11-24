<?php

namespace App\Entity\Enum;

enum TransactionStatus: string
{
    case Draft = 'draft';
    case Validated = 'validated';
}