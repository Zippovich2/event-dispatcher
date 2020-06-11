<?php

/*
 * This file is part of the "Event Dispatcher" library.
 *
 * (c) Skoropadskyi Roman <zipo.ckorop@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zippovich2\EventDispatcher\Tests\Fixtures\PekariumCase;

use Zippovich2\EventDispatcher\EventDispatcher;

class BalanceUpdateSubscriber
{
    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    public $notifyUserInvokedTimes = 0;

    public function __construct(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function notifyUser(): void
    {
        ++$this->notifyUserInvokedTimes;
    }
}
