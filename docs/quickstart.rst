==========
Quickstart
==========

The purpose of this document is to provide a general overview of the |ASB|.
The target audience is developers who need to understand how to get started with
the |ASB|.

Overview
--------

The |ASB| provides boilerplate functionality for service command calls and the
organization of response data into individual response models or collections of
response model data. It also makes it easy to load Guzzle
`service descriptions`_ from YAML files, cache response data and has an event
system which allows extensibility.

.. _service descriptions: https://guzzle3.readthedocs.io/webservice-client/guzzle-service-descriptions.html

The ServiceClient
-----------------

The APIServicesBundle has a |ServiceClient| class which extends
``\GuzzleHttp\Command\Guzzle\GuzzleClient`` and provides additional
functionality. The following sections provide information on how to instantiate
the |ServiceClient| as well as configuration options available.

Instantiate A New ServiceClient
===============================

The |ServiceClient| can be instantiated using the :meth:`ServiceClient::factory`
method. This method makes it easy to create the client from the service
description file in addition to other configuration settings. To obtain a
|ServiceClient| and have a service description file loaded for it:

.. code-block:: php
    :linenos:
    :caption: Create a new |ServiceClient|

    use Cob\Bundle\ApiServicesBundle\Models\ServiceClient;

    $serviceClient = ServiceClient::factory([
        'description_file' => '<path to description YAML file>'
    ]);

.. warning::

    When instantiating the |ServiceClient|, you MUST provide a valid file path
    to a description YAML file using the ``description_file`` option!

.. note::

    Guzzle provides a way for service descriptions to be created using its
    ``Description`` class but service descriptions can also be constructed using
    data structures like JSON or YAML. The |ASB| loads service descriptions from
    YAML documents. See `Guzzle's service description documentation`_ for more
    information on service description structure. They provide a `sample JSON
    services document`_ which is helpful as well.

    Additional options can be provided within the configuration array sent to
    the ``factory`` method. See the
    `\GuzzleHttp\Command\Guzzle\GuzzleClient`_ and `\GuzzleHttp\Client`_
    documentation and code for details.

.. _Guzzle's service description     documentation: https://guzzle3.readthedocs.io/webservice-client/guzzle-service-descriptions.html
.. _sample JSON services document: https://guzzle3.readthedocs.io/webservice-client/guzzle-service-descriptions.html#example-service-description
.. _\GuzzleHttp\Command\Guzzle\GuzzleClient: https://github.com/guzzle/command
.. _\GuzzleHttp\Client: http://docs.guzzlephp.org/en/6.5/request-options.html

Using The Service Container
===========================

To make a |ServiceClient| instance available from the Symfony service container:

.. code-block:: yaml
    :linenos:
    :caption: Use the Symfony service container

    services:
      my_bundle.namespace.whatever.service_client:
        class: Cob\Bundle\ApiServicesBundle\Models\ServiceClient
        factory: 'Cob\Bundle\ApiServicesBundle\Models\ServiceClient::factory'
        arguments:
          config:
            description_file: '%kernel.root_dir%/../path/to/description.yml'

.. note::

    Continue on to see even more settings you can specify in a ``services.yml``
    file for obtaining the |ServiceClient|.

Now, whenever you want to get the client:

.. code-block:: php

    $client = $this->getContainer()->get('my_bundle.namespace.whatever.service_client');

Response Models
---------------

The APIServicesBundle provides two abstract classes, |ARM|
(:doc:`docs <response-models/abstract-response-model>`) and |ARMC|
(:doc:`docs <response-models/abstract-response-model-collection>`) which can be
extended to build powerful applications. These models are associated with a
service `"operation"`_ and default arguments for the operation. For detailed
information about these classes, see
:doc:`Response Models documentation <response-models/index>`.

.. _"operation": https://guzzle3.readthedocs.io/webservice-client/guzzle-service-descriptions.html#operations

Caching API Responses
---------------------

A |ServiceClient| can cache responses its received if a cache provider has
been set. A cache provider must implement :class:`CacheProviderInterface`.
You can set the cache provider manually by using
:meth:`ServiceClient::setCacheProvider` or, if you would like to specify the
cache provider within the service container:

.. code-block:: yaml
    :linenos:
    :caption: Specify cache provider in services config

    services:
      my_bundle.namespace.whatever.cache_provider:
        class: Cob\Bundle\ApiServicesBundle\Models\CacheProvider
        arguments: ['/tmp/myCacheDirectory', '.myCacheExtension.php']
      my_bundle.namespace.whatever.service_client:
        class: Cob\Bundle\ApiServicesBundle\Models\ServiceClient
        factory: 'Cob\Bundle\ApiServicesBundle\Models\ServiceClient::factory'
        arguments:
          config:
            description_file: '%kernel.root_dir%/../path/to/description.yml'
        calls:
          - ['setCacheProvider', ['@my_bundle.namespace.whatever.cache_provider']]

See :doc:`CacheProvider documentation <cache-provider>` for details.

Dispatching Events For Extensibility
------------------------------------

The loading and organizing of response model data can be altered or extended
using the |ASB|'s extensive event system. A dispatcher implementing
``Symfony\Component\EventDispatcher\EventDispatcherInterface`` has to be set
using :class:`ServiceClient::setDispatcher` in order for events to be triggered.

.. code-block:: php
    :linenos:
    :caption: Set a dispatcher

    use Cob\Bundle\ApiServicesBundle\Models\ServiceClient;
    use Symfony\Component\EventDispatcher\EventDispatcher;

    $serviceClient = ServiceClient::factory([
        'description_file' => '<path to description YAML file>'
    ]);

    $serviceClient->setDispatcher(new EventDispatcher());

The |ASB| has a service definition of ``api_services.dispatcher``, which is a
``Symfony\Component\EventDispatcher\EventDispatcher`` instance. It can be used
in your ``services.yml`` file as well. Building on the previous ``service.yml``
example:

.. code-block:: yaml
    :linenos:
    :caption: Specify event dispatcher provider in services config

    services:
      my_bundle.namespace.whatever.cache_provider:
        class: Cob\Bundle\ApiServicesBundle\Models\CacheProvider
        arguments: ['/tmp/myCacheDirectory', '.myCacheExtension.php']
      my_bundle.namespace.whatever.service_client:
        class: Cob\Bundle\ApiServicesBundle\Models\ServiceClient
        factory: 'Cob\Bundle\ApiServicesBundle\Models\ServiceClient::factory'
        arguments:
          config:
            description_file: '%kernel.root_dir%/../path/to/description.yml'
        calls:
          - ['setCacheProvider', ['@my_bundle.namespace.whatever.cache_provider']]
          - ['setDispatcher', ['@api_services.dispatcher']]

For additional details, see
:doc:`Event System documentation <event-system>`.

Learn More
----------

 * :doc:`Response Models <response-models/index>`
 * :doc:`Event System <event-system>`
 * :doc:`CacheProvider <cache-provider>`
 * :doc:`Traversing response data with dot <dot>`