<?php

namespace Ledjin\Sagepay\Action;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetCurrency;
use Payum\Core\Security\GenericTokenFactoryInterface;

class ConvertPaymentAction extends GatewayAwareAction
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
     * @param Convert $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        $this->gateway->execute($currency = new GetCurrency($payment->getCurrencyCode()));
        $divisor = pow(10, $currency->exp);

        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        $details['VendorTxCode'] = $payment->getNumber();
        $details['Amount'] = $payment->getTotalAmount() / $divisor;
        $details['Currency'] = $payment->getCurrencyCode();
        $details['Description'] = $payment->getDescription();
        $details['CustomerEMail'] = $payment->getClientEmail();

        $request->setResult((array) $details);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            $request->getTo() == 'array'
        ;
    }
}
