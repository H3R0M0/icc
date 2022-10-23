<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Shapecode\Bundle\CronBundle\Attribute\AsCronJob;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCronJob('@monthly')]
#[AsCommand('app:db:optimize', 'Optimizes all database tables using an OPTIMIZE query.')]
class OptimizeDatabaseCommand extends Command {
    public function __construct(private EntityManagerInterface $em, string $name = null) {
        parent::__construct($name);
    }

    public function execute(InputInterface $input, OutputInterface $output): int {
        $style = new SymfonyStyle($input, $output);

        $tables = $this->em->getConnection()->createSchemaManager()->listTables();

        $style->section(sprintf('Optimize %d tables', count($tables)));

        foreach($tables as $table) {
            $style->writeln('> Optimize ' . $table->getName());
            $this->em->getConnection()->executeQuery('OPTIMIZE TABLE ' . $table->getName());
        }

        $style->success('All tables optimized.');
        return 0;
    }
}