# Event Dispatcher

Simple event dispatcher.

## Installation
```shell script
$ composer require zippovich2/event-dispatcher
```

## Usage
1. Default usage:
    ```php
    use Zippovich2\EventDispatcher\EventDispatcher;
    
    $dispatcher = new EventDispatcher();
    
    // Add subscriber to events
    $dispatcher->subscribe('event1', 'Subscriber::callback');
    $dispatcher->subscribe('event2', 'Subscriber::callback2');
    
    // Getting event subscribers
    $event1Subscribers = $dispatcher->getSubscribers('event1');
    
    // Or get subscribers from all events
    $allSubscribers = $dispatcher->getSubscribers();
    
    // Dispatch event
    $dispatcher->dispatch('eventName');
    ```

2. Using `TraceableEventDispatcher`:
    ```php
    use Zippovich2\EventDispatcher\TraceableEventDispatcher;
    
    $dispatcher = new TraceableEventDispatcher();
    
    // Add subscriber to events
    $dispatcher->subscribe('event1', 'Subscriber::callback');
    $dispatcher->subscribe('event2', 'Subscriber::callback2');
    
    // Dispatch event return callstack tree
    $callstackTree = $dispatcher->dispatch('eventName');
       
    // Getting raw callstack
    $callStack = $dispatcher->getCallStack();
    ```