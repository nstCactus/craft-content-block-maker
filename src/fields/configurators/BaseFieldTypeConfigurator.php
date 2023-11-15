<?php

namespace modules\maker\fields\configurators;

use craft\helpers\Console;
use PhpSchool\CliMenu\Builder\CliMenuBuilder;
use PhpSchool\CliMenu\CliMenu;

class BaseFieldTypeConfigurator implements FieldTypeConfiguratorInterface
{
    public function getTypeSettings(): array
    {
        return [
            'required' => $this->isRequired(),
            'instructions' => $this->instructions(),
            'searchable' => true,
            'translationMethod' => 'none',
            'typeSettings' => [],
        ];
    }

    public function isRequired(): bool {
        return Console::confirm('Is this field required?');
    }

    public function instructions(): string {
        $instructions = '';

        if (Console::confirm('Do you want to add instructions?')) {
            $instructions = Console::prompt('Instructions: ');
        }

        return $instructions;
    }
}
