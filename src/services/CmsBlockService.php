<?php

namespace modules\maker\services;

use benf\neo\elements\Block as NeoBlock;
use benf\neo\Field as NeoField;
use benf\neo\models\BlockType;
use Craft;
use craft\base\Component;
use craft\base\Field;
use craft\base\FieldInterface;
use craft\helpers\ArrayHelper;
use craft\models\FieldGroup;
use craft\models\FieldLayout;
use craft\services\Fields;
use modules\maker\helpers\Console;
use RuntimeException;
use verbb\supertable\fields\SuperTableField;

class CmsBlockService extends Component
{
    protected Fields $fieldsService;

    public function init(): void
    {
        parent::init();
        $this->fieldsService = Craft::$app->getFields();
    }

    public function ensureFieldsGroupExists(string $groupName): FieldGroup
    {
        $groups = $this->fieldsService->getAllGroups();

        foreach ($groups as $group) {
            if ($group->name === $groupName) {
                return $group;
            }
        }

        $group = new FieldGroup(['name' => $groupName]);

        if (!$this->fieldsService->saveGroup($group)) {
            throw new RuntimeException("Could not create the \"$group->name\" field group: " . implode(', ', $group->getFirstErrors()));
        }

        return $group;
    }

    public function createTemplates(SuperTableField $superTableField): bool
    {
        return true;
    }

    public function addNeoBlockType(string $neoFieldHandle, string $blockHandle, string $blockName, $tabs): NeoField
    {
        $fieldLayout = $this->addFieldLayout($tabs, NeoBlock::class);

        $blockType = [
            'handle' => $blockHandle,
            'name' => $blockName,
            'description' => null,
            'maxBlocks' => null,
            'maxSiblingBlocks' => null,
            'maxChildBlocks' => null,
            'childBlocks' => null,
            'topLevel' => true,
            'sortOrder' => null,
            'fieldLayoutId' => $fieldLayout->id,
        ];

        /** @var NeoField $neoField */
        $neoField = $this->fieldsService->getFieldByHandle($neoFieldHandle);

        if (!$neoField) {
            Console::output("Crap! The \"$neoFieldHandle\" doesn't exist. I'll create it for you…");
            $firstGroup = $this->fieldsService->getAllGroups()[0];

            return $this->createNeoField($neoFieldHandle, 'Content blocks', $firstGroup->id, ['new0' => $blockType]);
        }

        $existingBlockTypes = $neoField->getBlockTypes();
        $sortOrders = ArrayHelper::getColumn($existingBlockTypes, fn(BlockType $blockType) => $blockType->sortOrder);
        $blockType['sortOrder'] = max($sortOrders) + 1;
        $neoField->setBlockTypes($existingBlockTypes + ['new0' => $blockType]);
        if (!Craft::$app->getFields()->saveField($neoField)) {
            throw new RuntimeException("Couldn't add a block to the existing Neo field: " . implode(', ', $neoField->getFirstErrors()));
        }

        return $neoField;
    }

    /**
     * TODO: fire an event to make this configurable
     */
    protected function createNeoBlockTypeDescriptor(string $name, string $handle, array $tabs): array
    {

        return [];
    }

    protected function addFieldLayout(array $tabs, string $type): FieldLayout
    {
        $fieldsService = Craft::$app->getFields();
        $fieldLayout = FieldLayout::createFromConfig([
            'tabs' => $tabs,
        ]);
        $fieldLayout->type = $type;
        if (!$fieldsService->saveLayout($fieldLayout)) {
            throw new RuntimeException('Could not create field layout: ' . implode(', ', $fieldLayout->getFirstErrors()));
        }

        return $fieldLayout;
    }

    /**
     * TODO: fire an event to make this configurable
     */
    protected function createNeoField(string $handle, string $name, int $groupId, array $blockTypes): NeoField
    {
        $fieldSettings = [
            'type' => NeoField::class,
            'id' => null,
            'uid' => null,
            'groupId' => $groupId,
            'name' => $name,
            'handle' => $handle,
            'columnSuffix' => null,
            'instructions' => '',
            'searchable' => true,
            'translationMethod' => 'none',
            'translationKeyFormat' => null,
            'settings' => [
                'items' => [
                    'sortOrder' => [
                        0 => 'blocktype:new0',
                    ],
                    'blockTypes' => $blockTypes,
                ],
                'minBlocks' => '',
                'maxBlocks' => '',
                'minTopBlocks' => '',
                'maxTopBlocks' => '',
                'minLevels' => '',
                'maxLevels' => '',
            ],
        ];

        /** @var NeoField $field */
        $field = $this->fieldsService->createField($fieldSettings);
        if (!$this->fieldsService->saveField($field)) {
            $errors = $field->getErrors();
            Craft::dd([
                'blockTypes' => $field->getBlockTypes(),
                'errors' => array_map(function (\benf\neo\models\BlockType $blockType) {
                    return $blockType->getFirstErrors();
                }, $field->getBlockTypes()),
            ]);
            $errorText = print_r($errors, true);
            throw new RuntimeException("Could not save the $name field: $errorText");
        }

        return $field;
    }

    public function saveField(FieldInterface $field): bool
    {
        return $this->fieldsService->saveField($field);
    }
}
