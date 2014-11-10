<?php

namespace Ledjin\Sagepay\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\FillOrderDetails;
use Payum\Core\Security\GenericTokenFactoryInterface;

class FillOrderDetailsAction implements ActionInterface
{

    /**
     * @var GenericTokenFactoryInterface
     */
    protected $tokenFactory;

    /**
     * @param GenericTokenFactoryInterface $tokenFactory
     */
    public function __construct(GenericTokenFactoryInterface $tokenFactory = null)
    {
        $this->tokenFactory = $tokenFactory;
    }

    /**
     * {@inheritDoc}
     *
     * @param FillOrderDetails $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $order = $request->getOrder();

        $details = $order->getDetails();

        $details['VendorTxCode'] = $order->getNumber();
        $details['Amount'] = $order->getTotalAmount();
        $details['Currency'] = $order->getCurrency();
        $details['Description'] = $order->getDescription();
        $details['NotificationURL'] = $this->tokenFactory
            ->createNotifyToken(
                $request->getToken()->getPaymentName(),
                $order
            )->getTargetUrl();
        $details['BillingSurname'] = $order->getBillingAddress()->getLastName();
        $details['BillingFirstnames'] = $order->getBillingAddress()->getFirstName();
        $details['BillingAddress1'] = $order->getBillingAddress()->getStreet();
        $details['BillingCity'] = $order->getBillingAddress()->getCity();
        $details['BillingPostCode'] = $order->getBillingAddress()->getPostcode();
        $details['BillingCountry'] = $order->getBillingAddress()->getCountry()->getIsoName();
        $details['DeliverySurname'] = $order->getShippingAddress()->getLastName();
        $details['DeliveryFirstnames'] = $order->getShippingAddress()->getFirstName();
        $details['DeliveryAddress1'] = $order->getShippingAddress()->getStreet();
        $details['DeliveryCity'] = $order->getShippingAddress()->getCity();
        $details['DeliveryPostCode'] = $order->getShippingAddress()->getPostcode();
        $details['DeliveryCountry'] = $order->getShippingAddress()->getCountry()->getIsoName();

        $order->setDetails($details);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof FillOrderDetails;
    }
}
