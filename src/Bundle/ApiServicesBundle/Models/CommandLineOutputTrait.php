<?php
/*
 * This file is part of the cobhimself/api-services-bundle package.
 *
 * (c) Collin D. Brooks <collin.brooks@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cob\Bundle\ApiServicesBundle\Models;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;

trait CommandLineOutputTrait
{

    /**
     * @var SymfonyStyle
     */
    private $io;

    /** @var ProgressBar */
    private $progressBar;

    private static $terminalWidth;

    /**
     * @return ProgressBar|null
     */
    public function getProgressBar()
    {
        return $this->progressBar;
    }

    /**
     * @param ProgressBar $progressBar
     */
    public function setProgressBar(ProgressBar $progressBar)
    {
        $this->progressBar = $progressBar;
    }

    /**
     * @return SymfonyStyle|null
     */
    public function getIo()
    {
        return $this->io;
    }

    /**
     * @param SymfonyStyle $io
     */
    public function setIo(SymfonyStyle $io)
    {
        $this->io = $io;
    }
}
