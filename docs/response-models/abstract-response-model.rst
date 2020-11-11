=====================
AbstractResponseModel
=====================

The purpose of this document is to explain how the |ARM| class can help build
powerful response models. The target audience is any developer who needs to work
with the |ASB| and its |ARM|.

Be sure to check out the :doc:`/response-models/introduction` document as a
quick introduction. In addition, the :doc:`/quickstart` document describes many
of the setup required within this document.

Introduction
------------

The |ARM| class provided by the |ASB| represents response data. How this data is
obtained is up to how an extending class decides to instantiate it. It's
possible data has already been obtained from a previous API request and the data
is given to this class from another or it's possible there is no data within and
it must be loaded from an API call.

Setup Configuration
===================

Before the extending class can do anything, it must first provide some setup
configuration. This setup configuration is used by the methods within the
|ARM| class to build the necessary API calls to load the data.

To better understand how an extending class is setup, let's create a ``Plan``
to represent a Bamboo Plan:

.. code-block:: php
    :linenos:

    <?php

    use Cob\Bundle\ApiServicesBundle\Models\AbstractResponseModel;

    class Plan extends AbstractResponseModel
    {
        const LOAD_COMMAND   = 'GetPlan';
        const LOAD_ARGUMENTS = [];
    }

As you can see above, this simple class only has two constants defined:
``LOAD_COMMAND`` and ``LOAD_ARGUMENTS``. These two constants provide the
|ARM| all it needs to obtain data from a command (also known
as a Guzzle service description's `"operation"`_) and the arguments which should
be used with the command.

.. _"operation": https://guzzle3.readthedocs.io/webservice-client/guzzle-service-descriptions.html#operations

In order to load our model from the associated load command "GetPlan", we must
first have a "GetPlan" operation in our service description file. The following
is an excerpt from a sample bamboo service description file:

.. code-block:: yaml
    :linenos:

    name: atlassian_services.descriptions.bamboo
    apiVersion: latest
    baseUri: https://bamboo.sentryds.com
    description: API for obtaining information from bamboo
    operations:
      GetPlan:
        httpMethod: GET
        uri: /rest/api/latest/plan/{projectKey}-{buildKey}{?expand}
        summary: >
          Method used to list all plans on Bamboo service that user is allowed to see (READ permission)
          See https://developer.atlassian.com/server/bamboo/bamboo-rest-resources/#plan-service
        responseClass: JSONResponse
        parameters:
          projectKey:
            type: string
            description: project key
            location: uri
            required: true
          buildKey:
            type: string
            description: plan key (might be simply planKey or composite planKey-jobKey)
            location: uri
            required: true
          expand:
            type: string
            description: >
              Used to have bamboo generate additional details. Possible expand parameters:
                actions, stages, branches, variableContext
            location: uri

.. note::

    For information about what an API Service Description file is, see
    :doc:`/index`

Putting Data Into Our Model
---------------------------

Loading From API Service Operation
==================================

Assuming we have a ``ServiceClient`` in our service container at
``atlassian_services.service_clients.bamboo`` and it is setup to use the
operation yaml above, we can quickly get the data from the service like so:

.. code-block:: php
    :linenos:

    <?php

    use \Plan;

    ...

    $client = $this->getContainer()->get('atlassian_services.service_clients.bamboo');
    $data = Plan::getLoaded($this->client)->getData();

.. note::

    Neat! Behind the scenes, the |ARM| class uses the
    ``LOAD_COMMAND`` value alongside the arguments in ``LOAD_ARGUMENTS`` to
    construct a ``GuzzleHttp\Command\Command`` using the
    ``Cob\Bundle\ApiServicesBundle\Models\ServiceClient`` we've setup in our
    services.yml file (see :doc:`/quickstart`).

    By calling the ``getData`` method on the ``Plan`` instance, we can retrieve
    the validated and structured data returned from the API endpoint!

The more astute of you might have wondered how the above call was going to work
if the ``projectKey`` and ``buildKey`` parameters are required by the "GetPlan"
operation. The truth is, the above command *won't* work. There are some models
which do not require load arguments but this is not one of them.

Fortunately, the |ASB| has a comprehensive set of exceptions which
will give us the exact reason why the above doesn't work::

    [Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelException]
    Could not load model \Plan
    Unhandled response model exception!
    Validation errors: [projectKey] is a required string: project key
    [buildKey] is a required string: plan key (might be simply planKey or composite planKey-jobKey)

As we can see above, we've been notified of validation issues. In order to be
more explicit as to what specific response model there was an issue with, the
|ASB| provides additional details about what was happening and with
which model. This makes it easier to determine where the error occurred.

.. note::

    The validation is done by ``GuzzleHttp\Command\Guzzle\SchemaValidator``
    and any errors seen are thrown by
    ``GuzzleHttp\Command\Guzzle\Handler\ValidatedDescriptionHandler``.

Providing Additional Load Arguments
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Some response models do not require specific load arguments to be provided to
obtain data for them. However, in this instance, we need to provide a
``projectKey`` and a ``buildKey`` for our ``Plan`` class to be loaded. This can
be done by providing additional load arguments to the ``getLoaded`` method:

.. code-block:: php
    :linenos:
    :emphasize-lines: 10-13

    <?php

    use \Plan;

    ...

    $client = $this->getContainer()->get('atlassian_services.service_clients.bamboo');
    $plan = Plan::getLoaded(
        $this->getClient(),
        [
            'projectKey' => 'CORE',
            'buildKey'   => 'CBU'
        ]
    );
    $planData = $plan->getData();

Now, ``$planData`` contains an array structure of the data returned from
Bamboo's API at the endpoint defined by the "GetPlan" operation in our service
description file!

.. _sample-response-data:

.. code-block:: javascript
    :linenos:

    {
      "expand": "actions,stages,branches,variableContext",
      "projectKey": "CORE",
      "projectName": "CORE",
      "project": {
        "key": "CORE",
        "name": "CORE",
        "description": "",
        "link": {
          "href": "https:\/\/bamboo.mywebsite.com\/rest\/api\/latest\/project\/CORE",
          "rel": "self"
        }
      },
      "description": "Unit Tests for the CoreBundle",
      "shortName": "CoreBundle",
      "buildName": "CoreBundle",
      "shortKey": "CBU",
      "type": "chain",
      "enabled": true,
      "link": {
        "href": "https:\/\/bamboo.mywebsite.com\/rest\/api\/latest\/plan\/CORE-CBU",
        "rel": "self"
      },

    ...

    }

.. note::

    Our ``Plan`` response model doesn't care about how or where these load
    arguments are used in the underlying request. The service description file
    tells Guzzle all it needs to know about the request associated with the
    operation!

Instantiate A Response Model With Existing Data
===============================================

Sometimes, the data we already have makes sense in other response models. For
example, in the above data returned about our ``Plan``, we see a project key
with values relating to this ``Plan``'s parent project. What if we wanted to
create a link between this ``Plan`` response model and a ``Project`` response
model? This is precisely what the ``AbstractResponseModel::withData`` method is
for!

First, let's create a ``Project`` response model:

.. code-block:: php
    :linenos:

    <?php

    use Cob\Bundle\ApiServicesBundle\Models\AbstractResponseModel;

    class Project extends AbstractResponseModel
    {
    }

.. note::

    You'll notice our ``Project`` class does not have any setup constants
    configured. This makes the ``Project`` class a simple container for data
    with no ability to use factory methods like ``getLoaded`` (doing so would
    throw a ``Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException``
    letting you know you need to provide setup details for the model).

    However, classes used to represent structured data can also extend
    |ARM| and gain access to the underlying helper methods
    like ``dot``! Learn more about ``dot`` in :doc:`/dot`).

    At some point, an operation could be added to the services description file
    which allows for a ``Project`` class to have constants setup for the
    ``Project`` to be loaded from the API endpoint as well!

