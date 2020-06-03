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

use Zippovich2\EventDispatcher\EventDispatcherInterface;

class SubscriberWithEventDispatcher
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public $callback1Invoked = false;
    public $callback2Invoked = false;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function callback1()
    {
        $this->dispatcher->dispatch('event2');
        $this->callback1Invoked = true;
    }

    public function callback2()
    {
        $this->callback2Invoked = true;
    }
}
