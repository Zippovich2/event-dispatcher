<?php

/*
 * This file is part of the "Event Dispatcher" library.
 *
 * (c) Skoropadskyi Roman <zipo.ckorop@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zippovich2\EventDispatcher\Tests\Fixtures\Bug1;

use Zippovich2\EventDispatcher\Tests\Fixtures\AbstractSubscriber;

class SubscriberA extends AbstractSubscriber
{
    public function handle()
    {
        $this->dispatcher->dispatch('B');
        $this->dispatcher->dispatch('C');
    }
}
