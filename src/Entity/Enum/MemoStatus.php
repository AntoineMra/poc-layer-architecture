<?php

namespace App\Entity\Enum;

enum MemoStatus: string
{
    case Ongoing = 'ongoing';
    case Closed = 'closed';
}
