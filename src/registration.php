<?php

declare(strict_types=1);

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'MVenghaus_MagewirePluginDynamicBlockResolver',
    __DIR__
);
