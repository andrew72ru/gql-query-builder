<?php


use afsc\QueryBuilder\Builder;
use afsc\QueryBuilder\Exceptions\ParserException;
use afsc\QueryBuilder\QueryBody;
use afsc\QueryBuilder\QueryParam;

class QueryBuilderCest
{
    private $expect = 'query TradingSchema($pools: [String!], $symbols: [String!], $yesterday: DateTime) {
        today: quotes(pools: $pools, symbols: $symbols) {
            symbol {
                symbol
                expirationDate
            }
            time
            ask
            bid
        }
        yesterday: quotes(pools: $pools, symbols: $symbols, time: $yesterday) {
            symbol {
                symbol
                expirationDate
            }
            time
            ask
        }
    }';

    /**
     * @throws ParserException
     * @return Builder
     */
    private function createTestedClass()
    {
        $builder = new Builder();

        $builder->setName('TradingSchema');
        $builder->addQueryParam('pools', Builder::TYPE_STRING, true, true)
            ->addQueryParam('symbols', Builder::TYPE_STRING, true, true)
            ->addQueryParam('yesterday', Builder::TYPE_DATE_TIME, false, false);

        $bodyToday = new QueryBody($builder);
        $bodyToday->setName('quotes')
            ->setVariableName('today')
            ->addBodyPart(['symbol' => ['symbol', 'expirationDate']])
            ->addBodyPart('time')
            ->addBodyPart('ask')
            ->addBodyPart('bid');

        $bodyToday->addNameParam('pools', 'pools')
            ->addNameParam('symbols', 'symbols');

        $bodyYesterday = new QueryBody($builder);
        $bodyYesterday->setName('quotes')
            ->setVariableName('yesterday');

        $body = [
            'symbol' => [
                'symbol',
                'expirationDate',
            ],
            'time',
            'ask'
        ];
        $bodyYesterday->setBody($body);

        $params = [
            [
                'name' => 'pools',
                'type' => 'pools',
            ],
            [
                'name' => 'symbols',
                'type' => 'symbols',
            ],
            [
                'name' => 'time',
                'type' => 'yesterday',
            ]
        ];

        $bodyYesterday->setNameParams($params);

        $builder->setBody($bodyToday)->addBodyPart($bodyYesterday);

        return $builder;
    }

    private function normalizeString(string $string): string
    {
        return preg_replace('/(\s{2,})/', ' ', preg_replace('/(\n)|(\s{2,})/', ' ', $string));
    }

    public function createBuilderClasses(UnitTester $I)
    {
        $builder = new Builder();
        $I->assertInstanceOf(Builder::class, $builder);
        $I->assertInstanceOf(QueryBody::class, $builder->getBody()[0]);
    }

    public function exceptionWithoutMessage(UnitTester $I)
    {
        $I->expectException(new ParserException('Parser exception'), function () {
            throw new ParserException();
        });
    }

    /**
     * @param \UnitTester $I
     *
     * @throws ParserException
     */
    public function setAndAddParameters(UnitTester $I)
    {
        $builder = new Builder();
        $I->assertInstanceOf(Builder::class, $builder->setName("TestBuilder"));
        $I->assertEquals("TestBuilder", $builder->getName());
        $I->assertInstanceOf(Builder::class, $builder->addQueryParam("testParam", Builder::TYPE_STRING, true, true));

        $I->expectException(new ParserException('Bad parameter format'), function () use ($builder) {
            $builder->setQueryParams(['a' => 1, 'b' => 'c', 'd' => 3]);
        });

        $secondParam = (object) [
            'name' => 'testParam1',
            'type' => Builder::TYPE_DATE_TIME,
            'required' => true,
            'isArray' => false,
        ];

        $I->assertInstanceOf(Builder::class, $builder->setQueryParams([$secondParam]));
        $I->assertEquals([new QueryParam($secondParam)], $builder->getQueryParams());
    }

    /**
     * @param \UnitTester $I
     *
     * @throws \afsc\QueryBuilder\Exceptions\ParserException
     */
    public function validateQueryParams(UnitTester $I)
    {
        $param = new QueryParam();
        $I->assertInstanceOf(QueryParam::class, $param->setName('newName'));
        $I->assertInstanceOf(QueryParam::class, $param->setType('string'));
        $I->assertInstanceOf(QueryParam::class, $param->setRequired(true));
        $I->assertInstanceOf(QueryParam::class, $param->setIsArray(false));

        $I->assertEquals($param->toArray(), [
            'name' => 'newName',
            'type' => 'string',
            'required' => true,
            'isArray' => false,
        ]);
    }

    /**
     * @param \UnitTester $I
     *
     * @throws \afsc\QueryBuilder\Exceptions\ParserException
     */
    public function validateVariousQueryParams(UnitTester $I)
    {
        $one = [
            'name' => 'testParam1',
            'type' => Builder::TYPE_STRING,
            'required' => true,
            'isArray' => false,
        ];

        $I->assertInstanceOf(QueryParam::class, new QueryParam($one, true));

        $two = (object) [
            'type' => Builder::TYPE_DATE_TIME,
            'required' => true,
            'isArray' => true,
            'name' => 'timeParam1',
        ];

        $I->assertInstanceOf(QueryParam::class, new QueryParam($two, true));

        $three = (object) [
            'not' => 'valid',
            'object' => 'here',
        ];

        $I->expectException(\Exception::class, function () use ($three) {
            new QueryParam($three, true);
        });
    }

    /**
     * @param \UnitTester $I
     *
     * @throws ParserException
     */
    public function createAndValidateBody(UnitTester $I)
    {
        $builder = new Builder();
        $body = new QueryBody($builder);
        $I->assertInstanceOf(QueryBody::class, $body->setName("queryTest"));
        $I->assertEquals("queryTest", $body->getName());

        $I->expectException(ParserException::class, function () use ($body) {
            $body->addNameParam('pools', 'pools');
        });

        $I->expectException(ParserException::class, function () use ($body) {
            $body->setNameParams(['a' => 'b', 'c' => 'd']);
        });

        $builder->addQueryParam('pools', Builder::TYPE_STRING, true, true);
        $I->assertInstanceOf(QueryBody::class, $body->addNameParam('pools', 'pools'));

        $bodyParam = [
            'name' => 'pools',
            'type' => 'pools',
        ];

        $I->assertInstanceOf(QueryBody::class, $body->setNameParams([$bodyParam]));
        $I->assertEquals([(object) $bodyParam], $body->getNameParams());

        $I->assertInstanceOf(QueryBody::class, $body->setVariableName("today"));
        $I->assertEquals($body->getVariableName(), "today");

        $I->assertInstanceOf(QueryBody::class, $builder->makeBody());
    }

    /**
     * @param \UnitTester $I
     *
     * @throws ParserException
     */
    public function testExpectedData(UnitTester $I)
    {
        $fullBuilder = $this->createTestedClass();
        $I->assertTrue(is_array($fullBuilder->getBody()));
        foreach ($fullBuilder->getBody() as $body) {
            $I->assertInstanceOf(QueryBody::class, $body);
        }

        $I->assertEquals($this->normalizeString($this->expect), $this->normalizeString($fullBuilder->build()));
    }

    /**
     * @param \UnitTester $I
     *
     * @throws ParserException
     */
    public function testSimpleData(UnitTester $I)
    {
        $expect = 'query TradingSchema { quotes { symbol { expirationDate } time } }';
        $builder = new Builder();
        $body = new QueryBody($builder);
        $body->setName('quotes')
            ->setBody([
                'symbol' => ['expirationDate'],
                'time',
            ]);
        $builder->setBody($body)
            ->setName('TradingSchema');

        $I->assertEquals($this->normalizeString($expect), $this->normalizeString($builder->build()));
    }

    /**
     * @param \UnitTester $I
     *
     * @throws \afsc\QueryBuilder\Exceptions\ParserException
     */
    public function checkGraphQLParams(UnitTester $I)
    {
        $params = ['pools' => ['mt5_rc'], 'symbols' => ['V20#5_VEB_2020_USD']];
        $builder = new Builder();
        $I->assertInstanceOf(Builder::class, $builder->setGqlParams($params));
        $I->assertEquals($params, $builder->getGqlParams());
    }
}
