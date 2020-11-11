=================================
Traversing Response Data With dot
=================================

The purpose of this document is to introduce the |ASB|'s
:meth:`AbstractResponseModel::dot` method. The target audience is anyone who
needs to use the |ARM| or extending classes (like |ARMC|).

Introduction
------------

Many times, when working with APIs, the data returned is deep and requires
multiple levels of traversing to get the data you're after. This can lead to
long multidimensional array calls which quickly become cumbersome to work with.

The dot method provides a simple, and familiar, way to reach into deep data and
return exactly what you want.

.. _sample-data:

Sample Data
-----------

The following sample data is referenced in the sections below. It represents a
response from Bamboo's API when querying build plan data.

.. code-block:: javascript

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

Using Dot
---------

Working with deep array structures like the
:ref:`sample data above <sample-data>` is no fun.
:meth:`AbstractResponseModel::dot` allows you to construct JavaScript-like
object paths to obtain the data in a response model.

Here are some examples of using :meth:`AbstractResponseModel::dot` with a
``Plan`` response model we're assuming extends |ARM| and has been loaded with
the above data:

.. code-block:: php
    :linenos:

    //"CORE"
    $plan->dot('projectKey');

    //"CORE"
    $plan->dot('project.key');

    //"https:\/\/bamboo.mywebsite.com\/rest\/api\/latest\/project\/CORE"
    $plan->dot('project.link.href');

    //"https:\/\/bamboo.mywebsite.com\/rest\/api\/latest\/plan\/CORE-CBU"
    $plan->dot('link.href');

As you can see, reaching in and grabbing the data you want from the response's
data is simple!

Default Values
--------------

By default, ``false`` is returned if the given path does not find data. However,
to have another default value returned, set the second parameter to the value
you'd like to be returned if the dot path does not exist:

.. code-block:: php

    $plan->dot('this.does.not.work', 'duh'); //"duh"
