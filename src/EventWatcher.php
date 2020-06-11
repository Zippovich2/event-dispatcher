<?php

/*
 * This file is part of the "Event Dispatcher" library.
 *
 * (c) Skoropadskyi Roman <zipo.ckorop@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zippovich2\EventDispatcher;

class EventWatcher
{
    private $events = [];

    private $subscribers = [];

    private $level = 0;

    public function startEvent(string $event)
    {
        if (\in_array($event, $this->events)) {
            throw new \LogicException(\sprintf('Event "%s" is already started.', $event));
        }

        $this->events[] = $event;
        ++$this->level;
    }

    public function stopEvent(string $event)
    {
        if (!\in_array($event, $this->events)) {
            throw new \LogicException(\sprintf('There is no events with name "%s" to stop.', $event));
        }

        \array_pop($this->events);
        --$this->level;
    }

    public function addEventSubscriber(string $subscriber)
    {
        if (0 === \count($this->events)) {
            throw new \LogicException('There is no started events to add subscriber to it.');
        }

        $this->subscribers[] = [
            'subscriber' => $subscriber,
            'event' => $this->getActiveEvent(),
            'level' => $this->level,
            'parent' => $this->getParentEvent(),
        ];
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function getSubscribers(): array
    {
        return $this->subscribers;
    }

    public function reset(): void
    {
        $this->events = [];
        $this->subscribers = [];
    }

    protected function getActiveEvent(): ?string
    {
        $activeEvent = \end($this->events);

        return $activeEvent ?: null;
    }

    protected function getParentEvent(): ?string
    {
        return 1 === \count($this->events) ? null : $this->events[\count($this->events) - 2];
    }
}
