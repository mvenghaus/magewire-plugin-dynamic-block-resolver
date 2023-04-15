<?php

declare(strict_types=1);

namespace MVenghaus\MagewirePluginDynamicBlockResolver\Model\Component\Resolver;

use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Result\PageFactory as ResultPageFactory;
use Magewirephp\Magewire\Component;
use Magewirephp\Magewire\Model\Component\Resolver\Layout;
use Magewirephp\Magewire\Model\ComponentFactory;
use Magewirephp\Magewire\Model\RequestInterface as MagewireRequestInterface;
use MVenghaus\MagewirePluginDynamicBlockResolver\Model\Mapping;

class DynamicBlockResolver extends Layout
{
    public function __construct(
        private readonly ObjectManagerInterface $objectManager,
        private readonly Mapping $mapping,
        ResultPageFactory $resultPageFactory,
        EventManagerInterface $eventManager,
        ComponentFactory $componentFactory
    ) {
        parent::__construct($resultPageFactory, $eventManager, $componentFactory);
    }

    public function getName(): string
    {
        return 'dynamic_block';
    }

    public function complies(AbstractBlock $block): bool
    {
        return ($this->getMappingsByBlock($block) !== null);
    }

    public function construct(AbstractBlock $block): Component
    {
        $component = parent::construct($block);

        $mappings = $this->getMappingsByBlock($block);
        $mappingName = array_key_first($mappings);

        $component->setMetaData([
            'dynamic_block_name' => $mappingName,
            'dynamic_block_data' => $this->getBlockData($block)
        ]);

        return $component;
    }

    public function reconstruct(MagewireRequestInterface $request): Component
    {
        $page = $this->resultPageFactory->create();
        $page->addHandle(strtolower($request->getFingerprint('handle')))->initLayout();

        $dataMeta = $request->getServerMemo('dataMeta');

        $dynamicBlockName = $dataMeta['dynamic_block_name'];
        $dynamicBlockData = $dataMeta['dynamic_block_data'];

        $mapping = $this->getMappingByName($dynamicBlockName);

        /** @var Component $component */
        $component = $this->objectManager->create($mapping['magewire']);

        $block = $page->getLayout()->createBlock($mapping['block'])
            ->setData('magewire', $component)
            ->addData($dynamicBlockData);

        return $this->construct($block);
    }

    private function getMappingsByBlock(AbstractBlock $block): ?array
    {
        $blockClass = preg_replace('/\\\\Interceptor$/', '', $block::class);
        $magewireClass = preg_replace('/\\\\Interceptor$/', '', get_class($block->getData('magewire')));

        $mappings = array_filter(
            $this->mapping->mappings,
            fn(array $mapping) => $mapping['block'] === $blockClass && $mapping['magewire'] === $magewireClass
        );

        return $mappings ?: null;
    }

    private function getMappingByName(string $name): ?array
    {
        return $this->mapping->mappings[$name] ?? null;
    }

    private function getBlockData(AbstractBlock $block): array
    {
        $blockData = [];
        foreach ($block->getData() as $name => $value) {
            if (is_object($value) ||
                in_array($name, ['type', 'magewire', 'module_name'])
            ) {
                continue;
            }

            $blockData[$name] = $value;
        }

        return $blockData;
    }
}
