<?php

declare(strict_types=1);

/*
 * This file is part of the "Event Dispatcher" library.
 *
 * (c) Skoropadskyi Roman <zipo.ckorop@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zippovich2\EventDispatcher\Tests;

use PHPUnit\Framework\TestCase;
use Zippovich2\EventDispatcher\EventDispatcher;
use Zippovich2\EventDispatcher\Tests\Fixtures\Subscriber;
use Zippovich2\EventDispatcher\Tests\Fixtures\SubscriberWithEventDispatcher;

class EventDispatcherTest extends TestCase
{
    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    protected function setUp(): void
    {
        $this->dispatcher = new EventDispatcher();
    }

    protected function tearDown(): void
    {
        $this->dispatcher = null;
    }

    public function testInitialState(): void
    {
        static::assertEquals([], $this->dispatcher->getSubscribers());
    }

    public function testSubscribe(): void
    {
        $subscriber = new Subscriber();

        $this->dispatcher->subscribe('event1', [$subscriber, 'callback1']);
        $this->dispatcher->subscribe('event1', [$subscriber, 'callback2']);
        $this->dispatcher->subscribe('event2', [$subscriber, 'callback1']);

        static::assertEquals(
            [
                'event1' => [
                    [$subscriber, 'callback1'],
                    [$subscriber, 'callback2'],
                ],
                'event2' => [
                    [$subscriber, 'callback1'],
                ],
            ],
            $this->dispatcher->getSubscribers()
        );

        static::assertEquals(
            [
                [$subscriber, 'callback1'],
                [$subscriber, 'callback2'],
            ],
            $this->dispatcher->getSubscribers('event1')
        );

        static::assertEquals(
            [
                [$subscriber, 'callback1'],
            ],
            $this->dispatcher->getSubscribers('event2')
        );

        static::assertEquals(
            [],
            $this->dispatcher->getSubscribers('event3')
        );
    }

    public function testDispatch(): void
    {
        $subscriber = new SubscriberWithEventDispatcher($this->dispatcher);

        $this->dispatcher->subscribe('event1', [$subscriber, 'callback1']);
        $this->dispatcher->subscribe('event2', [$subscriber, 'callback2']);

        $this->dispatcher->dispatch('event1');

        static::assertTrue($subscriber->callback1Invoked);
        static::assertTrue($subscriber->callback2Invoked);
    }
}
