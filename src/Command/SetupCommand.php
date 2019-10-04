<?php

namespace App\Command;

use App\Entity\DocumentVisibility;
use App\Entity\MessageVisibility;
use App\Entity\TimetablePeriodVisibility;
use App\Entity\UserType;
use App\Entity\WikiArticleVisibility;
use App\Repository\DocumentVisibilityRepositoryInterface;
use App\Repository\MessageVisibilityRepositoryInterface;
use App\Repository\TimetablePeriodVisibilityRepositoryInterface;
use App\Repository\WikiArticleVisibilityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SetupCommand extends Command {

    private $documentVisibilityRepository;
    private $messageVisibilityRepository;
    private $timetablePeriodVisibilityRepository;
    private $wikiArticleVisibilityRepository;

    private $em;

    public function __construct(DocumentVisibilityRepositoryInterface $documentVisibilityRepository, MessageVisibilityRepositoryInterface $messageVisibilityRepository,
                                TimetablePeriodVisibilityRepositoryInterface $timetablePeriodVisibilityRepository, WikiArticleVisibilityRepositoryInterface $wikiArticleVisibilityRepository,
                                EntityManagerInterface $em, string $name = null) {
        parent::__construct($name);

        $this->documentVisibilityRepository = $documentVisibilityRepository;
        $this->messageVisibilityRepository = $messageVisibilityRepository;
        $this->timetablePeriodVisibilityRepository = $timetablePeriodVisibilityRepository;
        $this->wikiArticleVisibilityRepository = $wikiArticleVisibilityRepository;

        $this->em = $em;
    }

    public function configure() {
        parent::configure();

        $this
            ->setName('app:setup')
            ->setDescription('Sets up the application.');
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $style = new SymfonyStyle($input, $output);

        $this->setupSessions($style);

        $this->addMissingDocumentVisibilities($style);
        $this->addMissingMessageVisibilities($style);
        $this->addMissingWikiVisibilities($style);
        $this->addMissingTimetablePeriodVisibilities($style);
    }

    private function addMissingVisibility(SymfonyStyle $style, string $type, array $visibilities, \Closure $newVisibilityAction) {
        $style->section(sprintf('Adding missing %s', $type));

        foreach(UserType::values() as $value) {
            if(in_array($value, $visibilities)) {
                $style->text(sprintf('%s for user type "%s" already exists', $type, $value));
            } else {
                $newVisibilityAction(new UserType($value));
                $style->text(sprintf('%s for user type "%s" added', $type, $value));
            }
        }

        $style->success(sprintf('Finished adding missing %s', $type));
    }

    private function addMissingDocumentVisibilities(SymfonyStyle $style) {
        $visibilities = array_map(function(DocumentVisibility $documentVisibility) {
                return $documentVisibility->getUserType()->getValue();
            },
            $this->documentVisibilityRepository->findAll()
        );

        $action = function(UserType $userType) {
            $visibility = (new DocumentVisibility())
                ->setUserType($userType);

            $this->documentVisibilityRepository->persist($visibility);
        };

        $this->addMissingVisibility($style, DocumentVisibility::class, $visibilities, $action);
    }

    private function addMissingMessageVisibilities(SymfonyStyle $style) {
        $visibilities = array_map(function(MessageVisibility $messageVisibility) {
            return $messageVisibility->getUserType()->getValue();
        },
            $this->messageVisibilityRepository->findAll()
        );

        $action = function(UserType $userType) {
            $visibility = (new MessageVisibility())
                ->setUserType($userType);

            $this->messageVisibilityRepository->persist($visibility);
        };

        $this->addMissingVisibility($style, MessageVisibility::class, $visibilities, $action);
    }

    private function addMissingTimetablePeriodVisibilities(SymfonyStyle $style) {
        $visibilities = array_map(function(TimetablePeriodVisibility $timetablePeriodVisibility) {
            return $timetablePeriodVisibility->getUserType()->getValue();
        },
            $this->timetablePeriodVisibilityRepository->findAll()
        );

        $action = function(UserType $userType) {
            $visibility = (new TimetablePeriodVisibility())
                ->setUserType($userType);

            $this->timetablePeriodVisibilityRepository->persist($visibility);
        };

        $this->addMissingVisibility($style, TimetablePeriodVisibility::class, $visibilities, $action);
    }

    private function addMissingWikiVisibilities(SymfonyStyle $style) {
        $visibilities = array_map(function(WikiArticleVisibility $wikiArticleVisibility) {
            return $wikiArticleVisibility->getUserType()->getValue();
        },
            $this->wikiArticleVisibilityRepository->findAll()
        );

        $action = function(UserType $userType) {
            $visibility = (new WikiArticleVisibility())
                ->setUserType($userType);

            $this->wikiArticleVisibilityRepository->persist($visibility);
        };

        $this->addMissingVisibility($style, WikiArticleVisibility::class, $visibilities, $action);
    }

    private function setupSessions(SymfonyStyle $style) {
            $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `sessions` (
    `sess_id` VARCHAR(128) NOT NULL PRIMARY KEY,
    `sess_data` BLOB NOT NULL,
    `sess_time` INTEGER UNSIGNED NOT NULL,
    `sess_lifetime` MEDIUMINT NOT NULL
) COLLATE utf8_bin, ENGINE = InnoDB;
SQL;
        $this->em->getConnection()->exec($sql);

        $style->success('Sessions table ready.');
    }
}