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

use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelException;
use Cob\Bundle\ApiServicesBundle\Exceptions\UnknownCommandException;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Command\Command;
use GuzzleHttp\Command\CommandInterface;
use GuzzleHttp\Command\Guzzle\Description;
use GuzzleHttp\Command\Guzzle\DescriptionInterface;
use GuzzleHttp\Command\Guzzle\GuzzleClient;
use GuzzleHttp\HandlerStack;
use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Yaml\Yaml;

class ServiceClient extends GuzzleClient implements ServiceClientInterface
{
    use HasOutputTrait;

    const DESCRIPTION_FILE_KEY = 'description_file';

    private $serializer;

    public $dispatcher;

    /**
     * ServiceClient constructor.
     *
     * @throws ResponseModelException
     */
    public function __construct(
        ClientInterface $client,
        DescriptionInterface $description,
        callable $commandToRequestTransformer = null,
        callable $responseToResultTransformer = null,
        HandlerStack $commandHandlerStack = null,
        array $config = []
    ) {
        //Take a quick moment to validate our description so we can catch common
        //issues before they happen.
        $this->validateDescription($description);

        $this->serializer = $commandToRequestTransformer ?? new Serializer($description, []);

        parent::__construct(
            $client,
            $description,
            $commandToRequestTransformer,
            $responseToResultTransformer,
            $commandHandlerStack,
            $config
        );
    }

    /**
     * Factory which handles configuration setup for the inner Guzzle client
     * based on a service description file.
     *
     * @throws ResponseModelException
     */
    public static function factory(array $config = []): ServiceClient
    {
        if (!array_key_exists(self::DESCRIPTION_FILE_KEY, $config)) {
            throw new InvalidArgumentException(sprintf('%s::factory requires the %s config option!', self::class, self::DESCRIPTION_FILE_KEY ));
        }

        $config = static::initConfig($config);
        $serviceConfig = static::getServiceConfig(
            $config[self::DESCRIPTION_FILE_KEY]
        );

        if (isset($config['baseUri'])) {
            $serviceConfig['baseUri'] = $config['baseUri'];
        }

        $description = new Description($serviceConfig);

        $client = new Client($config);

        // Response validation is OFF by default. Request validation is always ON
        $validateResponse = isset($config['validate_response']) && $config['validate_response'];

        return new static(
            $client,
            $description,
            new Serializer($description, []),
            new Deserializer($description, true, [], $validateResponse),
            null,
            $serviceConfig
        );
    }

    /**
     * Initialize some good defaults for our configuration.
     */
    public static function initConfig(array $config): array
    {
        $config += [
            'validate_response' => true
        ];

        return $config;
    }

    /**
     * Return the service description from the given service description file.
     *
     * @param string $descriptionFile
     *
     * @return array the configuration to be used with the service client
     */
    public static function getServiceConfig(string $descriptionFile): array
    {
        $locator = new FileLocator($descriptionFile);
        $description = $locator->locate($descriptionFile);

        $contents = file_get_contents($description);

        return Yaml::parse($contents);
    }

    /**
     * Our service client can
     *
     * @param EventDispatcherInterface $dispatcher
     */
    public function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @var CacheProvider
     */
    private $cacheProvider;

    /**
     * @inheritDoc
     */
    public function getCache(): CacheProviderInterface
    {
        return $this->cacheProvider;
    }

    public function canCache(): bool{
        return !is_null($this->cacheProvider);
    }

    /**
     * @inheritDoc
     */
    public function setCacheProvider(
        CacheProviderInterface $cacheProvider
    ): ServiceClientInterface {
        $this->cacheProvider = $cacheProvider;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getChunkedCommands(
        string $commandName,
        array $commandArguments,
        int $size,
        callable $buildArgsFunc,
        int $maxResults = 25
    ): array {
        //Make sure we don't make an unnecessary request if our chunks line up
        //perfectly with our size.
        if ($size % $maxResults === 0) {
            ++$maxResults;
        }

        $commands = [];

        for ($i = 0; $i <= $size; $i += $maxResults) {
            $commands[] = new Command(
                $commandName,
                $buildArgsFunc($commandArguments, $i, $maxResults)
            );
        }

        return $commands;
    }

    /**
     * @inheritDoc
     *
     * @throws UnknownCommandException if the given command cannot be found
     */
    public function getCommand($name, array $args = []): CommandInterface
    {
        try {
            $command = parent::getCommand($name, $args);
        } catch (InvalidArgumentException $e) {
            //We can't find the given command. Make our exception message
            //a bit more helpful.
            throw new UnknownCommandException($name, $this->getDescription());
        }

        return $command;
    }

    /**
     * Perform basic validation of our description.
     *
     * Additional validation can be added to find even more common errors.
     *
     * @throws ResponseModelException if there is an issue with the description
     */
    private function validateDescription(DescriptionInterface $description)
    {
        $operations = $description->getOperations();

        foreach (array_keys($operations) as $name) {
            try {
                $description->getOperation($name);
            } catch (InvalidArgumentException $e) {
                throw new ResponseModelException(
                    sprintf(
                        'Operation %s needs a responseModel set!',
                        $name
                    )
                );
            }
        }
    }

    /**
     * Get the client's event dispatcher.
     *
     * If a dispatcher has not been set already, a default Symfony event
     * dispatcher is used.
     */
    public function getDispatcher(): EventDispatcherInterface
    {
        if (null === $this->dispatcher) {
            $this->dispatcher = new EventDispatcher();
        }

        return $this->dispatcher;
    }

    /**
     * Dispatch an event.
     *
     * @param string $eventClass the event's FQCN
     * @param mixed  ...$args    arguments to send to the event constructor
     */
    public function dispatchEvent(string $eventClass, ...$args): Event
    {
        $name  = $eventClass::NAME;
        $event = new $eventClass(...$args);

        return $this->getDispatcher()->dispatch($name, $event);
    }

    /**
     * Get the {@link RequestInterface} generated by the given {@link CommandInterface}.
     *
     * @param CommandInterface $command the command used to generate the request
     *
     * @return RequestInterface the request generated by the command
     */
    public function getRequestFromCommand(CommandInterface $command): RequestInterface
    {
        return call_user_func($this->serializer, $command);
    }
}
