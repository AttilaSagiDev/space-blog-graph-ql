<?php
/**
 * Copyright © 2023, Open Software License ("OSL") v. 3.0
 */

declare(strict_types=1);

namespace Space\BlogGraphQl\Model\Resolver\DataProvider;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Space\Blog\Api\BlogRepositoryInterface;
use Magento\Store\Model\Store;
use Space\Blog\Api\Data\BlogInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class PostList
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
     * Constructor
     *
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
     * Get post list
     *
     * @param int $pageSize
     * @param int $currentPage
     * @param int $storeId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getPostList(int $pageSize, int $currentPage, int $storeId): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(Store::STORE_ID, [$storeId, Store::DEFAULT_STORE_ID], 'in')
            ->addFilter(BlogInterface::IS_ACTIVE, true)
            ->setPageSize($pageSize)
            ->setCurrentPage($currentPage)->create();

        $postListResults = $this->blogRepository->getList($searchCriteria);

        if (!$postListResults->getTotalCount()) {
            throw new NoSuchEntityException(__('The post list is empty.'));
        }

        $listSize = $postListResults->getTotalCount();
        $totalPages = 0;
        if ($listSize > 0 && $pageSize > 0) {
            $totalPages = ceil($listSize / $pageSize);
        }

        $postList = [];
        foreach ($postListResults->getItems() as $post) {
            $postList['items'][] = [
                BlogInterface::BLOG_ID => $post->getId(),
                BlogInterface::TITLE => $post->getTitle(),
                BlogInterface::CONTENT => $post->getContent(),
                BlogInterface::AUTHOR => $post->getAuthor(),
                BlogInterface::CREATION_TIME => $post->getCreationTime(),
                BlogInterface::UPDATE_TIME => $post->getUpdateTime(),
                BlogInterface::IS_ACTIVE => $post->isActive()
            ];
        }

        $postList['page_info'] = [
            'total_pages' => $totalPages,
            'page_size' => $pageSize,
            'current_page' => $currentPage,
        ];
        $postList['total_count'] = $listSize;

        return $postList;
    }
}
