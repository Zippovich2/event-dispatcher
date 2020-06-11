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

use Zippovich2\EventDispatcher\TraceableEventDispatcherInterface;

class TransactionSubscriber
{
    /**
     * @var TraceableEventDispatcherInterface
     */
    private $dispatcher;

    public $creditBalanceInvokedTimes = 0;
    public $calculateBonusesInvokedTimes = 0;
    public $sendEmailInvokedTimes = 0;

    public function __construct(TraceableEventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function creditBalance(): void
    {
        $this->dispatcher->dispatch('BalanceUpdatedEvent');
        ++$this->creditBalanceInvokedTimes;
    }

    public function calculateBonuses(): void
    {
        $this->dispatcher->dispatch('BonusTransactionCompletedEvent');
        ++$this->calculateBonusesInvokedTimes;
    }

    public function sendEmail(): void
    {
        ++$this->sendEmailInvokedTimes;
    }
}
