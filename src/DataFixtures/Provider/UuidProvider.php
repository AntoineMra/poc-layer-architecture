<?php

declare(strict_types=1);

/*
 * This file is part of the April Marine project.
 *
 * Copyright (c) 2022 April Marine
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DataFixtures\Provider;

use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV6;

class UuidProvider
{
    public static function uuidV6(): UuidV6
    {
        return Uuid::v6();
    }

    public function uuidV6FromString(string $uuid): UuidV6
    {
        return UuidV6::fromString($uuid);
    }
}
