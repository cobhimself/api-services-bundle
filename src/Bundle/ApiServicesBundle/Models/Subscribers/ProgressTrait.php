<?php
/*
 * This file is part of the cobhimself/api-services-bundle package.
 *
 * (c) Collin D. Brooks <collin.brooks@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cob\Bundle\ApiServicesBundle\Models\Subscribers;

use Cob\Bundle\ApiServicesBundle\Models\CommandLineOutputTrait;

/**
 * Trait which provides properties and methods often used with
 * progress reporting.
 */
trait ProgressTrait
{
    use CommandLineOutputTrait;

    /** @var bool */
    protected $ignoreAdvance;

    /**
     * If the output is at a debug verbosity, output information about an event.
     *
     * @param mixed $message if a string, the message is appended to the calling
     *                       function; if an object, its class is used
     */
    protected function outputEvent($message)
    {
        //No need to generate a backtrace if we're not debugging...
        if ($this->getOutput()->isDebug()) {
            $message = is_object($message) ? get_class($message) : $message;

            $callers = debug_backtrace(null, 2);
            $this->getOutput()->writeln(
                sprintf(
                    '%s:%s',
                    $callers[1]['function'],
                    $message
                )
            );
        }
    }

    /**
     * Set the content for the 'message' and 'context' templates in the
     * progress bar.
     *
     * @param string $message
     * @param mixed  $context value to provide context to the progress
     */
    protected function setInfo(string $message, $context)
    {
        $this->getProgressBar()->setMessage($message);
        $this->setContext($context);
    }

    /**
     * Set the context value within the progress bar.
     *
     * If the given context is a string, it is displayed normally. If it is an
     * object, the class of the object is obtained and the last part of the FQCN
     * will be displayed. If null, the context is cleared.
     *
     * @param mixed|null $context value to provide context to the
     *                                    progress
     */
    protected function setContext($context = null)
    {
        if (null === $context) {
            $context = '';
        } elseif (is_object($context)) {
            $context = get_class($context);
            $context = ($pos = strrpos($context, '\\'))
                ? substr($context, $pos + 1)
                : $context;
        }

        $this->getProgressBar()->setMessage($context, 'context');
    }

    /**
     * Reset the progress bar by hiding any advancement, emptying context, and
     * finishing the progress.
     */
    private function resetProgressBar()
    {
        $this->ignoreAdvance();
        $this->setContext();
        $this->getProgressBar()->finish();
    }

    /**
     * Make it so any attempt to advance a progress bar is ignored.
     */
    private function ignoreAdvance()
    {
        $this->ignoreAdvance = true;
    }

    /**
     * Make it so attempts to advance a progress bar works as usual.
     */
    private function allowAdvance()
    {
        $this->ignoreAdvance = false;
    }

    /**
     * Advance our progress bar if we aren't ignoring advancements.
     *
     * @param int $steps
     */
    private function advance(int $steps = 1)
    {
        if (!$this->ignoreAdvance) {
            $this->getProgressBar()->advance($steps);
        }
    }
}
