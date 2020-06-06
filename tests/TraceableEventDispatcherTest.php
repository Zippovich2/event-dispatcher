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
use Zippovich2\EventDispatcher\EventWatcher;
use Zippovich2\EventDispatcher\Tests\Fixtures\Bug1\SubscriberA;
use Zippovich2\EventDispatcher\Tests\Fixtures\Bug1\SubscriberB;
use Zippovich2\EventDispatcher\Tests\Fixtures\Bug1\SubscriberC;
use Zippovich2\EventDispatcher\Tests\Fixtures\Bug1\SubscriberD;
use Zippovich2\EventDispatcher\Tests\Fixtures\ComplexNestedEvents\SubscriberA as ComplexCaseSubscriberA;
use Zippovich2\EventDispatcher\Tests\Fixtures\ComplexNestedEvents\SubscriberB as ComplexCaseSubscriberB;
use Zippovich2\EventDispatcher\Tests\Fixtures\ComplexNestedEvents\SubscriberC as ComplexCaseSubscriberC;
use Zippovich2\EventDispatcher\Tests\Fixtures\ComplexNestedEvents\SubscriberD as ComplexCaseSubscriberD;
use Zippovich2\EventDispatcher\Tests\Fixtures\ComplexNestedEvents\SubscriberE as ComplexCaseSubscriberE;
use Zippovich2\EventDispatcher\Tests\Fixtures\ComplexNestedEvents\SubscriberF as ComplexCaseSubscriberF;
use Zippovich2\EventDispatcher\Tests\Fixtures\ComplexNestedEvents\SubscriberG as ComplexCaseSubscriberG;
use Zippovich2\EventDispatcher\Tests\Fixtures\ComplexNestedEvents\SubscriberH as ComplexCaseSubscriberH;
use Zippovich2\EventDispatcher\Tests\Fixtures\PekariumCase\BalanceUpdateSubscriber;
use Zippovich2\EventDispatcher\Tests\Fixtures\PekariumCase\TransactionSubscriber;
use Zippovich2\EventDispatcher\Tests\Fixtures\Subscriber;
use Zippovich2\EventDispatcher\Tests\Fixtures\SubscriberWithTraceableEventDispatcher;
use Zippovich2\EventDispatcher\TraceableEventDispatcher;

class TraceableEventDispatcherTest extends TestCase
{
    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    protected function setUp(): void
    {
        $this->dispatcher = new TraceableEventDispatcher(new EventWatcher());
    }

    protected function tearDown(): void
    {
        $this->dispatcher = null;
    }

    public function testInitialState(): void
    {
        static::assertEquals([], $this->dispatcher->getSubscribers());
        static::assertEquals([], $this->dispatcher->getCallStack());
        static::assertEquals([], $this->dispatcher->getCallStackTree());
    }

    public function testGetCallStack(): void
    {
        $subscriber = new Subscriber();

        $this->dispatcher->subscribe('event1', [$subscriber, 'callback1']);
        $this->dispatcher->subscribe('event1', [$subscriber, 'callback2']);
        $this->dispatcher->subscribe('event2', [$subscriber, 'callback1']);

        $this->dispatcher->dispatch('event1');
        $this->dispatcher->dispatch('event2');

        static::assertEquals(
            [
                [\sprintf('%s::%s', \get_class($subscriber), 'callback1'), 'event1'],
                [\sprintf('%s::%s', \get_class($subscriber), 'callback2'), 'event1'],
                [\sprintf('%s::%s', \get_class($subscriber), 'callback1'), 'event2'],
            ],
            $this->dispatcher->getCallStack()
        );
    }

    public function testGetCallStackWithNestedDispatch(): void
    {
        $subscriber = new SubscriberWithTraceableEventDispatcher($this->dispatcher);

        $this->dispatcher->subscribe('event1', [$subscriber, 'callback1']);
        $this->dispatcher->subscribe('event1', [$subscriber, 'callback2']);
        $this->dispatcher->subscribe('event2', [$subscriber, 'callback2']);

        $this->dispatcher->dispatch('event1');

        static::assertEquals(
            [
                [\sprintf('%s::%s', \get_class($subscriber), 'callback1'), 'event1'],
                [\sprintf('%s::%s', \get_class($subscriber), 'callback2'), 'event2'],
                [\sprintf('%s::%s', \get_class($subscriber), 'callback2'), 'event1'],
            ],
            $this->dispatcher->getCallStack()
        );
        static::assertEquals(1, $subscriber->callback1InvokedTimes);
        static::assertEquals(2, $subscriber->callback2InvokedTimes);
    }

