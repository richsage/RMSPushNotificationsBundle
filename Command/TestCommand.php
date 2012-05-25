<?php

namespace RMS\PushNotificationsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use RMS\PushNotificationsBundle\Service\Notifications;

class TestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('rms:test')
            ->setDescription('Greet someone')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Notifications */
        $svc = $this->getContainer()->get("rms_push_notifications");
        var_dump($svc->send(Notifications::OS_ANDROID, "APA91bFjxyvRJZokVJrEtyduSbRQMPPs04iyp1mTWEGiPh-V1x_HC6a3btXxWsQjSWQkebfwHuI7swMcAMASxyrZ-CipSoM8yq1EEhbO2dXiHbF_djKl0ywBDnCR-aqE14PmNLQsjsUK2ZNoMXOFiXxfS9wEULYzh1VIhQ9zEDzo4Y8I9J7zvCs", "bar"));
    }
}
