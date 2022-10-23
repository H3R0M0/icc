<?php

namespace App\Command;

use App\Event\MessageCreatedEvent;
use App\Notification\NotificationService;
use App\Repository\MessageRepositoryInterface;
use SchulIT\CommonBundle\Helper\DateHelper;
use Shapecode\Bundle\CronBundle\Attribute\AsCronJob;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCronJob('*\/5 * * * *')]
#[AsCommand('app:notifications:send', 'Sends notifications for messages which did not push any notification yet.')]
class SendPushNotifications extends Command {

    public function __construct(private DateHelper $dateHelper, private NotificationService $notificationService, private MessageRepositoryInterface $messageRepository, string $name = null) {
        parent::__construct($name);
    }

    public function execute(InputInterface $input, OutputInterface $output): int {
        $style = new SymfonyStyle($input, $output);

        $today = $this->dateHelper->getToday();
        $messages = $this->messageRepository->findAllNotificationNotSent($today);

        if(count($messages) > 0) {
            $message = $messages[0];
            $style->section(sprintf('Send notifications for message "%s"', $message->getTitle()));

            $this->notificationService->sendNotifications(new MessageCreatedEvent($message));
            $style->success(sprintf('Done (%d still queued for sending notifications)', count($messages) - 1));
        } else {
            $style->success('No messages found with unsent notifications.');
        }

        return 0;
    }
}