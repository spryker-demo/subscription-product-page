<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerDemo\Yves\SubscriptionProductPage\Controller;

use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Spryker\Yves\Kernel\Controller\AbstractController;
use SprykerDemo\Yves\SubscriptionProductPage\Plugin\Router\SubscriptionProductPageRouteProviderPlugin;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \SprykerDemo\Yves\SubscriptionProductPage\SubscriptionProductPageFactory getFactory()
 */
class SubscriptionProductController extends AbstractController
{
    /**
     * @var string
     */
    protected const PRODUCT_CANCEL_SUBSCRIPTION_SUCCESS_MESSAGE = 'product.cancel_subscription.success_message';

    /**
     * @var string
     */
    protected const PRODUCT_CANCEL_SUBSCRIPTION_ERROR_MESSAGE = 'product.cancel_subscription.error_message';

    /**
     * @var string
     */
    protected const PRODUCT_CANCEL_SUBSCRIPTION_WARNING_MESSAGE = 'product.cancel_subscription.warning_message';

    /**
     * @var string
     */
    protected const PARAM_PAGE = 'page';

    /**
     * @var string
     */
    protected const PARAM_PER_PAGE = 'perPage';

    /**
     * @var int
     */
    protected const DEFAULT_PAGE = 1;

    /**
     * @var int
     */
    protected const DEFAULT_ITEMS_PAGE = 10;

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Spryker\Yves\Kernel\View\View|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexAction(Request $request)
    {
        $viewData = $this->executeIndexAction($request);

        return $this->view(
            $viewData,
            [],
            '@SubscriptionProductPage/views/subscription/subscription.twig',
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    protected function executeIndexAction(Request $request): array
    {
        $paginationTransfer = $this->createPaginationTransfer($request);

        $orderListTransfer = $this->getFactory()
            ->createOrderReader()
            ->getPaginatedCustomerOrders($paginationTransfer);

        return [
            'pagination' => $orderListTransfer->getPagination(),
            'orderList' => $orderListTransfer,
            'isOrderSearchEnabled' => false,
            'isOrderSearchOrderItemsVisible' => true,
            'productCancelSubscriptionForm' => $this->getFactory()->createSubscriptionProductFormFactory()->createProductCancelSubscriptionForm()->createView(),
        ];
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Spryker\Yves\Kernel\View\View|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function cancelAction(Request $request)
    {
        $customerTransfer = $this->getFactory()->getCustomerClient()->getCustomer();
        $idSalesOrder = (int)$request->query->get('sales_order_id');

        $orderTransfer = new OrderTransfer();
        $orderTransfer->setIdSalesOrder($idSalesOrder)
            ->setFkCustomer($customerTransfer->getIdCustomer())
            ->setCustomer($customerTransfer);

        $orderTransfer = $this->getFactory()->getSalesClient()->getOrderDetails($orderTransfer);
        $itemTransfer = $this->getItemTransfer($orderTransfer, (int)$request->query->get('sales_order_item_id'));

        $this->addCancellationMessage($orderTransfer, $itemTransfer);

        return $this->redirectResponseInternal(SubscriptionProductPageRouteProviderPlugin::ROUTE_SUBSCRIPTION);
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param int $orderItemId
     *
     * @return \Generated\Shared\Transfer\ItemTransfer|null
     */
    protected function getItemTransfer(OrderTransfer $orderTransfer, int $orderItemId): ?ItemTransfer
    {
        foreach ($orderTransfer->getItems() as $item) {
            if ($item->getIdSalesOrderItem() == $orderItemId) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param \Generated\Shared\Transfer\ItemTransfer|null $itemTransfer
     *
     * @return void
     */
    protected function addCancellationMessage(OrderTransfer $orderTransfer, ?ItemTransfer $itemTransfer): void
    {
        if ($orderTransfer->getIdSalesOrder() !== null && $itemTransfer && $itemTransfer->getState()->getName() == 'subscription active') {
            $successMessage = $this->getFactory()->getGlossaryClient()->translate(
                static::PRODUCT_CANCEL_SUBSCRIPTION_SUCCESS_MESSAGE,
                $this->getLocale(),
                ['%product%' => $itemTransfer->getName()],
            );

            $this->getFactory()->getSubscriptionProductOmsClient()->cancelOrderItemSubscription($itemTransfer);

            $this->addSuccessMessage($successMessage);

            return;
        }

        if ($itemTransfer->getState()->getName() == 'subscription cancelled' || $itemTransfer->getState()->getName() == 'closed') {
            $this->addErrorMessage(static::PRODUCT_CANCEL_SUBSCRIPTION_WARNING_MESSAGE);

            return;
        }

        $this->addErrorMessage(static::PRODUCT_CANCEL_SUBSCRIPTION_ERROR_MESSAGE);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Generated\Shared\Transfer\PaginationTransfer
     */
    protected function createPaginationTransfer(Request $request): PaginationTransfer
    {
        $paginationTransfer = new PaginationTransfer();

        $paginationTransfer->setPage(
            $request->query->getInt(static::PARAM_PAGE, static::DEFAULT_PAGE),
        );
        $paginationTransfer->setMaxPerPage(
            $request->query->getInt(static::PARAM_PER_PAGE, static::DEFAULT_ITEMS_PAGE),
        );

        return $paginationTransfer;
    }
}
