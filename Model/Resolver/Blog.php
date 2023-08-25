<?php
/**
 * Copyright © 2023, Open Software License ("OSL") v. 3.0
 */

declare(strict_types=1);

namespace Space\BlogGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Space\Blog\Model\ResourceModel\Blog\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Space\Blog\Api\Data\BlogInterface;
use Space\Blog\Model\Source\IsActive;
use Magento\Framework\Exception\NoSuchEntityException;

class Blog implements ResolverInterface
{
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $collectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * Constructor
     *
     * @param CollectionFactory $collectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Resolver
     *
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed|void
     * @throws NoSuchEntityException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $postCollection = $this->collectionFactory->create();
        $postCollection->addStoreFilter((int)$this->storeManager->getStore()->getId())
            ->addFieldToFilter(BlogInterface::IS_ACTIVE, ['eq' => IsActive::STATUS_ENABLED]);

        $blogData = [];
        /** @var BlogInterface $post */
        foreach ($postCollection->getItems() as $post) {
            $blogData[] = [
                BlogInterface::BLOG_ID => $post->getId(),
                BlogInterface::TITLE => $post->getTitle(),
                BlogInterface::CONTENT => $post->getContent(),
                BlogInterface::AUTHOR => $post->getAuthor(),
                BlogInterface::CREATION_TIME => $post->getCreationTime(),
                BlogInterface::UPDATE_TIME => $post->getUpdateTime(),
                BlogInterface::IS_ACTIVE => $post->isActive()
            ];
        }

        return $blogData;
    }
}