    public function testGetCallStackTree(): void
    {
        $subscriber = new SubscriberWithTraceableEventDispatcher($this->dispatcher);

        $this->dispatcher->subscribe('event1', [$subscriber, 'callback1']);
        $this->dispatcher->subscribe('event1', [$subscriber, 'callback2']);
        $this->dispatcher->subscribe('event2', [$subscriber, 'callback2']);

        $callStackTree = $this->dispatcher->dispatch('event1');

        $expected = [
            [
                'subscriber' => \sprintf('%s::%s', \get_class($subscriber), 'callback1'),
                'children' => [
                    [
                        'subscriber' => \sprintf('%s::%s', \get_class($subscriber), 'callback2'),
                    ],
                ],
            ],
            [
                'subscriber' => \sprintf('%s::%s', \get_class($subscriber), 'callback2'),
            ],
        ];

        static::assertEquals($expected, $callStackTree);
        static::assertEquals(1, $subscriber->callback1InvokedTimes);
        static::assertEquals(2, $subscriber->callback2InvokedTimes);
    }

    public function testResetStack(): void
    {
        $subscriber = new Subscriber();

        $this->dispatcher->subscribe('event1', [$subscriber, 'callback1']);

        $this->dispatcher->dispatch('event1');

        static::assertNotEquals([], $this->dispatcher->getCallStack());
        static::assertNotEquals([], $this->dispatcher->getCallStackTree());

        $this->dispatcher->resetCallStack();

        static::assertEquals([], $this->dispatcher->getCallStack());
        static::assertEquals([], $this->dispatcher->getCallStackTree());
    }

    public function testCallableFormats(): void
    {
        $subscriber = new SubscriberWithTraceableEventDispatcher($this->dispatcher);

        $closure = function () {};

        $this->dispatcher->subscribe('event1', \sprintf('%s::%s', \get_class($subscriber), 'staticCallBack'));
        $this->dispatcher->subscribe('event1', [$subscriber, 'callback1']);
        $this->dispatcher->subscribe('event1', [$subscriber, 'callback2']);
        $this->dispatcher->subscribe('event1', $closure);

        $this->dispatcher->dispatch('event1');

        static::assertEquals(
            [
                'event1' => [
                    \sprintf('%s::%s', \get_class($subscriber), 'staticCallBack'),
                    [$subscriber, 'callback1'],
                    [$subscriber, 'callback2'],
                    $closure,
                ],
            ],
            $this->dispatcher->getSubscribers()
        );

        static::assertEquals(
            [
                [\sprintf('%s::%s', \get_class($subscriber), 'staticCallBack'), 'event1'],
                [\sprintf('%s::%s', \get_class($subscriber), 'callback1'), 'event1'],
                [\sprintf('%s::%s', \get_class($subscriber), 'callback2'), 'event1'],
                ['closure', 'event1'],
            ],
            $this->dispatcher->getCallStack()
        );
    }

    public function testPekariumCase(): void
    {
        $transactionSubscriber = new TransactionSubscriber($this->dispatcher);
        $balanceUpdateSubscriber = new BalanceUpdateSubscriber($this->dispatcher);

        $this->dispatcher->subscribe('TransactionCompletedEvent', [$transactionSubscriber, 'creditBalance']);
        $this->dispatcher->subscribe('TransactionCompletedEvent', [$transactionSubscriber, 'calculateBonuses']);
        $this->dispatcher->subscribe('TransactionCompletedEvent', [$transactionSubscriber, 'sendEmail']);
        $this->dispatcher->subscribe('BonusTransactionCompletedEvent', [$transactionSubscriber, 'creditBalance']);
        $this->dispatcher->subscribe('BalanceUpdatedEvent', [$balanceUpdateSubscriber, 'notifyUser']);

        $callStackTree = $this->dispatcher->dispatch('TransactionCompletedEvent');

        $expected = [
            [
                'subscriber' => \sprintf('%s::%s', \get_class($transactionSubscriber), 'creditBalance'),
                'children' => [
                    [
                        'subscriber' => \sprintf('%s::%s', \get_class($balanceUpdateSubscriber), 'notifyUser'),
                    ],
                ],
            ],
            [
                'subscriber' => \sprintf('%s::%s', \get_class($transactionSubscriber), 'calculateBonuses'),
                'children' => [
                    [
                        'subscriber' => \sprintf('%s::%s', \get_class($transactionSubscriber), 'creditBalance'),
                        'children' => [
                            [
                                'subscriber' => \sprintf('%s::%s', \get_class($balanceUpdateSubscriber), 'notifyUser'),
                            ],
                        ],
                    ],
                ],
            ],
            [
                'subscriber' => \sprintf('%s::%s', \get_class($transactionSubscriber), 'sendEmail'),
            ],
        ];

        static::assertEquals($expected, $callStackTree);
        static::assertEquals(2, $transactionSubscriber->creditBalanceInvokedTimes);
        static::assertEquals(1, $transactionSubscriber->calculateBonusesInvokedTimes);
        static::assertEquals(1, $transactionSubscriber->sendEmailInvokedTimes);
        static::assertEquals(2, $balanceUpdateSubscriber->notifyUserInvokedTimes);

        static::assertTrue(true);
    }

