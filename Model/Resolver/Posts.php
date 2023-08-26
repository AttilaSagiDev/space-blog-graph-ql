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
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

class Posts implements ResolverInterface
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
     * @throws GraphQlInputException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $store = $context->getExtensionAttributes()->getStore();

        if (isset($args['currentPage']) && $args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if (isset($args['pageSize']) && $args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }

        $postCollection = $this->collectionFactory->create();
        $postCollection->addStoreFilter($store)
            ->addFieldToFilter(BlogInterface::IS_ACTIVE, ['eq' => IsActive::STATUS_ENABLED])
            ->setCurPage($args['currentPage'])
            ->setPageSize($args['pageSize']);

        $collectionSize = $postCollection->getSize();
        $totalPages = 0;
        if ($postCollection->getSize() > 0 && $args['pageSize'] > 0) {
            $totalPages = ceil($collectionSize / $args['pageSize']);
        }

        $blogData = [];
        /** @var BlogInterface $post */
        foreach ($postCollection->getItems() as $post) {
            $blogData['items'][] = [
                BlogInterface::BLOG_ID => $post->getId(),
                BlogInterface::TITLE => $post->getTitle(),
                BlogInterface::CONTENT => $post->getContent(),
                BlogInterface::AUTHOR => $post->getAuthor(),
                BlogInterface::CREATION_TIME => $post->getCreationTime(),
                BlogInterface::UPDATE_TIME => $post->getUpdateTime(),
                BlogInterface::IS_ACTIVE => $post->isActive()
            ];
        }

        $blogData['page_info'] = [
            'total_pages' => $totalPages,
            'page_size' => $args['pageSize'],
            'current_page' => $args['currentPage'],
        ];
        $blogData['total_count'] = $collectionSize;

        return $blogData;
    }
}
