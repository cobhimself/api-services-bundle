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

use Symfony\Component\Console\Application;

trait CommandLineStringHelpersTrait
{
    /**
     * {@inheritDoc}
     */
    public function withIndent(
        string $msg,
        int    $indent,
        string $prefix = ' > ',
        int    $width = null
    ): string {
        return $this->wordwrap($msg, $indent, $prefix, $width);
    }

    /**
     * {@inheritDoc}
     */
    public function wordwrap(
        string $msg,
        int $indent = 0,
        string $prefix = '',
        int $width = null,
        bool $shrink = false
    ): string {
        if ($shrink) {
            $msg = str_replace(PHP_EOL, ' ', trim($msg));
            $msg = preg_replace('/\s+/', ' ', $msg);
        }

        $width = $width ?? static::getTerminalWidth();

        //Our width will need to be modified based on how much we are indenting.
        $leadingSpaces = str_repeat(' ', $indent);
        $spacePrefix = str_repeat(' ', strlen($leadingSpaces) + strlen($prefix));

        if ($width > static::getTerminalWidth()) {
            $width = static::getTerminalWidth();
        }

        $msg = wordwrap(
            $msg,
            $width - strlen($leadingSpaces . $prefix),
            PHP_EOL
        );

        $lines = explode(PHP_EOL, $msg);
        foreach ($lines as $i => $line) {
            if ($i === 0) {
                $lines[$i] = $leadingSpaces . $prefix . $line;
            } else {
                $lines[$i] = $spacePrefix . $line;
            }
        }

        return implode(PHP_EOL, $lines);
    }

    /**
     * {@inheritDoc}
     */
    public function shrinkwrap(
        string $msg,
        int $indent = 0,
        string $prefix = '',
        int $width = null
    ): string {
        return $this->wordwrap($msg, $indent, $prefix, $width, true);
    }

    /**
     * @inheritDoc
     */
    public function outputStructure(
        array $structure,
        int   $indent = 2,
        int $currentDepth = 0
    ): string {
        $output = '';
        array_walk_recursive(
            $structure,
            function ($value, $key) use ($indent, $currentDepth, &$output) {
                $currentIndent = $currentDepth * $indent;
                if (is_array($value)) {
                    if (empty($value)) {
                        $output .= $this->withIndent(
                            $key . ': []', $currentIndent
                            ) . PHP_EOL;
                    } else {
                        $output .= $this->withIndent(
                            $key . ': ', $currentIndent
                            ) . PHP_EOL;
                        $output .= $this->outputStructure(
                            $value,
                            $indent,
                            $currentDepth + 1
                        ) . PHP_EOL;
                    }
                } else {
                    if (null === $value) {
                        $value = 'null';
                    } elseif (is_bool($value)) {
                        $value = ($value) ? 'true' : 'false';
                    }

                    $output .= $this->withIndent(
                        $key . ': ' . $value, $currentIndent
                        ) . PHP_EOL;
                }
            }
        );

        return $output;
    }

    /**
     * {@inheritDoc}
     */
    public static function getTerminalWidth()
    {
        if (self::$terminalWidth === null) {
            $dimensions = (new Application())->getTerminalDimensions();
            //Give ourselves some space
            self::$terminalWidth = $dimensions[0] ? $dimensions[0] - 5 : 110;
        }

        return self::$terminalWidth;
    }
}
