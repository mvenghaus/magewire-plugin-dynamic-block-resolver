# Magewire Plugin - Dynamic Block Resolver

Innately magewire defines the component in the layout xml. But sometimes you want to create a blog on the fly in your template with the full power of magewire.

```
$block->getLayout()
    ->createBlock(\Magento\Framework\View\Element\Template::class)
    ->setData('magewire', \Vendor\Module\Magewire\Test')
    ->toHtml();
```

The problem is that magewire can't find this definition in a subsequent request.
This plugin allows you to define a mapping which magewire uses to resolve the block and the component on a subsequent request.

### Defining the magewire component

Just add a mapping to your di.xml module file like this:

```
<type name="MVenghaus\MagewirePluginDynamicBlockResolver\Model\Mapping">
    <arguments>
        <argument name="mappings" xsi:type="array">
            <item name="test" xsi:type="array">
                <item name="block" xsi:type="string">Magento\Framework\View\Element\Template</item>
                <item name="magewire" xsi:type="string">Vendor\Module\Magewire\Test</item>
            </item>
        </argument>
    </arguments>
</type>
```