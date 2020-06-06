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

class TraceableEventDispatcher extends EventDispatcher implements TraceableEventDispatcherInterface
{
    /**
     * @var EventWatcher
     */
    private $eventWatcher;

    public function __construct(EventWatcher $eventWatcher)
    {
        $this->eventWatcher = $eventWatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(string $event): array
    {
        if (isset($this->subscribers[$event])) {
            $this->eventWatcher->startEvent($event);

            foreach ($this->subscribers[$event] as $subscriber) {
                $this->eventWatcher->addEventSubscriber($this->getCallableName($subscriber));
                $subscriber();
            }

            $this->eventWatcher->stopEvent($event);
        }

        return $this->getCallStackTree();
    }

    /**
     * {@inheritdoc}
     */
    public function getCallStack(): array
    {
        $callstack = [];

        foreach ($this->eventWatcher->getSubscribers() as $subscriber) {
            $callstack[] = [$subscriber['subscriber'], $subscriber['event']];
        }

        return $callstack;
    }

    /**
     * {@inheritdoc}
     */
    public function getCallStackTree(): array
    {
        return $this->buildCallStackTree($this->eventWatcher->getSubscribers());
    }

    /**
     * {@inheritdoc}
     */
    public function resetCallStack(): void
    {
        $this->eventWatcher->reset();
    }

    /**
     * Build call stack tree.
     *
     * @return array
     */
    private function buildCallStackTree(array $subscribers, ?string $parentEvent = null)
    {
        $tree = [];

        foreach ($subscribers as $i => $subscriber) {
            if ($subscriber['parent'] === $parentEvent) {
                $branch = ['subscriber' => $subscriber['subscriber']];

                $children = $this->buildCallStackTree($this->getNestedSubscribers($subscriber, \array_slice($subscribers, $i + 1)), $subscriber['event']);

                if (!empty($children)) {
                    $branch['children'] = $children;
                }

                $tree[] = $branch;
            }
        }

        return $tree;
    }

    private function getNestedSubscribers(array $subscriber, array $remainingSubscribers): array
    {
        $childrenSubscribers = [];

        foreach ($remainingSubscribers as $remainingSubscriber) {
            if ($remainingSubscriber['level'] <= $subscriber['level']
                || $remainingSubscriber['event'] === $subscriber['event']
            ) {
                break;
            }

            $childrenSubscribers[] = $remainingSubscriber;
        }

        return $childrenSubscribers;
    }

    /**
     * Convert callable to string.
     *
     * @return string The callable name
     */
    private function getCallableName(callable $callable): string
    {
        switch (true) {
            case \is_string($callable):
                return \trim($callable);
            case \is_array($callable):
                return \sprintf('%s::%s', \is_object($callable[0]) ? \get_class($callable[0]) : \trim($callable[0]), \trim($callable[1]));
            case $callable instanceof \Closure:
                return 'closure';
            default:
                return 'unknown';
        }
    }
}
