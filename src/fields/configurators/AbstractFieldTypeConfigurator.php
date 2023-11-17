<?php

namespace modules\maker\fields\configurators;

use PhpSchool\CliMenu\Action\ExitAction;
use PhpSchool\CliMenu\Builder\CliMenuBuilder;
use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\MenuItem\SelectableItem;

abstract class AbstractFieldTypeConfigurator implements FieldTypeConfiguratorInterface
{
    protected bool $isRequired = false;
    protected string $instructions = '';

    public function getTypeSettings(string $name, string $handle): array
    {
        $menu = $this->getTypeSettingsMenu();
        $menu->addItem(new SelectableItem('Done', new ExitAction()));
        $menu->open();

        return [
            'required' => $this->isRequired,
            'instructions' => $this->instructions,
            'searchable' => true,
            'translationMethod' => 'none',
            'typeSettings' => [],
        ];
    }

    protected function getTypeSettingsMenu(): CliMenu
    {
        return (new CliMenuBuilder())
            ->disableDefaultItems()
            ->addItem('Add instructions', function (CliMenu $menu) {
                $this->instructions = $menu->askText()
                    ->setPromptText('Instructions: ')
                    ->ask()
                    ->fetch();
            })
            ->addCheckboxItem('This field is required', function () { $this->isRequired = true; })
            ->build();
    }
}
