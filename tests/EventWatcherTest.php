<?php

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
use Zippovich2\EventDispatcher\EventWatcher;

class EventWatcherTest extends TestCase
{
    public const EVENT_NAME_1 = 'event_name_1';
    public const EVENT_NAME_2 = 'event_name_2';

    /**
     * @var EventWatcher
     */
    private $eventWatcher;

    public function setUp(): void
    {
        $this->eventWatcher = new EventWatcher();
    }

    public function tearDown(): void
    {
        $this->eventWatcher = null;
    }

    public function testStartEvent(): void
    {
        $this->eventWatcher->startEvent(self::EVENT_NAME_1);
        static::assertCount(1, $this->eventWatcher->getEvents());

        $this->eventWatcher->startEvent(self::EVENT_NAME_2);
        static::assertCount(2, $this->eventWatcher->getEvents());
    }

    public function testStartEventException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('Event "%s" is already started.', self::EVENT_NAME_1));

        $this->eventWatcher->startEvent(self::EVENT_NAME_1);
        $this->eventWatcher->startEvent(self::EVENT_NAME_1);
    }

    public function testStopEvent(): void
    {
        $this->eventWatcher->startEvent(self::EVENT_NAME_1);
        $this->eventWatcher->startEvent(self::EVENT_NAME_2);
        static::assertCount(2, $this->eventWatcher->getEvents());

        $this->eventWatcher->stopEvent(self::EVENT_NAME_2);
        static::assertCount(1, $this->eventWatcher->getEvents());

        $this->eventWatcher->stopEvent(self::EVENT_NAME_1);
        static::assertCount(0, $this->eventWatcher->getEvents());
    }

    public function testStopEventException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('There is no events with name "%s" to stop.', self::EVENT_NAME_1));

        $this->eventWatcher->stopEvent(self::EVENT_NAME_1);
    }

    public function testAddEventSubscriber(): void
    {
        $this->eventWatcher->startEvent(self::EVENT_NAME_1);
        $this->eventWatcher->addEventSubscriber('subscriber_1');

        $this->eventWatcher->startEvent(self::EVENT_NAME_2);
        $this->eventWatcher->addEventSubscriber('subscriber_2');
        $this->eventWatcher->addEventSubscriber('subscriber_3');

        $this->eventWatcher->stopEvent(self::EVENT_NAME_2);

        $this->eventWatcher->addEventSubscriber('subscriber_4');

        $this->eventWatcher->stopEvent(self::EVENT_NAME_1);

        static::assertEquals([
            [
                'subscriber' => 'subscriber_1',
                'event' => self::EVENT_NAME_1,
                'level' => 1,
                'parent' => null,
            ],
            [
                'subscriber' => 'subscriber_2',
                'event' => self::EVENT_NAME_2,
                'level' => 2,
                'parent' => self::EVENT_NAME_1,
            ],
            [
                'subscriber' => 'subscriber_3',
                'event' => self::EVENT_NAME_2,
                'level' => 2,
                'parent' => self::EVENT_NAME_1,
            ],
            [
                'subscriber' => 'subscriber_4',
                'event' => self::EVENT_NAME_1,
                'level' => 1,
                'parent' => null,
            ],
        ], $this->eventWatcher->getSubscribers());
    }

    public function testAddEventSubscriberException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('There is no started events to add subscriber to it.');

        $this->eventWatcher->addEventSubscriber('subscriber_1');
    }
}
