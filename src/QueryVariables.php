<?php
/**
 * User: andrew
 * Date: 30.01.2018
 * Time: 19:12
 */

namespace QueryBuilder;


/**
 * Class QueryVariables
 * @package QueryBuilder
 */
class QueryVariables
{
    /**
     * @var \QueryBuilder\Builder
     */
    private $builder;

    /**
     * QueryVariables constructor.
     *
     * @param \QueryBuilder\Builder $builder
     */
    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }
}