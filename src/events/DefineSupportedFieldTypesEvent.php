<?php

namespace modules\maker\events;

use craft\base\Event;

class DefineSupportedFieldTypesEvent extends Event
{
    public array $supportedFieldTypes;
}
