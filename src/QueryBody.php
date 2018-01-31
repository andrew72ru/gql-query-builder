<?php
/**
 * User: andrew
 * Date: 30.01.2018
 * Time: 18:55
 */

namespace QueryBuilder;

use QueryBuilder\Traits\ParseTrait;

/**
 * Class QueryBody
 * @package QueryBuilder
 */
class QueryBody
{
    use ParseTrait;

    /**
     * @var array
     */
    private $body = [];

    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $variableName = null;

    /**
     * @var array
     */
    private $nameParams = [];

    /**
     * @var \QueryBuilder\Builder
     */
    private $builder;

    /**
     * QueryBody constructor.
     *
     * @param \QueryBuilder\Builder $builder
     */
    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @param array $body
     *
     * @return \QueryBuilder\QueryBody
     */
    public function setBody(array $body): QueryBody
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @param string|array $part
     *
     * @return \QueryBuilder\QueryBody
     */
    public function addBodyPart($part): QueryBody
    {
        array_push($this->body, $part);
        return $this;
    }

    /**
     * @param string $name
     *
     * @return \QueryBuilder\QueryBody
     */
    public function setName(string $name): QueryBody
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param mixed $variableName
     * @return \QueryBuilder\QueryBody
     */
    public function setVariableName($variableName): QueryBody
    {
        $this->variableName = $variableName;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getVariableName()
    {
        return $this->variableName;
    }

    /**
     * @param array $nameParams
     *
     * @return \QueryBuilder\QueryBody
     * @throws \QueryBuilder\Exceptions\ParserException
     */
    public function setNameParams(array $nameParams): QueryBody
    {
        $validated = [];
        foreach ($nameParams as $name_param) {
            $validated[] = Builder::validateQueryParam($name_param);
        }
        $this->nameParams = $validated;
        return $this;
    }

    /**
     * @param string $param
     * @param string $type
     * @param bool   $required
     * @param bool   $isArray
     *
     * @return \QueryBuilder\QueryBody
     */
    public function addNameParam(string $param, string $type, bool $required = false, bool $isArray = false): QueryBody
    {
        array_push($this->nameParams, (object) [
            'name' => $param,
            'type' => $type,
            'required' => $required,
            'isArray' => $isArray,
        ]);

        return $this;
    }

    /**
     * @return string
     * @throws \QueryBuilder\Exceptions\ParserException
     */
    public function build(): string
    {
        $result = $this->name;
        $params = null;
        foreach ($this->nameParams as $name_param) {
            $params .= $this->parseParam($name_param);
        }
        if ($params !== null) {
            $result .= '(' . $params . ')' . Builder::PARSER_EOL;
        }

        $result .= $this->parseBody($this->body);

        return $result;
    }
}
