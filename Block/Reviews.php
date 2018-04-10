<?php

namespace OuterEdge\Reviews\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;
use Magento\Review\Model\ResourceModel\Review\Collection as ReviewCollection;
use Magento\Review\Model\Review;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

class Reviews extends Template
{
    /**
     * @var ReviewCollectionFactory
     */
    protected $reviewCollectionFactory;
    
    /**
     * @var ReviewCollection
     */
    protected $reviewCollection;
    
    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;
    
    /**
     * @var integer
     */
    protected $defaultLimit = 10;
    
    /**
     * Constructor
     * 
     * @param Conext $context
     * @param ReviewCollectionFactory $reviewCollectionFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        ReviewCollectionFactory $reviewCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        array $data = []
    ) {
        $this->reviewCollectionFactory = $reviewCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        parent::__construct($context, $data);
    }
    
    /**
     * Get all reviews
     * 
     * @return ReviewCollection
     */
    public function getReviews()
    {
        if (null === $this->reviewCollection) {
            $page = $this->getRequest()->getParam('p', 1);
            $limit = $this->getRequest()->getParam('limit', $this->defaultLimit);
            $product = $this->getProductFilter();
            
            $this->reviewCollection = $this->reviewCollectionFactory->create()
                ->addStoreFilter($this->_storeManager->getStore()->getId())
                ->addStatusFilter(Review::STATUS_APPROVED)
                ->addFieldToFilter('display_on_global_review_list', 1)
                ->setDateOrder();
                
            if ($product) {
                $this->reviewCollection->addEntityFilter('product', $product);
            }
                
            $this->reviewCollection->setPageSize($limit);
            $this->reviewCollection->setCurPage($page);
        }
        
        return $this->reviewCollection;
    }
    
    /**
     * Get list of products from reviews
     * 
     * @return ProductCollection
     */
    public function getProducts()
    {
        $productIds = [];
        foreach ($this->getReviews() as $review) {
            $productIds[$review->getEntityPkValue()] = true;
        }
        
        return $this->productCollectionFactory->create()
            ->addAttributeToSelect('name')
            ->addIdFilter(array_keys($productIds));
    }
    
    /**
     * Get the current product filter
     * 
     * @return null|integer
     */
    public function getProductFilter()
    {
        return $this->getRequest()->getParam('product', null);
    }
    
    /**
     * Add pager to the review results
     * 
     * @return $this
     */
    protected function _prepareLayout()
    {
        if ($this->getReviews()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'reviews.pager'
            )->setAvailableLimit([
                $this->defaultLimit => $this->defaultLimit,
                //25 => 25,
                //50 => 50,
                //500 => 500
            ])->setShowPerPage(true)->setCollection(
                $this->getReviews()
            );
            $this->setChild('pager', $pager);
            $this->getReviews()->load();
        }
        return parent::_prepareLayout();
    }
    
    /**
     * Get pager html
     * 
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    
    /**
     * Render page using template
     * 
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getTemplate()) {
            return '';
        }
        return $this->fetchView($this->getTemplateFile());
    }
}