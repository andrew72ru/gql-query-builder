<?php


use QueryBuilder\Builder;
use QueryBuilder\Exceptions\ParserException;
use QueryBuilder\QueryBody;

class QueryBuilderCest
{
    public function _before(UnitTester $I)
    {
    }

    public function _after(UnitTester $I)
    {
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
     * @throws \QueryBuilder\Exceptions\ParserException
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
        $I->assertEquals([$secondParam], $builder->getQueryParams());
    }

    public function createAndValidateBody(UnitTester $I)
    {
        $builder = new Builder();
        $body = new QueryBody($builder);
        $I->assertInstanceOf(QueryBody::class, $body->setName("queryTest"));
        $I->assertEquals("queryTest", $body->getName());
    }
}
