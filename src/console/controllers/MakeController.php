<?php

namespace modules\maker\console\controllers;

use craft\console\Controller;
use craft\helpers\Console as CraftConsole;
use modules\maker\events\DefineSupportedFieldTypesEvent;
use modules\maker\fields\configurators\FieldTypeConfiguratorInterface;
use modules\maker\fields\configurators\ImageFieldTypeConfigurator;
use modules\maker\fields\configurators\LightswitchFieldTypeConfigurator;
use modules\maker\fields\configurators\PlainTextFieldTypeConfigurator;
use modules\maker\helpers\Console;
use PhpSchool\CliMenu\Builder\CliMenuBuilder;
use PhpSchool\CliMenu\CliMenu;
use verbb\supertable\fields\SuperTableField;
use yii\helpers\Inflector;

/**
 *
 * @property-read array $fieldTypeOptions
 */
class MakeController extends Controller
{
    public const EVENT_DEFINE_SUPPORTED_FIELD_TYPES = 'supportedFieldTypes';

    public function actionCmsBlock() {
        $blockName = Console::prompt('Name of the block?', [ 'required' => true ]);
        $blockHandle = Console::prompt("Handle of the block?", [
            'default' => lcfirst(Inflector::camelize($blockName)),
        ]);

        $fields = [];
        for ($i = 0; $i < 100; $i++) {
            Console::output("");
            if ($i === 0) {
                Console::outputSuccess("Great, let's add fields!");
            } else {
                Console::outputSuccess("All right, let's add another field.");
            }

            $field = $this->addField($fields);

            if (!$field) {
                break;
            }

            $fields[] = $field;
        }

        \Craft::dd([
            'name' => $blockName,
            'handle' => $blockHandle,
            'instructions' => '',
            'required' => false,
            'searchable' => 1,
            "translationMethod" => "site",
            'type' => SuperTableField::class,
            'settings' => [
                'propagationMethod' => 'all',
                'staticField' => true,
                'fieldLayout'=> 'row',
                'blockTypes' => [
                    'new1' => [
                        'fields' => $fields,
                    ],
                ],
            ],
        ]);
    }

    protected function addField($existingFields): ?array
    {
        do {
            $fieldName = CraftConsole::prompt(Console::ansiFormat('Name of the field?', [Console::FG_YELLOW]) . " (leave blank when you're done)");
            if (empty($fieldName)) {
                return null;
            }

            $fieldHandle = Console::prompt("Handle of the field?", [
                'default' => lcfirst(Inflector::camelize($fieldName)),
            ]);
        } while ($this->doesFieldExist($existingFields, $fieldHandle));

        $menuBuilder = (new CliMenuBuilder)
            ->setBackgroundColour('blue')
            ->setForegroundColour('black')
            ->disableDefaultItems();

        $fieldTypeClass = null;
        $fieldTypeName = null;
        $supportedFieldTypes = $this->supportedFieldTypes();
        foreach ($supportedFieldTypes as $className) {
            $label = $className::displayName();
            $menuBuilder->addItem($label, function(CliMenu $menu) use (&$fieldTypeClass, &$fieldTypeName, $className, $label) {
                $fieldTypeClass = $className;
                $fieldTypeName = $label;
                $menu->close();
            });
        }

        $menuBuilder->build()->open();
        Console::output(Console::ansiFormat("Type of the field? ", [Console::FG_YELLOW]) . $fieldTypeName);

        return $this->getFieldTypeSpecificConfig($fieldName, $fieldHandle, $fieldTypeClass);
    }

    /**
     * Returns how to configure supporter field types.
     *
     * See [[supportedFieldTypes()]] for details about what should be returned.
     *
     * Models should override this method instead of [[supportedFieldTypes()]] so [[EVENT_DEFINE_SUPPORTED_FIELD_TYPES]]
     * handlers can modify the default supported field types.
     *
     * @return array
     */
    protected function defineSupportedFieldTypes(): array
    {
        return [
            ImageFieldTypeConfigurator::class,
            PlainTextFieldTypeConfigurator::class,
            LightswitchFieldTypeConfigurator::class,
        ];
    }

    /**
     * Return an array of FQCN of classes that implement FieldTypeConfiguratorInterface
     *
     * @return class-string<FieldTypeConfiguratorInterface>[]
     */
    protected function supportedFieldTypes(): array
    {
        $supportedFieldTypes = $this->defineSupportedFieldTypes();

        $event = new DefineSupportedFieldTypesEvent([
            'supportedFieldTypes' => $supportedFieldTypes,
        ]);
        $this->trigger(self::EVENT_DEFINE_SUPPORTED_FIELD_TYPES, $event);

        return $event->supportedFieldTypes;
    }

    protected function getFieldTypeSpecificConfig(string $name, string $handle, string $className): array
    {
        /** @var FieldTypeConfiguratorInterface $fieldTypeConfigurator */
        $fieldTypeConfigurator = new $className;

        return array_merge([
            'name' => $name,
            'handle' => $handle,
            'type' => $className,
        ], $fieldTypeConfigurator->getTypeSettings($name, $handle));
    }

    /**
     * Check whether a field having the same handle already exist
     */
    protected function doesFieldExist(array $existingFields, string $fieldHandle): bool
    {
        foreach ($existingFields as $existingField) {
            if ($existingField['handle'] === $fieldHandle) {
                Console::output("");
                Console::stdout("Abort: a field with that handle already exist. Please add another field.", Console::FG_RED);
                Console::output("");
                return true;
            }
        }

        return false;
    }
}
