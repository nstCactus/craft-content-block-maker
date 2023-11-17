<?php

namespace modules\maker\fields\configurators;

interface FieldTypeConfiguratorInterface
{
    public function getTypeSettings(string $name, string $handle): array;

    public static function displayName(): string;
}
