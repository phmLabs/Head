<?php

namespace whm\Head\Cli\Command;

use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use whm\Html\Document;
use whm\Html\Uri;


class RunCommand extends Command
{
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputArgument('url', InputArgument::REQUIRED, 'url to be scanned'),
                new InputOption('children', 'c', InputOption::VALUE_NONE, 'scan all depending urls'),
            ))
            ->setDescription('returns the headers')
            ->setName('run');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = new Client();

        $url = $input->getArgument("url");
        $startResult = $client->get($url);
        $results[$url] = $startResult;

        if ($input->getOption('children')) {
            $document = new Document((string)$startResult->getBody());
            $dependendUrls = $document->getDependencies(new Uri($url), false);

            foreach ($dependendUrls as $dependendUrl) {
                $results[(string)$dependendUrl] = $client->get($dependendUrl);
            }
        }

        foreach ($results as $url => $result) {
            $this->printHeaders($result, $url, $output);
        }

    }

    private function printHeaders($result, $url, OutputInterface $output)
    {
        $headers = $result->getHeaders();

        foreach ($headers as $key => $values) {
            foreach ($values as $value) {
                $output->writeln($url . ' : ' . $key . ' : ' . $value);
            }
        }
    }
}