<?php

namespace modules\maker\fields\configurators;

use PhpSchool\CliMenu\Action\ExitAction;
use PhpSchool\CliMenu\Builder\CliMenuBuilder;
use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\MenuItem\SelectableItem;
use PhpSchool\CliMenu\MenuStyle;

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
            'instructions' => $this->instructions,
            'required' => $this->isRequired ? '1' : '',
            'searchable' => '1',
            'typesettings' => [],
        ];
    }

    protected function getTypeSettingsMenu(): CliMenu
    {
        return (new CliMenuBuilder())
            ->setBackgroundColour('blue')
            ->setForegroundColour('black')
            ->disableDefaultItems()
            ->addItem('Add instructions', function (CliMenu $menu) {
                $style = (new MenuStyle());
                $style->setBg('yellow');
                $style->setFg('black');
                $this->instructions = $menu->askText($style)
                    ->setPromptText('Instructions: ')
                    ->ask()
                    ->fetch();
            })
            ->addCheckboxItem('This field is required', function () { $this->isRequired = true; })
            ->build();
    }
}
