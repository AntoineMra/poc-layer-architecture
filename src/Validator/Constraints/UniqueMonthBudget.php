<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueMonthBudget extends Constraint
{
    public string $message = 'The budget must have a unique month, A budget already exist for this date.';
}
