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

use Faker\Provider\Base;
use InvalidArgumentException;

final class RandomEnumProvider extends Base
{
    /**
     * @throws \Exception
     */
    public static function randomEnum(string $enum): \UnitEnum
    {
        if (!is_a($enum, \UnitEnum::class, true)) {
            throw new InvalidArgumentException(sprintf('"%s" is not a proper enum class', $enum));
        }

        $cases = $enum::cases();

        return $cases[random_int(0, \count($cases) - 1)]; /** @phpstan-ignore-line */
    }
}
