<?php

namespace modules\maker\helpers;

use craft\helpers\Console as CraftConsole;

class Console extends CraftConsole
{
    /**
     * Same as parent prompt method but colorizes the prompt text in yellow.
     */
    public static function prompt($text, $options = []): string
    {
        return parent::prompt(CraftConsole::ansiFormat($text, [self::FG_YELLOW]), $options);
    }

    public static function outputSuccess($text): bool|int
    {
        return self::output(self::ansiFormat($text, [self::FG_GREEN]));
    }
}
