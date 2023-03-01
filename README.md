# ApiServicesBundle

[![Build Status](https://app.travis-ci.com/cobhimself/api-services-bundle.svg?branch=1.0)](https://app.travis-ci.com/cobhimself/api-services-bundle)
[![Coverage Status](https://coveralls.io/repos/github/cobhimself/api-services-bundle/badge.svg?branch=1.0)](https://coveralls.io/github/cobhimself/api-services-bundle?branch=1.0)

# Generate Documentation

## Required Dependencies
 - [Pipenv](https://pipenv.pypa.io/en/latest/)

## Generate Documentation
```
cd docs
pipenv install
pipenv run make html
```

## Working With Documentation

If additional dependencies are needed for documentation generation, the `requirements.txt` file will need to be kept up
to date with changes to the `Pipfile`. This can be done in the following way:

```
cd docs
pipenv requirements > requirements.txt
```
