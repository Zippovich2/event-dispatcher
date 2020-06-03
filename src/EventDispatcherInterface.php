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

namespace Zippovich2\EventDispatcher;

interface EventDispatcherInterface
{
    /**
     * Adds an event subscriber.
     *
     * @param string   $event      The event to subscribe on
     * @param callable $subscriber The subscriber
     */
    public function subscribe(string $event, callable $subscriber);

    /**
     * Dispatches an event to all registered subscribers.
     *
     * @param string $event The event to pass to the event subscribers
     */
    public function dispatch(string $event);

    /**
     * Gets the subscribers of a specific event or all subscribers.
     *
     * @param string|null $event The name of the event
     *
     * @return array The subscribers for the specified event or all subscribers
     */
    public function getSubscribers(?string $event): array;
}
