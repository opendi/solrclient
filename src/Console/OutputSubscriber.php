<?php

namespace Opendi\Solr\Client\Console;

use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Event\CompleteEvent;
use GuzzleHttp\Event\EmitterInterface;
use GuzzleHttp\Event\ErrorEvent;
use GuzzleHttp\Event\ProgressEvent;
use GuzzleHttp\Event\SubscriberInterface;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Guzzle subscriber which logs requests to output and renders progress bars.
 */
class OutputSubscriber implements SubscriberInterface
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var ProgressBar
     */
    private $progressBar;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function getEvents()
    {
        return [
            'before'   => ['onBefore'],
            'progress' => ['onProgress'],
            'complete' => ['onComplete'],
        ];
    }

    public function onBefore(BeforeEvent $event, $name)
    {
        // Clear any existing progress bars
        $this->progressBar = null;

        // Log the event
        $url = $event->getRequest()->getUrl();
        $method = $event->getRequest()->getMethod();
        $this->output->writeln(sprintf("<info>%s</info> %s ", $method, $url));
    }

    public function onProgress(ProgressEvent $event, $name)
    {
        $max = round($event->uploadSize / 1024);
        $current = round($event->uploaded / 1024);

        if (isset($this->progressBar)) {
            $this->progressBar->setCurrent($current);
        } elseif ($max > 0) {
            // Progress bar created first time $event->uploadSize is sent
            $this->progressBar = new ProgressBar($this->output, $max);
        }
    }

    public function onComplete(CompleteEvent $event, $name)
    {
        if (isset($this->progressBar)) {
            $this->progressBar->finish();
        }

        $this->output->writeln("");
    }
}
