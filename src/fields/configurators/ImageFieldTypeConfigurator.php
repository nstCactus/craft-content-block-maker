<?php

namespace modules\maker\fields\configurators;

use craft\fields\Assets;

class ImageFieldTypeConfigurator extends AbstractAssetFieldTypeConfigurator
{
    public function getTypeSettings(string $name, string $handle): array
    {
        return array_replace_recursive(parent::getTypeSettings($name, $handle), [
            'type' => Assets::class,
            'typeSettings' => [
                "restrictLocation" => false,
                "defaultUploadLocationSource" => "volume =>7835ccc0-93f4-46bd-a0f3-1d8e67b9d5c0", // TODO: select this
                "defaultUploadLocationSubpath" => "hero-home\/desktop", // TODO: ask this
                "allowUploads" => true,
                "restrictFiles" => true,
                "allowedKinds" => [
                    "image"
                ],
                "showUnpermittedVolumes" => false,
                "showUnpermittedFiles" => false,
                "previewMode" => "full",
                "allowSelfRelations" => false,
                "localizeRelations" => false,
                "maxRelations" => 1,
                "selectionLabel" => 'Choose an image',
                "validateRelatedElements" => false,
                "viewMode" => "large",
            ],
        ]);
    }

    public static function displayName(): string
    {
        return 'Image';
    }
}
