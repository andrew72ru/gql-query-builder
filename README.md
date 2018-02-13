Small library for build [GraphQL](http://graphql.org) queries in php
====================================================================

[![Build Status](https://travis-ci.org/andrew72ru/gql-query-builder.svg?branch=master)](https://travis-ci.org/andrew72ru/gql-query-builder)
[![Coverage Satatus](https://codecov.io/gh/andrew72ru/gql-query-builder/branch/master/graph/badge.svg)](https://codecov.io/gh/andrew72ru/gql-query-builder/branch/master)

**Attention!**  
The library **does not include** any http-clients implementations and intended **only** for build valid queries for some GraphQL servers. The correctness of requests depends of specific GraphQL server implementation. 

Installation
------------

```
composer require andrew72ru/gql-query-builder
```

Usage
-----

Example query with parameters and variables:

```
query MyAwesomeScheme($pools: [String!], $objects: [String!], $yesterday: DateTime) {
        today: values(pools: $pools, objects: $objects) {
            object {
                object
                expirationDate
            }
            time
            myParam
            myOtherParam
        }
        yesterday: values(pools: $pools, objects: $objects, time: $yesterday) {
            object {
                object
                expirationDate
            }
            myThirdParam
        }
    }
```

The code above returns this query as string

```
public function createMyQuery()
{
    $builder = new Builder();

    $builder->setName('MyAwesomeScheme');
    $builder->addQueryParam('pools', Builder::TYPE_STRING, true, true)
        ->addQueryParam('objects', Builder::TYPE_STRING, true, true)
        ->addQueryParam('yesterday', Builder::TYPE_DATE_TIME, false, false);

    $bodyToday = new QueryBody($builder);
    $bodyToday->setName('values')
        ->setVariableName('today')
        ->addBodyPart(['object' => ['object', 'expirationDate']])
        ->addBodyPart('time')
        ->addBodyPart('myParam')
        ->addBodyPart('myOtherParam');

    $bodyToday->addNameParam('pools', 'pools')
        ->addNameParam('bjects', 'bjects');

    $bodyYesterday = new QueryBody($builder);
    $bodyYesterday->setName('values')
        ->setVariableName('yesterday');

    $body = [
        'object' => [
            'object',
            'expirationDate',
        ],
        'myThirdParam',
    ];
    $bodyYesterday->setBody($body);

    $params = [
        [
            'name' => 'pools',
            'type' => 'pools',
        ],
        [
            'name' => 'values',
            'type' => 'values',
        ],
        [
            'name' => 'time',
            'type' => 'yesterday',
        ]
    ];

    $bodyYesterday->setNameParams($params);

    $builder->setBody($bodyToday)->addBodyPart($bodyYesterday);

    return $builder->build();
    
    // Or You may use __toString() implementation 
    // return (string) $builder;
}
```

Simply query without parameters and variables

```
query MyAwesomeScheme { 
    values { 
        object { 
            expirationDate 
        } 
        time 
    } 
}
```

Code:

```
public function createSimpleQuery()
{
    $builder = new Builder();
    $body = new QueryBody($builder);
    $body->setName('quotes')
        ->setBody([
            'symbol' => ['expirationDate'],
            'time',
        ]);
    $builder->setBody($body)
        ->setName('TradingSchema');
        
    return (string) $builder;
}
```

Contributing
------------

You are welcome! :)

Testing
-------

Tests are implemented with [Codeception](https://codeception.com) framework.

- Clone this repository;
- install requirements (`composer install --dev`);
- run tests: `vendor/bin/codecept run --coverage-html`