Let's update our ``Plan`` model to have new ``setProject`` and ``getProject``
methods:


.. code-block:: php
    :linenos:
    :emphasize-lines: 13-21

    <?php

    use Cob\Bundle\ApiServicesBundle\Models\AbstractResponseModel;
    use Project;

    class Plan extends AbstractResponseModel
    {
        const LOAD_COMMAND   = 'GetPlan';
        const LOAD_ARGUMENTS = [];

        private $project;

        public function setProject(Project $project)
        {
            $this->project = $project;
        }

        public function getProject(): Project
        {
            return $this->project;
        }
    }

Now, after loading our plan data, we can establish the link between the plan
and project!

.. code-block:: php
    :linenos:
    :emphasize-lines: 17-23

    <?php

    use \Plan;
    use \Project;

    ...

    $client = $this->getContainer()->get('atlassian_services.service_clients.bamboo');
    $plan = Plan::getLoaded(
        $this->getClient(),
        [
            'projectKey' => 'CORE',
            'buildKey' => 'CBU'
        ]
    );

    //We have the project data so let's instantiate it using the withData method!
    $project = Project::withData(
        $this->getClient(),
        $plan->dot('project') //data specific to the Project.
    );

    $plan->setProject($project);

.. note::

    The `'project'` portion of the data (see
    :ref:`example response above <sample-response-data>`) is what would be
    returned had we made an API call to Bamboo's project endpoints. Here, we
    just pluck it right out of the ``Plan`` data so no additional call needs to
    be made!

