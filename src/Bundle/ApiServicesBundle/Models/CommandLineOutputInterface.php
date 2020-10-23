<?php
/*
 * This file is part of the cobhimself/api-services-bundle package.
 *
 * (c) Collin D. Brooks <collin.brooks@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/** @noinspection ReturnTypeCanBeDeclaredInspection */

namespace Cob\Bundle\ApiServicesBundle\Models;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

interface CommandLineOutputInterface
{
    /**
     * @return OutputInterface
     */
    public function getOutput(): OutputInterface;

    /**
     * @param OutputInterface $output
     *
     * @return $this
     */
    public function setOutput(OutputInterface $output);

    /**
     * @return ProgressBar
     */
    public function getProgressBar(): ProgressBar;

    /**
     * @param ProgressBar $progressBar
     *
     * @return $this
     */
    public function setProgressBar(ProgressBar $progressBar);

    /**
     * @return SymfonyStyle
     */
    public function getIo(): SymfonyStyle;

    /**
     * @param SymfonyStyle $io
     *
     * @return $this
     */
    public function setIo(SymfonyStyle $io);

    /**
     * Quickly output to the console if we're in debug mode.
     */
    public function writeDebug(string $msg, int $indent = 0);

    /**
     * Pass on the output classes from the given model to this model.
     *
     * @param object $model an object which uses this trait
     *
     * @return $this
     */
    public function inheritOutputFrom($model);

    /**
     * Write the given message, indent it, and fit it inside the width.
     *
     * @param string   $msg    the message to output
     * @param int      $indent how many spaces to indent our message on
     *                         each line
     * @param int|null $width  the width, in characters, to fill before wrapping
     *
     * @return string
     */
    public function writeWithIndent(
        string $msg,
        int $indent,
        int $width = null
    );
}
