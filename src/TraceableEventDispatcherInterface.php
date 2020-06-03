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

interface TraceableEventDispatcherInterface extends EventDispatcherInterface
{
    /**
     * Dispatches an event to all registered subscribers.
     *
     * @param string $event The event to pass to the event subscribers
     *
     * @return array The callstack tree
     */
    public function dispatch(string $event): array;

    /**
     * @return array The callstack in raw format sorted by call
     */
    public function getCallStack(): array;

    /**
     * @return array The callstack tree
     */
    public function getCallStackTree(): array;

    /**
     * Reset callstack.
     */
    public function resetCallStack(): void;
}
