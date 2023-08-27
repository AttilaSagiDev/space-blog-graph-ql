<?php
/**
 * Copyright © 2023, Open Software License ("OSL") v. 3.0
 */

declare(strict_types=1);

namespace Space\BlogGraphQl\Model\Resolver\DataProvider;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Space\Blog\Api\BlogRepositoryInterface;
use Space\Blog\Api\Data\BlogInterface;
use Magento\Store\Model\Store;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Post
{
    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var BlogRepositoryInterface
     */
    private BlogRepositoryInterface $blogRepository;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param BlogRepositoryInterface $blogRepository
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        BlogRepositoryInterface $blogRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->blogRepository = $blogRepository;
    }

    /**
     * Get post by ID
     *
     * @param int $blogId
     * @param int $storeId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getPostById(int $blogId, int $storeId): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(BlogInterface::BLOG_ID, $blogId)
            ->addFilter(Store::STORE_ID, [$storeId, Store::DEFAULT_STORE_ID], 'in')
            ->addFilter(BlogInterface::IS_ACTIVE, true)->create();

        $postResults = $this->blogRepository->getList($searchCriteria)->getItems();

        if (empty($postResults)) {
            throw new NoSuchEntityException(
                __('The post with the "%1" ID doesn\'t exist.', $blogId)
            );
        }

        $post = current($postResults);
        return [
            BlogInterface::BLOG_ID => $post->getId(),
            BlogInterface::TITLE => $post->getTitle(),
            BlogInterface::CONTENT => $post->getContent(),
            BlogInterface::AUTHOR => $post->getAuthor(),
            BlogInterface::CREATION_TIME => $post->getCreationTime(),
            BlogInterface::UPDATE_TIME => $post->getUpdateTime(),
            BlogInterface::IS_ACTIVE => $post->isActive()
        ];
    }
}
