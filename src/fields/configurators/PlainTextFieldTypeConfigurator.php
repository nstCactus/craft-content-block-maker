<?php

namespace modules\maker\fields\configurators;

use PhpSchool\CliMenu\Builder\CliMenuBuilder;
use PhpSchool\CliMenu\CliMenu;

class PlainTextFieldTypeConfigurator extends BaseFieldTypeConfigurator
{
    public function getTypeSettings(): array
    {
        return array_merge(parent::getTypeSettings(), []);
    }
}