    public function testBug1()
    {
        $subscriberA = new SubscriberA($this->dispatcher);
        $subscriberB = new SubscriberB($this->dispatcher);
        $subscriberC = new SubscriberC($this->dispatcher);
        $subscriberD = new SubscriberD($this->dispatcher);

        $this->dispatcher->subscribe('A', [$subscriberA, 'handle']);
        $this->dispatcher->subscribe('B', [$subscriberB, 'handle']);
        $this->dispatcher->subscribe('C', [$subscriberC, 'handle']);
        $this->dispatcher->subscribe('D', [$subscriberD, 'handle']);

        $callStackTree = $this->dispatcher->dispatch('A');

        $expected = [
            [
                'subscriber' => \sprintf('%s::%s', \get_class($subscriberA), 'handle'),
                'children' => [
                    [
                        'subscriber' => \sprintf('%s::%s', \get_class($subscriberB), 'handle'),
                        'children' => [
                            [
                                'subscriber' => \sprintf('%s::%s', \get_class($subscriberD), 'handle'),
                            ],
                        ],
                    ],
                    [
                        'subscriber' => \sprintf('%s::%s', \get_class($subscriberC), 'handle'),
                    ],
                ],
            ],
        ];

        static::assertEquals($expected, $callStackTree);
    }

    /**
     * Testing nested tree:
     *  -A
     *  --B
     *  ---C
     *  --D
     *  -A
     *  --E
     *  ---B
     *  ----C
     *  --F
     *  --G
     *  --H
     *  ---E
     *  ----B
     *  -----C.
     */
    public function testGetCallStackTreeWithComplexNestedEvents(): void
    {
        $subscriberA = new ComplexCaseSubscriberA($this->dispatcher);
        $subscriberB = new ComplexCaseSubscriberB($this->dispatcher);
        $subscriberC = new ComplexCaseSubscriberC($this->dispatcher);
        $subscriberD = new ComplexCaseSubscriberD($this->dispatcher);
        $subscriberE = new ComplexCaseSubscriberE($this->dispatcher);
        $subscriberF = new ComplexCaseSubscriberF($this->dispatcher);
        $subscriberG = new ComplexCaseSubscriberG($this->dispatcher);
        $subscriberH = new ComplexCaseSubscriberH($this->dispatcher);

        $this->dispatcher->subscribe('A', [$subscriberA, 'handle']);
        $this->dispatcher->subscribe('A', [$subscriberA, 'handle2']);
        $this->dispatcher->subscribe('B', [$subscriberB, 'handle']);
        $this->dispatcher->subscribe('C', [$subscriberC, 'handle']);
        $this->dispatcher->subscribe('D', [$subscriberD, 'handle']);
        $this->dispatcher->subscribe('E', [$subscriberE, 'handle']);
        $this->dispatcher->subscribe('F', [$subscriberF, 'handle']);
        $this->dispatcher->subscribe('G', [$subscriberG, 'handle']);
        $this->dispatcher->subscribe('H', [$subscriberH, 'handle']);

        $callStackTree = $this->dispatcher->dispatch('A');

        $expected = [
            [
                'subscriber' => \sprintf('%s::%s', \get_class($subscriberA), 'handle'),
                'children' => [
                    [
                        'subscriber' => \sprintf('%s::%s', \get_class($subscriberB), 'handle'),
                        'children' => [
                            [
                                'subscriber' => \sprintf('%s::%s', \get_class($subscriberC), 'handle'),
                            ],
                        ],
                    ],
                    [
                        'subscriber' => \sprintf('%s::%s', \get_class($subscriberD), 'handle'),
                    ],
                ],
            ],
            [
                'subscriber' => \sprintf('%s::%s', \get_class($subscriberA), 'handle2'),
                'children' => [
                    [
                        'subscriber' => \sprintf('%s::%s', \get_class($subscriberE), 'handle'),
                        'children' => [
                            [
                                'subscriber' => \sprintf('%s::%s', \get_class($subscriberB), 'handle'),
                                'children' => [
                                    [
                                        'subscriber' => \sprintf('%s::%s', \get_class($subscriberC), 'handle'),
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'subscriber' => \sprintf('%s::%s', \get_class($subscriberF), 'handle'),
                    ],
                    [
                        'subscriber' => \sprintf('%s::%s', \get_class($subscriberG), 'handle'),
                    ],
                    [
                        'subscriber' => \sprintf('%s::%s', \get_class($subscriberH), 'handle'),
                        'children' => [
                            [
                                'subscriber' => \sprintf('%s::%s', \get_class($subscriberE), 'handle'),
                                'children' => [
                                    [
                                        'subscriber' => \sprintf('%s::%s', \get_class($subscriberB), 'handle'),
                                        'children' => [
                                            [
                                                'subscriber' => \sprintf('%s::%s', \get_class($subscriberC), 'handle'),
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        static::assertEquals($expected, $callStackTree);
    }
}
