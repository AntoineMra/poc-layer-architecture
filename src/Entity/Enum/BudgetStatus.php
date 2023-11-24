<?php

namespace App\Entity\Enum;

enum BudgetStatus: string
{
    case Draft = 'draft';
    case Completed = 'completed';
}