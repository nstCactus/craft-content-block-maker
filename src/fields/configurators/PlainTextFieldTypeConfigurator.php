<?php

namespace modules\maker\fields\configurators;

use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\MenuItem\CheckboxItem;

class PlainTextFieldTypeConfigurator extends AbstractFieldTypeConfigurator
{
    protected bool $isEnlarged = false;
    protected bool $isMultiline = false;

    public function getTypeSettings(string $name, string $handle): array
    {
        return array_replace_recursive(parent::getTypeSettings($name, $handle), [
            'typeSettings' => [
                'uiMode' => $this->isEnlarged ? 'enlarged' : 'normal',
                'multiline' => $this->isMultiline,
            ],
        ]);
    }

    protected function getTypeSettingsMenu(): CliMenu
    {
        $menu = parent::getTypeSettingsMenu();
        $menu->addItem(new CheckboxItem('Display enlarged', function() { $this->isEnlarged = true; }));
        $menu->addItem(new CheckboxItem('Allow line breaks', function() { $this->isMultiline = true; }));

        return $menu;
    }

    public static function displayName(): string
    {
        return 'Plain Text';
    }
}
