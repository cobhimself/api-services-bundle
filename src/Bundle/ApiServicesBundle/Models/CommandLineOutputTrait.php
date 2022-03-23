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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

trait CommandLineOutputTrait
{
    use CommandLineStringHelpersTrait;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /** @var ProgressBar */
    private $progressBar;

    private static $terminalWidth;

    /**
     * @return OutputInterface|null
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param OutputInterface $output
     *
     * @return $this
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * @return ProgressBar|null
     */
    public function getProgressBar()
    {
        return $this->progressBar;
    }

    /**
     * @param ProgressBar $progressBar
     *
     * @return $this
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public function setProgressBar(ProgressBar $progressBar)
    {
        $this->progressBar = $progressBar;

        return $this;
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
     *
     * @return $this
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public function setIo(SymfonyStyle $io)
    {
        $this->io = $io;

        return $this;
    }

    /**
     * Quickly output to the console if we're in debug mode.
     */
    public function writeDebug(string $msg, int $indent = 0)
    {
        if ($this->output->isDebug()) {
            $this->output->writeln($this->withIndent($msg, $indent));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function writeWithIndent(
        string $msg,
        int $indent,
        int $width = null
    ) {
        $this->getOutput()->writeln($this->withIndent($msg, $indent, '', $width));
    }

    /**
     * {@inheritDoc}
     *
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public function inheritOutputFrom($model)
    {
        if (
            method_exists($model, 'getProgressBar')
            && null !== $model->getProgressBar()
        ) {
            $this->setProgressBar($model->getProgressBar());
        }

        if (
            method_exists($model, 'getIo')
            && null !== $model->getIo()
        ) {
            $this->setIo($model->getIo());
        }

        if (
            method_exists($model, 'getOutput')
            && null !== $model->getOutput()
        ) {
            $this->setOutput($model->getOutput());
        }

        return $this;
    }
}
