<?php

namespace Cob\Bundle\ApiServicesBundle\Models;

use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

trait HasOutputTrait {
    /**
     * @var OutputInterface
     */
    private $output;

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function getOutput(): OutputInterface
    {
        if (is_null($this->output)) {
            $this->output = new NullOutput();
        }

        return $this->output;
    }
}
