<?php

namespace modules\maker\fields\configurators;

use craft\fields\PlainText;
use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\MenuItem\CheckboxItem;

class PlainTextFieldTypeConfigurator extends AbstractFieldTypeConfigurator
{
    protected bool $isEnlarged = false;
    protected bool $isMultiline = false;

    public static function displayName(): string
    {
        return 'Plain Text';
    }

    public static function fieldClassName(): string
    {
        return PlainText::class;
    }

    public function getTypeSettings(string $name, string $handle): array
    {
        return array_replace_recursive(parent::getTypeSettings($name, $handle), [
            'typesettings' => [
                'uiMode' => $this->isEnlarged ? 'enlarged' : 'normal',
                "placeholder" => "",
                "fieldLimit" => "",
                "limitUnit" => "chars",
                "code" => "",
                'multiline' => $this->isMultiline ? 1 : '',
                "initialRows" => "4",
                "columnType" => "auto",
            ],
        ]);
    }

    protected function getTypeSettingsMenu(): CliMenu
    {
        $menu = parent::getTypeSettingsMenu();
        $menu->addItem(new CheckboxItem('Display enlarged', function () {
            $this->isEnlarged = true;
        }));
        $menu->addItem(new CheckboxItem('Allow line breaks', function () {
            $this->isMultiline = true;
        }));

        return $menu;
    }
}
