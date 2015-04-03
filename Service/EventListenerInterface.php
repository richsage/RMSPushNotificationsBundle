<?php
namespace RMS\PushNotificationsBundle\Service;


interface EventListenerInterface {

    public function onKernelTerminate ();
}