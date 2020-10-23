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

use Symfony\Component\Console\Input\InputInterface;

trait CommandLineInputTrait
{
    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @return InputInterface
     */
    public function getInput(): InputInterface
    {
        return $this->input;
    }

    /**
     * @param InputInterface $input
     *
     * @return $this
     */
    public function setInput(InputInterface $input): self
    {
        $this->input = $input;

        return $this;
    }
}
