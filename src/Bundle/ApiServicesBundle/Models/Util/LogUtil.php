<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Util;

use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfig;
use Cob\Bundle\ApiServicesBundle\Models\ServiceClientInterface;
use GuzzleHttp\Command\Command;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;

class LogUtil {

    /**
     * @var int
     */
    private static $terminalWidth;

    /**
     * Output a given message if the output's verbosity is set to debug.
     *
     * @param OutputInterface $output  the output interface performing the output
     * @param string          $message the message to output
     * @param int             $indent  the number of spaces to indent the message; default is 0
     */
    public static function debug(OutputInterface $output, string $message, int $indent = 0)
    {
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
            $output->writeln(self::withIndent($message, $indent), OutputInterface::VERBOSITY_DEBUG);
        }
    }

    /**
     * Outputs the message returned by the given callable if the verbosity level of the given output is debug.
     *
     * This method will not generate the message returned by the callable unless the verbosity level is at least debug;
     * this is helpful when you know the generation of the message may take some time and you don't want to generate it
     * unless you really have to.
     *
     * @param OutputInterface $output     the output interface performing the output
     * @param callable        $getMessage the callable used to obtain the message to log
     * @param int             $indent     the number of spaces to indent the message; default is 0
     */
    public static function lazyDebug(OutputInterface $output, callable $getMessage, int $indent = 0)
    {
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
            self::debug($output, $getMessage(), $indent);
        }
    }

    /**
     * Get the width of the terminal in chars (if available).
     *
     * Defaults to 110 characters if it cannot be determined.
     *
     * @return mixed
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

    /**
     * Return the given structure ready to be output.
     *
     * @param array $structure
     * @param int   $indent
     * @param int   $currentDepth used during recursive calls
     *
     * @return array|string
     */
    public static function outputStructure(
        array $structure,
        int   $indent = 2,
        int   $currentDepth = 0
    ): string {
        $output = '';
        array_walk_recursive(
            $structure,
            function ($value, $key) use ($indent, $currentDepth, &$output) {
                $currentIndent = $currentDepth * $indent;
                if (is_array($value)) {
                    if (empty($value)) {
                        $output .= self::withIndent(
                                $key . ': []', $currentIndent
                            ) . PHP_EOL;
                    } else {
                        $output .= self::withIndent(
                                $key . ': ', $currentIndent
                            ) . PHP_EOL;
                        $output .= self::outputStructure(
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

                    $output .= self::withIndent(
                            $key . ': ' . $value, $currentIndent
                        ) . PHP_EOL;
                }
            }
        );

        return $output;
    }

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
    public static function withIndent(
        string $msg,
        int    $indent,
        string $prefix = ' > ',
        int    $width = null
    ): string {
        return self::wordwrap($msg, $indent, $prefix, $width);
    }

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
    public static function wordwrap(
        string $msg,
        int    $indent = 0,
        string $prefix = '',
        int    $width = null,
        bool   $shrink = false
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
    public static function shrinkwrap(
        string $msg,
        int    $indent = 0,
        string $prefix = '',
        int    $width = null
    ): string {
        return self::wordwrap($msg, $indent, $prefix, $width, true);
    }

    /**
     * Output the request URI the client will use for the given command in the debug log.
     *
     * @param ServiceClientInterface $client the service client who will generate the request and whose output we
     *                                       will use
     * @param Command                $command the command to log the request URI of
     */
    public static function debugLogCommandRequest(
        ServiceClientInterface $client,
        Command $command
    ) {
        self::debug($client->getOutput(), $client->getRequestFromCommand($command)->getUri());
    }

    /**
     * Output the response for the give response model in the debug log.
     *
     * @param OutputInterface $output        the output interface being used to output
     * @param string          $responseModel the response model FQCN
     * @param mixed           $response      the response to format and output
     */
    public static function logResponse(
        OutputInterface $output,
        string $responseModel,
        $response
    ) {
        //Depending on the size of the response, the process of formatting the data could add up so we'll lazily debug
        self::lazyDebug(
            $output,
            function () use ($responseModel, $response) {
                return sprintf(
                    'Response obtained for %s:' . PHP_EOL . '%s' . PHP_EOL,
                    $responseModel,
                    print_r($response, true)
                );
            }
        );
    }
}
