<?php

namespace Ledjin\Sagepay\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Ledjin\Sagepay\Api;
use Ledjin\Sagepay\Api\State\StateInterface;

class StatusAction implements ActionInterface, StateInterface
{

    /**
     * {@inheritDoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = new ArrayObject($request->getModel());

        $state = $model['state'];

        if (null === $state ||
            self::STATE_WAITING === $state
        ) {
            $request->markNew();

            return;
        }

        if (self::STATE_REPLIED === $state ||
            self::STATE_NOTIFIED === $state
        ) {
            $request->markPending();

            return;
        }

        if (self::STATE_REPLIED === $state) {
            $request->markSuccess();

            return;
        }



        $request->markFailed();
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
