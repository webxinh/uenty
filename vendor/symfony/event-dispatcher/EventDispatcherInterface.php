<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\EventDispatcher;


interface EventDispatcherInterface
{
    
    public function dispatch($eventName, Event $event = null);

    
    public function addListener($eventName, $listener, $priority = 0);

    
    public function addSubscriber(EventSubscriberInterface $subscriber);

    
    public function removeListener($eventName, $listener);

    
    public function removeSubscriber(EventSubscriberInterface $subscriber);

    
    public function getListeners($eventName = null);

    
    public function getListenerPriority($eventName, $listener);

    
    public function hasListeners($eventName = null);
}
