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

interface CommandLineStringHelpersInterface
{
    /**
     * Indent the given message and prefix it with a prefix character.
     *
     * The message is wrapped to be contained within a terminal.
     *
     * @param string   $msg    the message to output
     * @param int      $indent how many spaces to indent our message on
     *                         each line
     * @param string   $prefix the string prefix used at the beginning of the
     *                         line; defaults to blank
     * @param int|null $width  the width, in characters, to fill before wrapping
     *
     * @return string
     */
    public function withIndent(
        string $msg,
        int $indent,
        $prefix = ' > ',
        int $width = null
    ): string;

    /**
     * Wrap the given message string to fit a set width.
     *
     * @param string   $msg    the message to output
     * @param int      $indent how many spaces to indent our message on
     *                         each line
     * @param string   $prefix the string prefix used at the beginning of the
     *                         line; defaults to blank
     * @param int|null $width  the width, in characters, to fill before wrapping
     * @param bool     $shrink whether or not the message should be condensed by
     *                         removing excessive white space and line breaks
     *
     * @return string
     */
    public function wordwrap(
        string $msg,
        int $indent = 0,
        string $prefix = '',
        int $width = null,
        bool $shrink = false
    ): string;

    /**
     * Replace all line breaks in the given message with an empty space and then
     * wrap to fit a set width.
     *
     * @param string   $msg    the message to output
     * @param int      $indent how many spaces to indent our message on
     *                         each line
     * @param string   $prefix the string prefix used at the beginning of the
     *                         line; defaults to blank
     * @param int|null $width  the width, in characters, to fill before wrapping
     *
     * @return string
     */
    public function shrinkwrap(
        string $msg,
        int $indent = 0,
        string $prefix = '',
        int $width = null
    ): string;

    /**
     * Return the given structure ready to be output.
     *
     * @param array $structure
     * @param int   $indent
     * @param int   $currentDepth used during recursive calls
     *
     * @return array|string
     */
    public function outputStructure(
        array $structure,
        $indent = 2,
        $currentDepth = 0
    );

    /**
     * Get the width of the terminal in chars.
     *
     * @return mixed
     */
    public static function getTerminalWidth();
}
