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

class EventDispatcher implements EventDispatcherInterface
{
    protected $subscribers = [];

    /**
     * {@inheritdoc}
     */
    public function subscribe(string $event, callable $subscriber): void
    {
        $this->subscribers[$event][] = $subscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(string $event)
    {
        if (isset($this->subscribers[$event])) {
            foreach ($this->subscribers[$event] as $subscriber) {
                $subscriber();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribers(?string $event = null): array
    {
        if (null !== $event) {
            if (!empty($this->subscribers[$event])) {
                return $this->subscribers[$event];
            }

            return [];
        }

        return $this->subscribers;
    }
}
