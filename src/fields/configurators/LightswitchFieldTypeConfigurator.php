<?php

namespace modules\maker\fields\configurators;

class LightswitchFieldTypeConfigurator extends BaseFieldTypeConfigurator
{
    public function getTypeSettings(): array
    {
        return array_merge(parent::getTypeSettings(), [
            'searchable' => false,
        ]);
    }
}
