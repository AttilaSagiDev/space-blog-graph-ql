<?php
/**
 * Copyright © 2023, Open Software License ("OSL") v. 3.0
 */

declare(strict_types=1);

namespace Space\BlogGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Space\BlogGraphQl\Model\Resolver\DataProvider\PostList as PostListDataProvider;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;

class Posts implements ResolverInterface
{
    /**
     * @var PostListDataProvider
     */
    private PostListDataProvider $postListDataProvider;

    /**
     * Constructor
     *
     * @param PostListDataProvider $postListDataProvider
     */
    public function __construct(
        PostListDataProvider $postListDataProvider
    ) {
        $this->postListDataProvider = $postListDataProvider;
    }

    /**
     * Resolver
     *
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws NoSuchEntityException
     * @throws GraphQlInputException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): array {
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

        if (isset($args['currentPage']) && $args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if (isset($args['pageSize']) && $args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }

        try {
            $postListData = $this->postListDataProvider->getPostList($args['pageSize'], $args['currentPage'], $storeId);
        } catch (NoSuchEntityException|LocalizedException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()));
        }

        return $postListData;
    }
}
