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

use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException;
use Cob\Bundle\ApiServicesBundle\Exceptions\ValidationException;
use Cob\Bundle\ApiServicesBundle\Models\Http\ClassResultInterface;
use GuzzleHttp\Command\CommandInterface;
use GuzzleHttp\Command\Guzzle\DescriptionInterface;
use GuzzleHttp\Command\Guzzle\Deserializer as DefaultDeserializer;
use GuzzleHttp\Command\Guzzle\Parameter;
use GuzzleHttp\Command\Guzzle\SchemaValidator;
use GuzzleHttp\Command\Result;
use GuzzleHttp\Command\ResultInterface;
use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Deserializer extends DefaultDeserializer
{
    /** @var SchemaValidator */
    private $validator;

    /** @var CommandInterface */
    private $command;

    /**
     * @var RequestInterface|null
     */
    private $request;

    public function __construct(
        DescriptionInterface $description,
        $process,
        array $responseLocations = [],
        bool $validateResponse = false
    ) {
        if ($validateResponse) {
            $this->validator = new SchemaValidator();
        }

        parent::__construct($description, $process, $responseLocations);
    }

    /**
     * Return the response model class based on the description's model data.
     *
     * @param Parameter               $model The model parameter from our description
     * @param array|ResponseInterface $data  The data to provide the model
     *
     * @return ResponseModelInterface|array
     *
     * @throws ResponseModelSetupException
     */
    private function toClass(Parameter $model, $data)
    {
        //The data should either be an array or a response model.
        if (!is_array($data) && !($data instanceof ResponseInterface)) {
            throw new InvalidArgumentException('The data parameter must either be an array or implement ResponseInterface.');
        }

        $modelData = $model->toArray();
        $class     = $modelData['class'] ?? null;

        //Do you even class bro?
        if (null === $class && $model->getType() === 'class') {
            throw new ResponseModelSetupException(sprintf(
                'Model type is "class" but "class" parameter isn\'t defined for model %s',
                $model->getName()
            ));
        }

        //Make sure our class is sane.
        if ($class && !is_subclass_of($class, ClassResultInterface::class)) {
            throw new ResponseModelSetupException(sprintf(
                'Result class should implement %s. Unable to deserialize response into %s',
                ClassResultInterface::class, $class
            ));
        }

        //If our data is an array, toss it directly to the constructor.
        if (is_array($data)) {
            $result = ($class) ? new $class($data) : $data;
        } else {
            //Looks like we'll need to generate the class from the response.
            $result = $class::fromResponse($data, $this->request);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(
        ResponseInterface $response,
        RequestInterface $request,
        CommandInterface $command
    ) {
        $this->command = $command;
        $this->request = $request;

        return parent::__invoke($response, $request, $command);
    }

    /**
     * @return Result|ResultInterface|mixed|ResponseInterface|ResponseModelInterface|void
     *
     * @throws ResponseModelSetupException
     */
    protected function visit(Parameter $model, ResponseInterface $response)
    {
        switch ($model->getType()) {
            case 'class':
                $result = $this->toClass($model, $response);
                break;
            case 'response':
                $result = $response;
                break;
            default:
                $result = parent::visit($model, $response);
                $res = $result->toArray();

                //If we use the normal 'object' type, we can do validation
                if (
                    $this->validator instanceof SchemaValidator
                    && $result instanceof ResultInterface
                    && $this->validator->validate($model, $res) === false
                ) {
                    throw new ValidationException(
                        sprintf(
                            'Response failed model validation: %s',
                            implode("\n", $this->validator->getErrors())
                        ),
                        $this->command
                    );
                }

                $result = $this->toClass($model, $res);
        }

        return $result;
    }
}
