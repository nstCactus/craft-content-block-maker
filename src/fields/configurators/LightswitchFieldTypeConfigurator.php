<?php

namespace modules\maker\fields\configurators;

use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\MenuItem\CheckboxItem;

class LightswitchFieldTypeConfigurator extends AbstractFieldTypeConfigurator
{
    protected bool $isCheckedByDefault = false;

    public function getTypeSettings(string $name, string $handle): array
    {
        return array_replace_recursive(parent::getTypeSettings($name, $handle), [
            'required' => false,
            'searchable' => false,
            'typeSettings' => [
                'value' => $this->isCheckedByDefault,
            ],
        ]);
    }

    protected function getTypeSettingsMenu(): CliMenu
    {
        $menu = parent::getTypeSettingsMenu();
        foreach ($menu->getItems() as $item) {
            if ($item->getText() === 'This field is required') {
                $menu->removeItem($item);
            }
        }
        $menu->addItem(new CheckboxItem('Checked by default', function() { $this->isCheckedByDefault = true; }));
        return $menu;
    }

    public static function displayName(): string
    {
        return 'Lightswitch';
    }
}
