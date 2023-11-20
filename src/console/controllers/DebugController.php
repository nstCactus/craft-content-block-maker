<?php

namespace modules\maker\console\controllers;

use Craft;
use craft\console\Controller;
use craft\fields\PlainText;
use modules\maker\helpers\Console;
use yii\console\ExitCode;
use verbb\supertable\fields\SuperTableField;

class DebugController extends Controller
{
    public function actionCreateTextField(): int
    {
        $field = Craft::$app->getFields()->createField([
            'name' => 'Text field from code',
            'handle' => 'testFieldFromCode',
            'instructions' => 'Champ texte crée programmatiquement',
            'groupId' => '1',
            'required' => null,
            'searchable' => true,
            'translationMethod' => 'none',
            'translationKeyFormat' => null,
            'type' => PlainText::class,
            'settings' => [
                'uiMode' => 'enlarged',
                'placeholder' => null,
                'code' => false,
                'multiline' => false,
                'initialRows' => 4,
                'charLimit' => null,
                'byteLimit' => null,
                'columnType' => null,
            ],
        ]);

        if (!Craft::$app->getFields()->saveField($field)) {
            Console::outputWarning(implode(', ', $field->getFirstErrors()));
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }

    public function actionCreateSupertableField(): int
    {
        $field = Craft::$app->getFields()->createField([
            'id' => null,
            'type' => SuperTableField::class,
            'groupId' => '1',
            'name' => 'Supertable field from code',
            'handle' => 'supertableFieldFromCode',
            'instructions' => '',
            'searchable' => true,
            'translationMethod' => 'none',
            'translationKeyFormat' => null,
            'settings' => [
                'fieldLayout' => 'row',
                'selectionLabel' => '',
                'minRows' => '',
                'maxRows' => '',
                'staticField' => '1',
                'blockTypes' => [
                    'new' => [
                        'fields' => [
                            'new1' => [
                                'name' => 'Heading',
                                'handle' => 'heading',
                                'type' => PlainText::class,
                                'instructions' => '',
                                'required' => '1',
                                'searchable' => '1',
                                'typesettings' => [
                                    'uiMode' => 'enlarged',
                                    'placeholder' => '',
                                    'fieldLimit' => '',
                                    'limitUnit' => 'chars',
                                    'code' => '',
                                    'multiline' => '',
                                    'initialRows' => '4',
                                    'columnType' => 'auto',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        if (!Craft::$app->getFields()->saveField($field)) {
            Console::outputWarning(implode(', ', $field->getFirstErrors()));
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }
}
