============
Introduction
============

Guzzle makes it easy to call an API endpoint and work with the returned data.
However, what is missing is the ability to create structured data relationships
which make the data easier to understand and work with. The |ASB| provides two
abstract classes, |ARM| and |ARMC| which provide powerful methods for working
with an API response's returned data.

The basic idea is, data which represents a single "item" would be represented
by a class which extends the |ARM| and data containing a "collection" of "items"
would be represented by a class extending |ARMC|.

Organizing Response Models
--------------------------

The two abstract response models provided by the |ASB| makes it easier to work
with non-relational data, like a simple request's single data value, as well as
with collection/item relationships like the ones shown below.

To understand how these classes are used, let's assume we are working with API
information from `Atlassian's Bamboo Software`_ and we want to obtain
information about a Bamboo Project and all the Plans within it.

.. _Atlassian's Bamboo Software: https://www.atlassian.com/software/bamboo

Array of Plans
==============

Bamboo's Plans have data associated with them such as a description, short name,
a build name, short key, etc. A ``Plan`` class could be created which extends
the |ARM| class to represent a single plan and its data could be queried as
usual.

Assuming we want to compile a selection of ``Plan`` objects, an array of
``Plan`` classes could be created. However, this would quickly become difficult
to organize and work with.

.. mermaid:: /_uml/response-models-array-of-plans.mermaid

Array of Projects
=================

Similarly, Bamboo has "Projects" which contain a group of "Plans". If we had an
array of ``Project`` classes (which extend the |ARM| class) and we kept an array
of the plans within it, our array structure would quickly become difficult to
traverse.

.. mermaid:: /_uml/response-models-array-of-projects.mermaid

PlanCollection class
====================

A better way to organize our data is to utilize a "collection" class to contain
the individual items belonging to it. We can then structure our data in an
way that is easier to comprehend and work with.

Here, we have a Bamboo Project (which would be a ``Project`` class which extends
|ARM|) and a collection of ``Plan`` classes (inside a ``PlanCollection`` class
extending |ARMC|).

.. mermaid:: /_uml/response-models-plancollection-class.mermaid

Multiple Collections
====================

It is also possible to query multiple Projects from Bamboo's API. This means we
can have a "collection" of Projects and each Project would have a "collection"
of Plan Items.

.. mermaid:: /_uml/response-models-multiple-collections.mermaid

In addition, Plans can have multiple Plan Branches. In this case, a Plan "item"
can have a Plan Branch "collection" which contains Plan Branch "items". As you
can see below, without a "collection" class, the structure of this data can
quickly become complicated.

.. mermaid:: /_uml/response-models-out-of-hand.mermaid

Summary
-------

As you can see, the use of "collection" classes which extend the |ARMC| can help
organize groups of model classes extending the |ARM| class. See below to learn
more about each of these abstract classes.
