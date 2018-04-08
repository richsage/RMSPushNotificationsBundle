<?php

namespace RMS\PushNotificationsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;
use RMS\PushNotificationsBundle\Message as PushMessage,
    RMS\PushNotificationsBundle\Message\MessageInterface;

class TestPushCommand extends ContainerAwareCommand
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * Configures the console commnad
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName("rms:test-push")
            ->setDescription("Sends a push command to a supplied push token'd device")
            ->addOption("badge", "b", InputOption::VALUE_OPTIONAL, "Badge number (for iOS devices)", 0)
            ->addOption("text", "t", InputOption::VALUE_OPTIONAL, "Text message")
            ->addArgument("service", InputArgument::REQUIRED, "One of 'ios', 'c2dm', 'gcm', 'fcm', 'mac', 'blackberry' or 'windowsphone'")
            ->addArgument("token", InputArgument::REQUIRED, "Authentication token for the service")
            ->addArgument("payload", InputArgument::OPTIONAL, "The payload data to send (JSON)", '{"data": "test"}')
        ;
    }

    /**
     * Main command execution.
     *
     * @param  InputInterface  $input  An InputInterface instance
     * @param  OutputInterface $output An OutputInterface instance
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $token = $input->getArgument("token");
        $service = strtolower($input->getArgument("service"));
        $json_payload = $input->getArgument("payload");
        $payload = json_decode($json_payload, true);

        $tokenLengths = array(
            "ios" => 64,
            "c2dm" => 162,
        );

        if (isset($tokenLengths[$service]) && strlen($token) != $tokenLengths[$service]) {
            $output->writeln("<error>Token should be " . $tokenLengths[$service] . "chars long, not " . strlen($token) . "</error>");

            return;
        }

        if ($payload == null) {
            throw new \InvalidArgumentException("Invalid JSON payload " . $json_payload);
        }

        $msg = $this->getMessageClass($service);

        if (method_exists($msg, "setAPSBadge")) {
            // Set badge on iOS
            $msg->setAPSBadge((int) $input->getOption("badge"));
        }
        if (method_exists($msg, "setAPSSound")) {
            // Set sound on iOS
            $msg->setAPSSound("default");
        }

        $msg->setDeviceIdentifier($token);
        $msg->setData($payload);

        if ($input->getOption("text")) {
            $msg->setMessage($input->getOption("text"));
        }

        $result = $this->getContainer()->get("rms_push_notifications")->send($msg);
        if ($result) {
            $output->writeln("<comment>Send successful</comment>");
        } else {
            $output->writeln("<error>Send failed</error>");
        }

        $output->writeln("<comment>done</comment>");
    }

    /**
     * Returns a message class based on the supplied os
     *
     * @param  string                    $service The name of the service to return a message for
     * @throws \InvalidArgumentException
     * @return MessageInterface
     */
    protected function getMessageClass($service)
    {
        switch ($service) {
            case "ios":
                return new PushMessage\iOSMessage();
            case "c2dm":
                return new PushMessage\AndroidMessage();
            case "gcm":
                $message = new PushMessage\AndroidMessage();
                $message->setGCM(true);

                return $message;
            case "fcm":
                $message = new PushMessage\AndroidMessage();
                $message->setFCM(true);

                return $message;
            case "blackberry":
                return new PushMessage\BlackberryMessage();
            case "mac":
                return new PushMessage\MacMessage();
            case "windowsphone":
                return new PushMessage\WindowsphoneMessage();
            default:
                throw new \InvalidArgumentException("Service '{$service}' not supported presently");
        }
    }
}
