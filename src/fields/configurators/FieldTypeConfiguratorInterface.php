<?php

namespace modules\maker\fields\configurators;

use craft\base\FieldInterface;

interface FieldTypeConfiguratorInterface
{
    public function getTypeSettings(string $name, string $handle): array;

    public static function displayName(): string;

    /**
     * @return class-string<FieldInterface>
     */
    public static function fieldClassName(): string;
}
