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
    private $callStack = [];

    /**
     * {@inheritdoc}
     */
    public function dispatch(string $event): array
    {
        if (isset($this->subscribers[$event])) {
            foreach ($this->subscribers[$event] as $subscriber) {
                $this->addCallToStack($subscriber, $event);
                $subscriber();
            }
        }

        return $this->getCallStackTree();
    }

    /**
     * {@inheritdoc}
     */
    public function getCallStack(): array
    {
        return $this->callStack;
    }

    /**
     * {@inheritdoc}
     */
    public function getCallStackTree(): array
    {
        return $this->buildCallStackTree($this->callStack);
    }

    /**
     * {@inheritdoc}
     */
    public function resetCallStack(): void
    {
        $this->callStack = [];
    }

    /**
     * Add subscriber and event name to call stack.
     */
    private function addCallToStack(callable $subscriber, string $eventName): void
    {
        $this->callStack[] = [$this->getCallableName($subscriber), $eventName];
    }

    /**
     * Build call stack tree from raw callstack.
     *
     * @return array
     */
    private function buildCallStackTree(array $callstack, ?string $eventName = null)
    {
        $tree = [];

        if (empty($callstack)) {
            return $tree;
        }

        if (null === $eventName) {
            $eventName = $callstack[0][1];
        }

        foreach ($callstack as $i => $call) {
            [$callbackName, $callbackEventName] = $call;

            if ($eventName === $callbackEventName) {
                $branch = ['subscriber' => $callbackName];

                // Getting children of current event - loop until current event name match
                $childCallbacks = [];

                for ($j = $i + 1; $j < \count($callstack) && $callstack[$j][1] !== $eventName; ++$j) {
                    $childCallbacks[] = $callstack[$j];
                }

                if ($childCallbacks) {
                    $branch['children'] = $this->buildCallStackTree($childCallbacks, $childCallbacks[0][1]);
                }
                $tree[] = $branch;
            }
        }

        return $tree;
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
