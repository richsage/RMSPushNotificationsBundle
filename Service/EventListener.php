<?php

namespace RMS\PushNotificationsBundle\Service;


class EventListener {

    /**
     * @var EventListenerInterface[]
     */
    protected $listeners = [];

    /**
     * @param EventListenerInterface $listener
     */
    public function addListener (EventListenerInterface $listener) {
        $this->listeners[] = $listener;
    }

    /**
     * Call onKernelTerminate on every listener
     */
    public function onKernelTerminate () {
        foreach ($this->listeners as $listener) {
            $listener->onKernelTerminate();
        }
    }
}