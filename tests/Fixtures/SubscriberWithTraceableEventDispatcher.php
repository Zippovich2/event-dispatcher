<?php

/*
 * This file is part of the "Event Dispatcher" library.
 *
 * (c) Skoropadskyi Roman <zipo.ckorop@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zippovich2\EventDispatcher\Tests\Fixtures;

use Zippovich2\EventDispatcher\TraceableEventDispatcherInterface;

class SubscriberWithTraceableEventDispatcher
{
    /**
     * @var TraceableEventDispatcherInterface
     */
    private $dispatcher;

    public $callback1InvokedTimes = 0;
    public $callback2InvokedTimes = 0;

    public function __construct(TraceableEventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function callback1()
    {
        $this->dispatcher->dispatch('event2');
        ++$this->callback1InvokedTimes;
    }

    public function callback2()
    {
        ++$this->callback2InvokedTimes;
    }

    public static function staticCallBack()
    {
    }
}