Instantiating Child Response Models On Load
===========================================

Associating a ``Project`` with a ``Plan`` like we do above is nice because we
can now obtain our ``Project`` from our ``Plan`` model (all without having to
make an additional API call!). However, doing so manually every time we create
a ``Plan`` would be a bummer.

The |ARM| class makes it easy to establish this link
whenever a ``Plan`` model is loaded! Let's modify our ``Plan`` class to override
the ``getDefaultInitCallback`` method:

.. code-block:: php
    :linenos:
    :emphasize-lines: 25-41

    <?php

    namespace Sentry\Bundle\AtlassianServicesBundle\Models\Bamboo\Response;

    use Sentry\Bundle\ApiServicesBundle\Models\AbstractResponseModel;
    use Sentry\Bundle\AtlassianServicesBundle\Models\Bamboo\Response\Project;

    class Plan extends AbstractResponseModel
    {
        const LOAD_COMMAND   = 'GetPlan';
        const LOAD_ARGUMENTS = [];

        private $project;

        public function setProject(Project $project)
        {
            $this->project = $project;
        }

        public function getProject(): Project
        {
            return $this->project;
        }

        /**
         * @inheritDoc
         */
        public function getDefaultInitCallback()
        {
            return static function (Plan $plan) {
                $project = $plan->dot('project');
                if ($project) {
                    $plan->setParentProject(
                        Project::withData(
                            $plan->getClient(),
                            $project
                        )
                    );
                }
            };
        }

    }

Now, when a ``Plan`` is loaded from an API call or, when it is instantiated with
data, a ``Project`` association is automatically created!

.. note::

    You'll notice we use a method called ``dot``. This method makes it easy to
    traverse response data. See :doc:`/dot` for more information.

Loading A Model With A Promise
==============================

There are instances where you will want to load multiple response models with
data at a time. Guzzle allows for asynchronous operations which means it can
call multiple API endpoints in parallel.

The |ASB| makes it easy to obtain a collection of "load" promises
for response models and then have them run in parallel.

See the dedicated :doc:`/promises` document for details surrounding Promises.

As a sneak peek, a response model could be loaded using a promise like so:

.. code-block:: php
    :linenos:

    $plan = Plan::getUsingPromise(
        $this->getClient(),
        $promise, //set by reference
        null, //the parent model; not set in this case
        [
            'projectKey' => 'CORE',
            'buildKey' => 'CBU'
        ]
    );

    $plan->getData(); //empty

    $promise->wait();

    $plan->getData(); //normal plan data

Public Methods
--------------

.. php:method:: addInitCallback(callable $initCallback)

    Add a callback to be run after data is set in a model (either through
    ``withData`` or after it is loaded from an API endpoint or cache).

    :param callable $initCallback:

.. php:method:: dot(string $key, $default = false, $data = null)

    Helper method which aids in the traversal of data within the response model.

    If a dot path has been resolved before, the value is returned without
    having to traverse the data structure thanks to caching.

    Example:

     .. code-block:: php

         $data = [
            'one' => 1,
            'parent' => [
                'child1' => [
                     'child2' => true
                ]
            ]
         ];

         $this->dot('one'); //1
         $this->dot('parent'); //['child1' => ['child2' => true]]
         $this->dot('parent.child1'); //['child2' => true]
         $this->dot('parent.child1.child2'); //true
         $this->dot('parent.child1.child3'); //false
         this->dot('parent.child1.child3', 'my_default'); //'my_default'

    :param string $key: the key to use as the path to find the data
    :param false|mixed $default: if the key path cannot be found, or if the key
      is empty, return this value
    :param array|ResultInterface|null $data: When null, the data traversed is
      the response model's data. However, if provided, the data is traversed and
      the data at the key path is returned (or default if not found); no caching
      is done. Caching is only done for the original full key path when this
      method is called recursively.
    :returns false|mixed: By default, if the data cannot be found, false is
      returned. Otherwise, if a default value has been provided, the default
      will be returned in that case. If data is found at the key path, the data
      found is returned.
