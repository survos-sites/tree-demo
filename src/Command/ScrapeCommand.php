<?php

namespace App\Command;

use App\Services\ImportService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ScrapeCommand extends Command
{

    private $importService;

    public function __construct(ImportService $importService, string $name = null)
    {
        parent::__construct($name);
        $this->importService = $importService;
    }

    protected function configure()
    {
        $this->setName('phenx:scrape')
            ->setDescription('Scrape PhenxToolkit.org for data')
            // ->addOption('Protocol', null, InputOption::VALUE_NONE, 'If set, get the protocols')
            // ->addOption('Measure', null, InputOption::VALUE_NONE, 'If set, get the measures')
            ->addOption('scrape', null, InputOption::VALUE_NONE, 'If set, scrape')
            ->addOption('process', null, InputOption::VALUE_NONE, 'If set, process whatever has been scraped')
            ->addOption('table', null, InputOption::VALUE_OPTIONAL, 'Which Table')
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Number of records', 20);

        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $service ImportService */
        $service = $this->importService;

        if ($table = $input->getOption('table')) {
            $tables = array($table);
        } else {
            $tables = array('Measure','Protocol');
        }

        if (!$input->getOption('scrape') && !$input->getOption('process')) {
            die("Specify either --scrape or --process\n");
        }

        foreach ($tables as $table) {
            $output->writeln("<info>$table</info>");

            if ($input->getOption('scrape')) {
                $output->writeln("<info>Scraping $table</info>");
                $service->scrape($table, $input->getOption('limit'));
            }

            if ($input->getOption('process')) {
                $output->writeln("<info>Processing $table</info>");
                switch ($table) {
                    case 'Protocol':
                        $service->processProtocol($input->getOption('limit'));
                        break;
                    case 'Measure':
                        $service->processMeasure($input->getOption('limit'));
                        break;
                }
            }
            $output->writeln("<info>Done</info>");
        }
        return 0;
    }
}
