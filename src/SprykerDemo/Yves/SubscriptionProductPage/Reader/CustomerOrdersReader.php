<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerDemo\Yves\SubscriptionProductPage\Reader;

use Generated\Shared\Transfer\FilterTransfer;
use Generated\Shared\Transfer\OrderListFormatTransfer;
use Generated\Shared\Transfer\OrderListTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Spryker\Client\Customer\CustomerClientInterface;
use Spryker\Client\Sales\SalesClientInterface;

class CustomerOrdersReader implements CustomerOrdersReaderInterface
{
    /**
     * @var \Spryker\Client\Customer\CustomerClientInterface
     */
    private CustomerClientInterface $customerClient;

    /**
     * @var \Spryker\Client\Sales\SalesClientInterface
     */
    private SalesClientInterface $salesClient;

    /**
     * @param \Spryker\Client\Customer\CustomerClientInterface $customerClient
     * @param \Spryker\Client\Sales\SalesClientInterface $salesClient
     */
    public function __construct(
        CustomerClientInterface $customerClient,
        SalesClientInterface $salesClient
    ) {
        $this->customerClient = $customerClient;
        $this->salesClient = $salesClient;
    }

    /**
     * @param \Generated\Shared\Transfer\PaginationTransfer $paginationTransfer
     *
     * @return \Generated\Shared\Transfer\OrderListTransfer
     */
    public function getPaginatedCustomerOrders(PaginationTransfer $paginationTransfer): OrderListTransfer
    {
        $orderListTransfer = $this->getOrderList($paginationTransfer);

        return $this->salesClient->getPaginatedOrder($orderListTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\PaginationTransfer $paginationTransfer
     *
     * @return \Generated\Shared\Transfer\OrderListTransfer
     */
    protected function getOrderList(PaginationTransfer $paginationTransfer): OrderListTransfer
    {
        $customerTransfer = $this->customerClient->getCustomer();
        $orderListTransfer = new OrderListTransfer();

        $orderListTransfer->setIdCustomer($customerTransfer->getIdCustomer())
            ->setCustomerReference($customerTransfer->getCustomerReference());

        $orderListTransfer->setPagination($paginationTransfer);
        $orderListTransfer->setFilter($this->createFilterTransfer());
        $orderListTransfer->setFormat(new OrderListFormatTransfer());

        return $orderListTransfer;
    }

    /**
     * @return \Generated\Shared\Transfer\FilterTransfer
     */
    protected function createFilterTransfer(): FilterTransfer
    {
        $filterTransfer = new FilterTransfer();
        $filterTransfer->setOrderBy('created_at');
        $filterTransfer->setOrderDirection('DESC');

        return $filterTransfer;
    }
}
