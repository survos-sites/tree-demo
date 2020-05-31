<?php

namespace App\Command;

use App\Services\ImportService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

// https://www.phenxtoolkit.org/index.php?pageLink=help.dcwsupport
// to stay current, this would unzip the files from
// https://www.phenxtoolkit.org/toolkit_content/documents/data_dictionary/ALL_DD_CSV_Files.zip

class ImportCommand extends Command
{
    private $importService;

    public function __construct(ImportService $importService, string $name = null)
    {
        parent::__construct($name);
        $this->importService = $importService;
    }

    protected function configure()
    {
        $this->setName('phenx:dd')
            ->setDescription('Imports the Data Dictionary from Phenx')
            // ->addArgument('crudbundle', InputArgument::REQUIRED, 'Name of generated bundle (e.g. CrudTobaccoFDABundle)')
            //->addArgument('schema', InputArgument::OPTIONAL, 'Name of schema file to generate from', '')
            ->addOption('variables', null, InputOption::VALUE_NONE, 'If set, import the variables file (use w/limit)')
            ->addOption('tree', null, InputOption::VALUE_NONE, 'If set, import the tree first')
            ->addOption('refresh', null, InputOption::VALUE_NONE, 'If set, get latest tree file')
            ->addOption('purge', null, InputOption::VALUE_NONE, 'If set, purge the variables first')
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Number of records', 250)
            ->addOption('start', null, InputOption::VALUE_OPTIONAL, 'Starting Record', 0);;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $service = $this->importService;

        if ($importTree = $input->getOption('tree')) {
            if ($purge = $input->getOption('purge')) {
                $service->deleteDomains();
                $output->writeln("<info>Domains deleted</info>");
            }

            foreach (['domain', 'measure', 'protocol'] as $tableType) {
                $output->writeln("<info>Importing $tableType from tree</info>");
                //
                $count = $service->importTree($tableType, $output, $input->getOption('refresh'));

                $output->writeln("<info>Imported $count records into $tableType.</info>");
            }
        }

        if ($importVariables = $input->getOption('variables')) {
            if ($purge = $input->getOption('purge')) {
                $service->deleteVariables();
                $output->writeln("<info>Variables deleted</info>");
            }

            $csvFilename = './ALL_DD_04_11_2017.csv';
            if (!file_exists($csvFilename)) {

                $zipFile = 'https://www.phenxtoolkit.org/toolkit_content/documents/data_dictionary/ALL_DD_CSV_Files.zip';
                $localZipFile = './phenx.zip';
                $output->writeln("Fetching " . $zipFile . ' to ' . $localZipFile . "\n");
                // todo: set permissions
                file_put_contents($localZipFile, file_get_contents($zipFile));
                // chmod($localZipFile, '+r');

                $zip = new \ZipArchive();
                $output->writeln("extracting " . $localZipFile);
                if($zip->open($localZipFile) != "true"){
                    echo "Error :- Unable to open the Zip File";
                }
                /* Extract Zip File */
                $zip->extractTo(".");
                $zip->close();
            }

            $service->setVariablesFile($csvFilename);

            $output->writeln(sprintf("<info>Importing variables from %s</info>", $service->getVariablesFile()));

            $count = $service->importVariables($output, $input->getOption('limit'), $input->getOption('start'));
            $output->writeln("\n<info>Imported $count variables.</info>");
        }

        if (!($importTree || $importVariables)) {
            $output->writeln("Use with --tree and/or --variables");
        }

        return 0;
    }
}
