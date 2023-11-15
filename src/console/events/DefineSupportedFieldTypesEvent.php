<?php

namespace modules\maker\console\events;

use craft\base\Event;

class DefineSupportedFieldTypesEvent extends Event
{
    public array $supportedFieldTypes;
}
