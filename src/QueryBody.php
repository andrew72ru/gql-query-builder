<?php
/**
 * User: andrew
 * Date: 30.01.2018
 * Time: 18:55
 */

namespace QueryBuilder;

use QueryBuilder\Exceptions\ParserException;
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
     * @return QueryBody
     */
    public function setBody(array $body): QueryBody
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @param string|array $part
     *
     * @return QueryBody
     */
    public function addBodyPart($part): QueryBody
    {
        array_push($this->body, $part);
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return QueryBody
     */
    public function setName(string $name): QueryBody
    {
        $this->name = $name;
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
     * @param mixed $variableName
     *
     * @return QueryBody
     */
    public function setVariableName($variableName): QueryBody
    {
        $this->variableName = $variableName;
        return $this;
    }

    /**
     * @param string $param
     * @param string $paramLink
     *
     * @return QueryBody
     * @throws \QueryBuilder\Exceptions\ParserException
     */
    public function addNameParam(string $param, string $paramLink): QueryBody
    {
        $object = $this->validateBodyParam([
            'name' => $param,
            'type' => $paramLink,
        ]);
        array_push($this->nameParams, $object);

        return $this;
    }

    /**
     * @return array
     */
    public function getNameParams()
    {
        return $this->nameParams;
    }

    /**
     * @param array $nameParams
     *
     * @return QueryBody
     * @throws ParserException
     */
    public function setNameParams(array $nameParams): QueryBody
    {
        $validated = [];
        foreach ($nameParams as $name_param) {
            $validated[] = $this->validateBodyParam($name_param);
        }
        $this->nameParams = $validated;
        return $this;
    }

    /**
     * @param $param
     *
     * @return object
     * @throws ParserException
     */
    public function validateBodyParam($param)
    {
        if (array_keys((array) $param) !== ['name', 'type']) {
            throw new ParserException('Wrong body param');
        }

        if (is_array($param)) {
            $param = (object) $param;
        }

        if (!$this->builder->isParamExists($param->type)) {
            throw new ParserException("Unable to find {$param->type} in Builder. Add this param to Builder before adding it to body query");
        }

        return (object) $param;
    }

    /**
     * @return string
     * @throws ParserException
     */
    public function build(): string
    {
        $result = $this->name;
        $params = null;
        foreach ($this->nameParams as $name_param) {
            $params .= $this->parseQueryParam($name_param);
        }
        if ($params !== null) {
            $result .= '(' . $params . ')' . Builder::PARSER_EOL;
        }

        $result .= $this->parseBody($this->body);

        return $result;
    }
}
