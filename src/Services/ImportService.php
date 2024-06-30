<?php

namespace App\Services;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use PhenxBundle\Entity\Domain;
use PhenxBundle\Entity\Measure;
use PhenxBundle\Entity\PhenxProtocol;
use PhenxBundle\Entity\Variable;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\RouterInterface;

class ImportService
{
    private $csvFile;

    /** @var EntityManager $em */
    private $em;

    public function __construct(private RouterInterface $router,
                                EntityManagerInterface $em, $rootPath = '', $csvFile = '')
    {
        $this->em = $em;
        // $dir = new FileLocator([$rootPath.'/Resources/data']); // , __DIR__.'/../Resources/data']);
        // $this->csvFile = $dir->locate($csvFile);
    }

    public function getJsonUrl(PhenxProtocol $protocol)
    {
        return $this->router->generate('phenx_protocol_json', ['phenxId' => $protocol->getPhenxId()], $router::ABSOLUTE_URL);
    }


    public function getVariablesFile()
    {
        return $this->csvFile;
    }

    public function setVariablesFile($v)
    {
        $this->csvFile = $v;
        return $this;
    }

    public function importTree($import_type = 'domain', Output $output = null, $refresh = null)
    {
        // creates the csv file to be imported by import_class (to phenx_data)
        //
        $path = "https://www.phenxtoolkit.org/tree.php";
        $kernel = $this->container->get('kernel');
        $cache_path = $kernel->locateResource('@PhenxBundle/Resources/data/tree.php.html');

        if ($refresh) {
            $output->writeln(sprintf("Getting tree from %s\n", $path));
            $data = file_get_contents($path);
            file_put_contents($cache_path, $data);
        } else {
            $output->writeln(sprintf("Getting cached tree from %s\n", $cache_path));
            $data = file_get_contents($cache_path);
        }

        // hack out everything after Measures <span class="tct">
        $data = preg_replace('{Measures <span class="tct">.*}s', '', $data);
        if (preg_match_all('{<a href="b\.php\?i=(\d+)">([^<]+)</a>}', $data, $mm, PREG_SET_ORDER)) {
            $progress = new ProgressBar($output);
            $progress->start(count($mm));

            $c = 0;
            $domain_id = $measure_id = 0;
            foreach ($mm as $m) {
                $progress->advance();
                // if % 1000, it's a domain, if % 100 a measure, else proto
                $id = $m[1];
                $text = $m[2];
                if (($id % 10000) == 0) {
                    $domain_id = $id;
                    $type = 'domain';
                } elseif ($id % 100 == 0) {
                    $type = 'measure';
                    $measure_id = $id;
                    // ignore measures
                } else {
//                    $protocol_id = $id;
                    $type = 'protocol';
                }
                // printf("\n%s: id-%d, d-%d m-%d p-%d", $m[0], $id, $domain_id, $measure_id, $protocol_id);

                if ($type != $import_type) {
                    continue;
                }

                // tweak the text
                $text = preg_replace('{Protocol \d: }', '', $text);

                $text = preg_replace('{ protocol}i', '', $text); // redundant

                $domainRepo = $this->em->getRepository(Domain::class);
                $measureRepo = $this->em->getRepository(Measure::class);


                switch ($type) {
                    case 'protocol':
                        /** @var PhenxProtocol $p */
                        $p =  $this->em->getRepository(PhenxProtocol::class)->findOneOrCreate(['phenxId' => $id]);
                        // if the measure has multiple protocols, we need to distiguish them, often in parents, e.g. (adult)
                        if (preg_match('{\(([^)]+)\)}', $text, $m)) {
                            $subTitle = $m[1];
                        } else {
                            $subTitle = "all";
                        }
                        $p
                            ->setMeasure($this->em->getReference(Measure::class, $measure_id))
                            // ->setDomainId($domain_id)
                            ->setTitle($text)
                            ->setSubTitle($subTitle);
                        $c++;
                        break;
                    case 'measure':
                        /** @var Measure $d */
                        $d = $measureRepo->findOneOrCreate(['phenxId' => $id]);
                        $d
                            ->setTitle($text)
                            ->setDomain($this->em->getReference(Domain::class, $domain_id));
                        // print "$domain_id $id $text.\n";
                        $c++;
                        break;
                    case 'domain':
                        $d = $domainRepo->findOneOrCreate(['phenxId' => $id]);
                        $d
                            ->setTitle($text);
                        $c++;
                        break;
                } // switch
                // if ($c > 15) break;
            }
        }
        $this->em->flush();
        $progress->finish();

        return $c;
        // file_put_contents($fn="/usr/sites/sr/scripts/data/phenx_data.txt", $csv);
        // printf("%s written.", $fn);
    }

