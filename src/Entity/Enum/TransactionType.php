<?php

namespace App\Entity\Enum;

enum TransactionType: string
{
    case Expense = 'expense';
    case Income = 'income';
}
