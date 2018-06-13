<?php
namespace Codeception\Lib\Console;

use SebastianBergmann\Comparator\ComparisonFailure;
use Symfony\Component\Console\Output\OutputInterface;


class MessageFactory
{
    
    protected $diffFactory;
    
    private $output;

    
    protected $colorizer;

    
    public function __construct(Output $output)
    {
        $this->output = $output;
        $this->diffFactory = new DiffFactory();
        $this->colorizer = new Colorizer();
    }

    
    public function message($text = '')
    {
        return new Message($text, $this->output);
    }

    
    public function prepareComparisonFailureMessage(ComparisonFailure $failure)
    {
        $diff = $this->diffFactory->createDiff($failure);
        if (!$diff) {
            return '';
        }
        $diff = $this->colorizer->colorize($diff);

        return "\n<comment>- Expected</comment> | <info>+ Actual</info>\n$diff";
    }
}
