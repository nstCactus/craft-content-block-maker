<?php

namespace modules\maker\console\controllers;

use Craft;
use craft\console\Controller;
use craft\fieldlayoutelements\CustomField;
use craft\helpers\Console as CraftConsole;
use modules\maker\events\DefineSupportedFieldTypesEvent;
use modules\maker\fields\configurators\FieldTypeConfiguratorInterface;
use modules\maker\fields\configurators\ImageFieldTypeConfigurator;
use modules\maker\fields\configurators\LightswitchFieldTypeConfigurator;
use modules\maker\fields\configurators\PlainTextFieldTypeConfigurator;
use modules\maker\helpers\Console;
use modules\maker\MakerModule;
use PhpSchool\CliMenu\Builder\CliMenuBuilder;
use PhpSchool\CliMenu\CliMenu;
use verbb\supertable\fields\SuperTableField;
use yii\console\ExitCode;
use yii\helpers\Inflector;

/**
 *
 * @property-read array $fieldTypeOptions
 */
class MakeController extends Controller
{
    public const EVENT_DEFINE_SUPPORTED_FIELD_TYPES = 'supportedFieldTypes';
    public const DEFAULT_FIELD_GROUP_NAME = 'CMS blocks';
    public const NEO_FIELD_HANDLE = 'contentBuildersMain';

    /**
     * TODO: Make field group name configurable
     * TODO: Make Neo field handle configurable
     * TODO: Move most of this to a service; handle errors using exceptions
     */
    public function actionCmsBlock(): int
    {
        $fieldsService = Craft::$app->getFields();

        $cmsBlocksService = MakerModule::getInstance()->getCmsBlocks();
        $group = $cmsBlocksService->ensureFieldsGroupExists(self::DEFAULT_FIELD_GROUP_NAME);

        // TODO: check if block already exists
        $blockName = Console::prompt('Name of the block?', [ 'required' => true ]);
        $blockHandle = Console::prompt("Handle of the block?", [
            'default' => lcfirst(Inflector::camelize($blockName)),
        ]);

        $fields = [];
        for ($i = 1; $i < 100; $i++) {
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

            $fields["new$i"] = $field;
        }

        $superTableField = $fieldsService->createField([
            'id' => null,
            'type' => SuperTableField::class,
            'groupId' => $group->id,
            'name' => $blockName,
            'handle' => 'block' . ucfirst($blockHandle),
            'instructions' => '',
            'searchable' => true,
            'translationMethod' => 'none',
            'translationKeyFormat' => null,
            'settings' => [
                'fieldLayout' => 'row',
                "selectionLabel" => "",
                "minRows" => "",
                "maxRows" => "",
                'staticField' => '1',
                'blockTypes' => [
                    'new' => [
                        'fields' => $fields,
                    ],
                ],
            ],
        ]);
        if (!$fieldsService->saveField($superTableField)) {
            Console::outputWarning(implode(', ', $superTableField->getFirstErrors()));
            return ExitCode::UNSPECIFIED_ERROR;
        }

        if (!$cmsBlocksService->addNeoBlockType(self::NEO_FIELD_HANDLE, $blockHandle, $blockName, [
            [
                'name' => 'Content',
                'elements' => [
                    [
                        'type' => CustomField::class,
                        'fieldUid' => $superTableField->uid,
                    ],
                ],
            ],
        ])) {
            Console::outputWarning(implode(', ', $superTableField->getFirstErrors()));
            return ExitCode::UNSPECIFIED_ERROR;
        }

        if (!$cmsBlocksService->createTemplates($superTableField)) {
            Console::outputWarning(implode(', ', $superTableField->getFirstErrors()));
            return ExitCode::UNSPECIFIED_ERROR;
        }

        Console::output("");
        Console::outputSuccess('Done!');
        return ExitCode::OK;
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
            $menuBuilder->addItem($label, function (CliMenu $menu) use (&$fieldTypeClass, &$fieldTypeName, $className, $label) {
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

    /**
     * @param string $name
     * @param string $handle
     * @param class-string<FieldTypeConfiguratorInterface> $className
     * @return array
     */
    protected function getFieldTypeSpecificConfig(string $name, string $handle, string $className): array
    {
        /** @var FieldTypeConfiguratorInterface $fieldTypeConfigurator */
        $fieldTypeConfigurator = new $className;

        return array_merge([
            'name' => $name,
            'handle' => $handle,
            'type' => $className::fieldClassName(),
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
