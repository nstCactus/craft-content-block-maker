<?php

namespace modules\maker\console\controllers;

use craft\console\Controller;
use craft\helpers\Console;
use modules\maker\events\DefineSupportedFieldTypesEvent;
use modules\maker\fields\configurators\FieldTypeConfiguratorInterface;
use modules\maker\fields\configurators\ImageFieldTypeConfigurator;
use modules\maker\fields\configurators\LightswitchFieldTypeConfigurator;
use modules\maker\fields\configurators\PlainTextFieldTypeConfigurator;
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
        $blockName = $this->prompt('Name of the block?', [ 'required' => true ]);
        $blockHandle = $this->prompt("Handle of the block?", [
            'default' => lcfirst(Inflector::camelize($blockName)),
        ]);

        $fields = [];
        for ($i = 0; $i < 100; $i++) {
            Console::output("");
            if ($i === 0) {
                Console::output("Great, let's add fields! (leave field name empty and hit enter when you're done)");
            } else {
                Console::output("All right, let's add another field. (leave field name empty and hit enter when you're done)");
            }

            $field = $this->addField();

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

    protected function addField(): ?array
    {
        $fieldName = $this->prompt("Name of the field?");
        if (empty($fieldName)) {
            return null;
        }

        $fieldHandle = $this->prompt("Handle of the field?", [
            'default' => lcfirst(Inflector::camelize($fieldName)),
        ]);

        $menuBuilder = (new CliMenuBuilder)
            ->setBackgroundColour('blue')
            ->setForegroundColour('black')
            ->disableDefaultItems();

        $fieldTypeClass = null;
        $fieldTypeName = null;
        $fieldTypeOptions = $this->getFieldTypeOptions();
        foreach ($fieldTypeOptions as $className => $label) {
            $menuBuilder->addItem($label, function(CliMenu $menu) use (&$fieldTypeClass, &$fieldTypeName, $className, $label) {
                $fieldTypeClass = $className;
                $fieldTypeName = $label;
                $menu->close();
            });
        }

        $menuBuilder->build()->open();
        Console::output("Type of the field? $fieldTypeName");

        return $this->getFieldTypeSpecificConfig($fieldName, $fieldHandle, $fieldTypeClass);
    }

    protected function getFieldTypeOptions(): array
    {
        $options = [];
        foreach($this->supportedFieldTypes() as $fieldClass) {
            $options[$fieldClass] = call_user_func([$fieldClass, 'displayName']);
        }

        return $options;
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
     * @return array
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
}
