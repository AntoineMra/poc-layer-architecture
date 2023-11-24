<?php

namespace App\DataFixtures\Provider;

use Faker\Provider\Base;

class EnumProvider extends Base
{
    public static function enum(string $enumValueShortcut): \UnitEnum
    {
        [$enumClassAlias, $constants] = explode('::', $enumValueShortcut);

        $constants = explode('|', $constants);

        // Flagged Enum if $constants count is greater than one:
        if (\count($constants) > 1) {

            $value = 0;

            foreach ($constants as $constant) {
                $value |= \constant($enumClassAlias.'::'.$constant);
            }
        } else {
            $value = \constant($enumClassAlias.'::'.current($constants));
        }

        return $value;
    }
}
