<?php
namespace MIntegration\TestTask\Block;

use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

class Index extends \Magento\Framework\View\Element\Template
{
    protected $categoryFactory;
    protected $imageBuilder;

    protected $_defaultToolbarBlock = Toolbar::class;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder
    )
    {
        parent::__construct($context);
        $this->categoryFactory = $categoryFactory;
        $this->imageBuilder=$imageBuilder;
    }

    public function getCategory()
    {
        $categoryId = $this->getCategoryId();
        $category = $this->categoryFactory->create()->load($categoryId);
        return $category;
    }
    protected function getProductCollection()
    {
        $productCollection = $this->getCategory()
            ->getProductCollection()
            ->addAttributeToSelect('*');

        if($this->getAttributeName() && $this->getAttributeValue()){
            $productCollection->addAttributeToFilter(
                $this->getAttributeName(), 
                array('eq' => $this->getAttributeValue())
            );
        }

        return $productCollection; 
    }
    public function getProductSliderCollection()
    {
        $productsNumber = $this->getProductsNumber();
        $productCollection = $this->getProductCollection();
        $productCollection->setPageSize($productsNumber);

        return $productCollection;
    }
    public function getProductListCollection()
    {
        $productCollection = $this->getProductCollection();
        $toolbar = $this->getToolbarBlock();
        $this->configureToolbar($toolbar, $productCollection);

        return $productCollection;
    }
    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
    }
    public function getPath()
    {
        $path = $this->getLinkPath();
        return $path;
    }
            
    protected function _beforeToHtml()
    {
        $collection = $this->getProductListCollection();
        $this->configureToolbar($this->getToolbarBlock(), $collection);
        $collection->load();

        return parent::_beforeToHtml();
    }

    public function getToolbarBlock()
    {
        $blockName = $this->getToolbarBlockName();
        if ($blockName) {
            $block = $this->getLayout()->getBlock($blockName);
            if ($block) {
                return $block;
            }
        }
        $block = $this->getLayout()->createBlock($this->_defaultToolbarBlock, uniqid(microtime()));
        return $block;
    }

    public function getAdditionalHtml()
    {
        return $this->getChildHtml('additional');
    }

    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }
    
    private function configureToolbar(Toolbar $toolbar, Collection $collection)
    {
        $orders = $this->getAvailableOrders();
        if ($orders) {
            $toolbar->setAvailableOrders($orders);
        }
        $sort = $this->getSortBy();
        if ($sort) {
            $toolbar->setDefaultOrder($sort);
        }
        $dir = $this->getDefaultDirection();
        if ($dir) {
            $toolbar->setDefaultDirection($dir);
        }
        $modes = $this->getModes();
        if ($modes) {
            $toolbar->setModes($modes);
        }

        $toolbar->setCollection($collection);
        $this->setChild('toolbar', $toolbar);
    }
}

