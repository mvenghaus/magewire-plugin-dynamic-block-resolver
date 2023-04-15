<?php

declare(strict_types=1);

namespace MVenghaus\MagewirePluginDynamicBlockResolver\Model;

class Mapping
{
    public function __construct(
        public readonly array $mappings = []
    ) {
    }
}
