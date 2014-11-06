<?php

namespace Ledjin\Sagepay\Api\State;

interface StateInterface
{
    const STATE_WAITING = 'waiting_for_reply';

    const STATE_REPLIED = 'replied';

    const STATE_NOTIFIED = 'notified';

    const STATE_CONFIRMED = 'confirmed';

    const STATE_ERROR = 'error';
}
