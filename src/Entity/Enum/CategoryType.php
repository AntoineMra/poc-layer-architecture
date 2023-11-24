<?php

namespace App\Entity\Enum;

enum CategoryType: string
{
    case Expense = 'expense';
    case Income = 'income';
}
