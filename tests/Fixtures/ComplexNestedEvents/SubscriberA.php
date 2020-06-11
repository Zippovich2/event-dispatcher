<?php

/*
 * This file is part of the "Event Dispatcher" library.
 *
 * (c) Skoropadskyi Roman <zipo.ckorop@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zippovich2\EventDispatcher\Tests\Fixtures\ComplexNestedEvents;

use Zippovich2\EventDispatcher\Tests\Fixtures\AbstractSubscriber;

class SubscriberA extends AbstractSubscriber
{
    public function handle()
    {
        $this->dispatcher->dispatch('B');
        $this->dispatcher->dispatch('D');
    }

    public function handle2()
    {
        $this->dispatcher->dispatch('E');
        $this->dispatcher->dispatch('F');
        $this->dispatcher->dispatch('G');
        $this->dispatcher->dispatch('H');
    }
}
