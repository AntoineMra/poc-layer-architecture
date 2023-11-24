<?php

namespace App\Entity\Enum;

enum GoalStatus: string
{
    case Draft = 'draft';
    case Delay = 'delay';
    case Abandoned = 'abandoned';
    case Completed = 'completed';
}
