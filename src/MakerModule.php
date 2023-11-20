<?php

namespace modules\maker;

use modules\maker\services\CmsBlockService;
use nstcactus\CraftUtils\AbstractModule;

/**
 * @property-read CmsBlockService $cmsBlocks
 */
class MakerModule extends AbstractModule
{
    /**
     * @inerhitdoc
     */
    protected function getComponentDefinitions(): array
    {
        return [
            'cmsBlocks' => CmsBlockService::class,
        ];
    }

    public function getCmsBlocks(): CmsBlockService
    {
        return $this->get('cmsBlocks');
    }
}
