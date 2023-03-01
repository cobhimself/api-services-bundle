===============================
|ASB| documentation
===============================

Working with API services can be time-consuming, error-prone, and repetitive.
The |ASB| is a Symphony Bundle which aids in the creation and use of commands
associated with API endpoints. While simple API calls can be constructed using
tools as ubiquitous as `curl`, or through the construction of
:class:`GuzzleHttp` client calls (or other API frameworks), these calls can be
made more `DRY`_ and reusable by using `API Service Descriptions`_.

In addition, many API data structures resemble "Items" and "Collections of
Items". The |ASB| provides boilerplate to help quickly organize data
returned from an API call and use it logically; this greatly improves the
usability--and re-usability--of API data. See :doc:`ApiServicesBundle Quick
Start <quickstart>` to get started.

What Is An API Service Description?
-----------------------------------

The |ASB| is built on top of `guzzle/command`_ and `guzzle/services`_. These
two libraries extend `guzzle/guzzle`_ which is the REST API framework used to
make API calls. `Guzzle's service description`_ files include `"operations"`_
which pair a named `"command"`_ with parameters (if any) to perform an API call.

According to `Guzzle's documentation`_:

 | Service descriptions define web service APIs by documenting each operation,
 | the operation's parameters, validation options for each parameter, an
 | operation's response, how the response is parsed, and any errors that can be
 | raised for an operation. Writing a service description for a web service
 | allows you to more quickly consume a web service than writing concrete
 | commands for each web service operation.

As you can see, one of the main benefits of an API service description is the
ability to document and abstract out different API calls and the parameters
available. The |ASB| makes it easy to use `Guzzle's service description SCHEMA`_
which is inspired by the `OpenAPI Specification`_ (hopefully Guzzle will move to
the OpenAPI Specification at some point). Specific documentation on a
description file's structure is `provided by Guzzle`_.

How Do I Use These Descriptions?
--------------------------------

There is plenty of documentation online showing how to use Guzzle's service
description files and commands so understanding the specifics is left up to the
reader. However, the |ASB| has its own |ServiceClient| which extends the
GuzzleClient class provided by guzzle/command. The |ServiceClient| makes it easy
to build API service clients with an underlying service description. This is
covered in the :doc:`ApiServicesBundle Quick Start <quickstart>` documentation.

.. _Guzzle's service description:
.. _API Service Descriptions: https://guzzle3.readthedocs.io/webservice-client/guzzle-service-descriptions.html
.. _DRY: https://en.wikipedia.org/wiki/Don%27t_repeat_yourself
.. _guzzle/command: https://github.com/guzzle/command
.. _guzzle/services: https://github.com/guzzle/guzzle-services
.. _guzzle/guzzle: https://github.com/guzzle/guzzle
.. _"operations": https://guzzle3.readthedocs.io/webservice-client/guzzle-service-descriptions.html#operations
.. _"command": https://github.com/guzzle/command#commands
.. _Guzzle's documentation: https://guzzle3.readthedocs.io/webservice-client/guzzle-service-descriptions.html
.. _Guzzle's service description SCHEMA: https://guzzle3.readthedocs.io/_downloads/guzzle-schema-1.0.json
.. _OpenAPI Specification: https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md
.. _provided by Guzzle: https://guzzle3.readthedocs.io/webservice-client/guzzle-service-descriptions.html

.. toctree::
    :maxdepth: 2
    :caption: Learn More:

    quickstart
    response-models/index
    cache-provider
    event-system
    dot
    promises