    public function deleteVariables()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $q = $em
            ->getRepository(Variable::class)
            ->createQueryBuilder('v')
            ->delete()
            ;
        $q->getQuery()->execute();
        $em->flush();
        return true; // ??
    }

    public function deleteDomains()
    {
        throw new \Exception("Move to doctrine");

    }

    public function importVariables(OutputInterface $output, $limit = 20, $startingAt = 0)
    {
        $lines_command = sprintf('wc -l %s', $this->csvFile);
        $lines = system($lines_command);
        $progress = new ProgressBar($output, (int)$lines);
        $progress->start();
        $progress->setProgress($startingAt);
        $progress->setRedrawFrequency(25);

        $reader = new \EasyCSV\Reader($this->csvFile);
        $reader->setForceUtf8(true);
        $reader->setUnnamedExtraDataVar('CHOICES');
        // prepare the statement based on which fields are being imported
        // $stmt = $db->prepare("insert into <table> set ... = ")

//        $con = \Propel::getConnection(DomainPeer::DATABASE_NAME);
        // $con->beginTransaction();

        $c = 0;
        $reader->advanceToRow($startingAt);

        while ($d = $reader->getRow()) {
            if ($reader->hasError()) {
                $output->writeln($reader->getError(), OutputInterface::OUTPUT_NORMAL);
                continue;
            }
            if (!empty($d['VALUES'])) {
                array_unshift($d['CHOICES'], $d['VALUES']);
            }
            unset($d['']);
            // unset($d['EXTRA']);
            $d = (object)$d;

            if (empty($d->VARNAME)) {
                printf("Empty VARNAME");
                //var_dump($d);
                continue; // bad data
            }

            if (preg_match('{PX(\d\d\d+)\.?_(.*?)$}', $d->VARNAME, $m)) {
                $protocol_id = $m[1];
                $d->VARNAME = strtolower($m[2]);
            } else {
                $output->writeln(sprintf("Error finding protocol_id in %s  row %d\n", $d->VARNAME, $reader->getLineNumber()));
                continue;
            }

            if (!isset($protocol) || ($protocol->getPhenxId() <> $protocol_id)) {
                /** @var $protocol PhenxProtocol */
                $protocol = $this->em->getRepository(PhenxProtocol::class)
                    // ->findOneBy(['phenxId' => $protocol_id]))
                    ->findOneBy(['phenxId' => $protocol_id]);
                if (!$protocol)
                {
                    // if ($protocol->getMeasure()) {

                        // by default set it to the previous measure?
                        $output->writeln("Skipping protocol_id in ".$d->VARNAME."\n");
                        dump($d);
                        continue; // skip if no protocol until we handle collections properly/

                    // dump($protocol);  die("Stopped at Protocol $protocol_id");
                }
                $orderIdx = 1;
            }
            /** @var Variable $v */
                $v = $this->em->getRepository(Variable::class)
                    ->findOneOrCreate([
                        'varname' => substr($d->VARNAME, 0, 64),
                        'protocol' => $protocol
                    ]);

                $output->writeln(sprintf("   creating %s in %s (%s) \n", $d->VARNAME, $protocol->getPhenxId(), $protocol_id), OutputInterface::VERBOSITY_VERY_VERBOSE);

                $choices = $d->CHOICES;
                $choiceFormula = null;
            if ( (count($choices) == 1) && is_string($choices[0]) && (strstr($choices[0], '..'))) {
                // look for min..max range?
                $choiceFormula = $choices[0];
                $choices = [];
            } elseif ($d->TYPE == 'enumerated') {
                $choiceFormula = join(';', array_map(
                    function ($choice) {
                        return sprintf("%s=%s", Utility::displayToCode($choice), $choice);
                    }, $choices));
                $choices = [];
            } else {
                $choiceFormula = join(';', $choices);
                $choices = [];
            }


            $v
                    ->setOrderIdx($orderIdx++);
                $v
                    // ->setProtocolId($protocol_id)
                    ->setQuestionText($d->VARDESC)// substr($d->VARDESC, 0, 128)) //????
                    ->setChoices($choices)
                    ->setChoiceFormula($choiceFormula)
                    ->setDescription($d->VARDESC)
                    ->setType($d->TYPE);
                foreach (['UNITS', 'MIN', 'MAX', 'RESOLUTION', 'COMMENT1', 'COMMENT2'] as $field) {
                    if ($d->$field !== '')
                    {
                        $v->setExtra($field, $d->$field);
                    }

            }

                /*
                $v
                    ->setUnits($d->UNITS)
                    ->setMin($d->MIN)
                    ->setMax($d->MAX)
                    ->setDocFile($d->DOCFILE)
                    ->setResolution($d->RESOLUTION)
                    ->setComment1($d->COMMENT1)
                    ->setComment2($d->COMMENT2)
                    ->setVariableSource($d->VARIABLE_SOURCE)
                    ->setVariableTerm($d->VARIABLE_TERM)
                    ->setValues($d->VALUES)
                    ->save();
                */
            try {
            } catch (\Exception $e) {
                //var_dump($d);
                print "Died on line $c\n";
                //var_dump($e);
                die();
            }
            $c++;
            if ($limit && $c > $limit) {
                break;
            }
            $progress->advance();
        }
        try {
            $this->em->flush();
            // $con->commit();
        } catch (\Exception $e) {
            //var_dump($e);
            dump($d);
            // die($e->getMessage() . "\n");
        }
        $progress->finish();

        return $c;
    }

    public function scrape($class, $limit)
    {
        $cache = new FilesystemAdapter();
        switch ($class) {
            case 'Measure':
                $repo = $this->em->getRepository(Measure::class);
                break;
            case 'Protocol':
                $repo = $this->em->getRepository(PhenxProtocol::class);
                break;
            default:
                throw new \Exception("Invalid table: $class, valid tables are Measure and Protocol");
        }
        $records = $repo->findBy([], ['phenxId' => 'ASC'], $limit);
        foreach ($records as $p) {
            $url = $this->getSourceUrl($class, $p->getPhenxId());
            printf("%s%s", $url, PHP_EOL);

// retrieve the cache item
            $cachedPage = $cache->getItem('urls.' . md5($url));
            if (!$cachedPage->isHit()) {
                $page = file_get_contents($url);
                $cachedPage->set($page);
                $cache->save($cachedPage);
            }
            $p->setHtmlPage($page = $cachedPage->get());


            /* alas, we don't have access to this anymore!
            $cache = $this->cache();
            $page = trim($cache->get($url));
            */

            if ($page) {
                // where did cache->info go?  Probably part of http_get
                /*
                if (isset($cache->info) && $cache->info && isset($cache->info->cache->in_cache)) {
                    printf("in cache%s", PHP_EOL);
                }
                */
                if (preg_match('{<!-- START: CONTENT -->(.*?)<!--   END: CONTENT -->}s', $page, $m)) {
                    $content = $m[1];
                    $content = preg_replace('{<script language="JavaScript">.*?</script>}is', '', $content);
                    $content = str_replace(0xbf, '*', $content);
                    $content = preg_replace('/[[:^print:]]/', '', $content);
                    // get rid of everything between the Measure and the footer
                    if ($class == 'Measure') {
                        $content = preg_replace('{.*<span class="definitionTitle">Measure:</span>}s', '', $content);
                        $content = preg_replace('{<p style="margin-top:30px;margin-bottom:10px;">.*}s', '', $content);
                        $content = preg_replace('{<img.*?>}is', '', $content);
                    }

                    $p->setMeta('Content', Utility::utf8_to_ascii($content));
                } else {
                    print("Missing Content on page");
                }
            } else {
                printf("ERROR: %s returns a blank page\n", $url);
            }

            // print $content; die();
        }
        $this->em->flush();
    }

    public function getSourceUrl($table, $id)
    {
        static $base = 'https://www.phenxtoolkit.org/index.php';
        $base_url = [
            'Measure'  => $base."?pageLink=browse.protocols&id=",
            'Protocol' => $base."?pageLink=browse.protocoldetails&id=",
        ];

        return $base_url[$table].$id;
    }

    public function processProtocol($limit)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository(PhenxProtocol::class);

        $query = $repo->createQueryBuilder('protocol')
            // ->where(ProtocolQuery::getModelAliasOrName() . '.Content IS NOT NULL')
            // ->filterByContent(null)
            ->where('protocol.htmlPage IS NOT Null')
            ->getQuery();

        $results = $query->getResult();
        printf("%d records found.\n", count($results));
        $map = [
            'SPECIFIC_INSTRUCTIONS'       => 'Instructions',
            'SOURCE'                      => 'Source',
            'PERSONNEL_AND_TRAINING_REQD' => 'Personnel',
            'EQUIPMENT_NEEDS'             => 'Equipment',
            'REFERENCES'                  => 'LiteratureReferences',
            'SELECTION_RATIONALE'         => 'Rationale',
            'DESCRIPTION'                 => 'Description',
            'PROTOCOL_TEXT'               => 'ProtocolText',
        ];
        /** @var PhenxProtocol $protocol */
        foreach ($results as $protocol) {
            $str = $protocol->getHtmlPage();
            foreach ($map as $var => $property) {
                if (!preg_match($pattern = "{id=\"element_$var\"([^>]*)>(.*?)</div>\n?<(a name|p style)}s", $str)) {
                    die("Can't even find $pattern");
                }
                // print "$pattern is ok"; die();
                if (preg_match($pattern = "{id=\"element_$var\"([^>]*)>(.*?)</div>\n?<(a name|p style)}s", $str, $mm)) {
                    /*
                    //var_dump($mm);
                    $$var = trim($mm[1]);
                    $val = trim($mm[1]);
                    $z[] = sprintf("`%s`='%s'", strtolower($var), addslashes($val));
                    */
                    $val = trim($mm[2]);
                    // should probably be any HTML, span, div, etc.
                    $val = preg_replace('{$<p>.*?</p>$}i', '', $val);
                    $method = 'set'.$property;
                    if (method_exists($protocol, $method)) {
                        $protocol->$method($val);
                    } else {
                        $protocol->setMeta($property, $val);
                    }
//                    $data[$var] = $mm[2];
                } else {
                    printf("Can't find $pattern in<br/>%s</br><textarea>%s</textarea>", htmlspecialchars($str), $str);
                    die();
                }
            }
            print ".";
        }
        $em->flush();
    }

    public function processMeasure($limit)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository(Measure::class);

        $query = $repo->createQueryBuilder('measure')
            // ->where(ProtocolQuery::getModelAliasOrName() . '.Content IS NOT NULL')
            // ->filterByContent(null)
            ->where('measure.htmlPage IS NOT Null')
            ->getQuery();

        $results = $query->getResult();

        $map = [
            'Definition'           => 'Definition',
            'Purpose'              => 'Purpose',
            'Keywords'             => 'Keywords',
            // 'Collections' => 'CollectionsHtml', not worth the effort, get it from tree.php
            // 'Protocols' => 'ProtocolsHtml', it's just for backup, but not worth it.
            'Measure Release Date' => 'ReleaseDate',
        ];

        printf("%d measures found.\n", count($results));
        /** @var $measure Measure */
        foreach ($results as $measure) {
            // get rid of the JS, too messy
            $body = $measure->getHtmlPage();
            $body = preg_replace('{^.*<!-- START: CONTENT -->}s', '', $body);
            $body = preg_replace('{<script.*?/script>}s', '', $body);
            // get Definition, Purpose, Keywords
            foreach ($map as $var => $property) {
                if (preg_match($pattern = "{<p><b>$var</b>:([^<]*)</p>}", $body, $mm)) {
                    $val = trim($mm[1]);
                    $measure->setMeta($var, $val);
                    /*
                    $method = 'set' . $property;
                    $measure->$method($val);
                    */
                } else {
                    printf("Warning: Can't find $pattern in\nMeasure %d\n%s", $measure->getId(), $body);
                }
            }
            print ".";
        }
        $em->flush();

        return [];
    }

    public function protocolToSurvey(PhenxProtocol $protocol): Survey
    {
        $surveyObj = (object)
        [
            // 'code' => $protocol->getCode()
            'title'       => $protocol->createTitle(),
            'description' => $protocol->getMeta('Rationale').sprintf(" %d Questions", $protocol->getVariables()->count()),
            'name'        => 'x' . $protocol->createTitle(),
            'code'        => 'phenx_' . $protocol->getPhenxId()
            // 'jobTypes'  => JobTypeQuery::create()->findByCode(JobType::TURK_TURK_OPINION)
        ];
        $typeMap = [
            'Encoded values' => 'radio',
            'Integer'        => 'number',
            'Decimal'        => 'number',
            'Time'           => 'number', // ??
            'Enumerated'     => 'radio', // might be radio, or even boolean (can be used for checkboxes)
            'String'         => 'text',
            'Date'         => 'date',
        ];




        /** @var Variable $v*/
        foreach ($protocol->getVariables() as $v) {
            // the goal is to build up an object that has the same structure as
            // http://utest.l.survos.net/app_dev.php/api/SURVEY_data
            // then pass it to the converter
            $choices = $v->getChoices();

            $q = (object)[
                'code'    => $v->getVarname(),
                'type'    => $typeMap[ucfirst($v->getType())],
                'text'    => $v->getQuestionText(),
                'choices' => $choices,
                'choiceFormula' => $v->getChoiceFormula()
            ];
            $surveyObj->questions[] = $q;
        }

        $repo = $this->container->get('doctrine.orm.entity_manager')->getRepository(Survey::class);
        /** @var $survey Survey */
        $survey = new Survey();
        $survey = $repo->setFromData($survey, $surveyObj, true);
        $survey
            ->setName(substr($protocol->createTitle(), 0, 64));

        return $survey;
    }
} // class
