<?php

namespace Rocketsoba\EPG\Console;

use Psr\Log\LoggerInterface;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Rocketsoba\EPG\EPGScrape;

class ScrapeCommand extends Command
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName("scrape")
             ->setDescription("Scrape EPG")
             ->addArgument(
                 "date",
                 InputArgument::OPTIONAL
             )
             ->addOption(
                 "channel",
                 null,
                 InputOption::VALUE_REQUIRED,
                 "channel",
                 null
             )
             ->addOption(
                 "limit",
                 null,
                 InputOption::VALUE_REQUIRED,
                 "limit"
             );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger->pushHandler(new ConsoleHandler($output));

        $date = $input->getArgument("date");
        $channel = $input->getOption("channel");

        if (is_null($date)) {
            $scraper = new EPGScrape();
        } else {
            $scraper = new EPGScrape($date);
        }
        if (!is_null($channel)) {
            $scraper->setChannels([$channel]);
        }

        $programs = $scraper->scrape()
                            ->getPrograms();

        /**
         * array_map()は多次元配列を扱えない？
         */
        $result = "";
        foreach ($programs as $idx1 => $val1) {
            $result .= $idx1 . PHP_EOL;
            foreach ($val1 as $idx2 => $val2) {
                $result .= $val2["title"] . PHP_EOL;
                $result .= $val2["start_date"] . "~" . $val2["end_date"] . PHP_EOL;
            }
            $result .= PHP_EOL;
        }
        $output->writeln($result);

        return 0;
    }
}
