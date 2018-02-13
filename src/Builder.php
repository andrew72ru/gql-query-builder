<?php
/**
 * User: andrew
 * Date: 30.01.2018
 * Time: 18:41
 */

namespace andrew72ru\QueryBuilder;

use andrew72ru\QueryBuilder\Exceptions\ParserException;
use andrew72ru\QueryBuilder\Traits\ParseTrait;

/**
 * Class Builder
 * @package QueryBuilder
 */
class Builder
{
    use ParseTrait;

    const TYPE_STRING = 'String';

    const TYPE_DATE_TIME = 'DateTime';

    const REQUIRED_SYMBOL = '!';

    const PARSER_EOL = "\n";

    const PARSER_L_BRACE = '{';

    const PARSER_R_BRACE = '}';

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $queryParams = [];

    /**
     * @var QueryBody[]
     */
    private $body;

    /**
     * @var array
     */
    private $gqlParams = [];

    /**
     * @return array
     */
    public function getGqlParams(): array
    {
        return $this->gqlParams;
    }

    /**
     * @param array $gqlParams
     *
     * @return Builder
     */
    public function setGqlParams(array $gqlParams): Builder
    {
        $this->gqlParams = $gqlParams;

        return $this;
    }

    /**
     * Builder constructor.
     */
    public function __construct()
    {
        $this->body = [new QueryBody($this)];
    }

    public function makeBody():  QueryBody
    {
        return new QueryBody($this);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Builder
     */
    public function setName(string $name): Builder
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $param
     * @param string $type
     * @param bool   $required
     * @param bool   $isArray
     *
     * @return Builder
     * @throws ParserException
     */
    public function addQueryParam(string $param, string $type, bool $required = false, bool $isArray = false): Builder
    {
        $param = new QueryParam([
            'name'     => $param,
            'type'     => $type,
            'required' => $required,
            'isArray'  => $isArray,
        ]);

        array_push($this->queryParams, $param);
        return $this;
    }

    /**
     * @return array
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * @param array $params Array of arrays / objects with ['name' => string, 'type' => string, 'required' => bool,
     *                      'isArray' => bool] structure
     *
     * @return Builder
     * @throws ParserException
     */
    public function setQueryParams(array $params): Builder
    {
        $validated = [];
        foreach ($params as $param) {
            $obj = new QueryParam($param);
            if (!$obj->validate()) {
                throw new ParserException("Bad parameter format");
            }

            $validated[] = $obj;
        }
        $this->queryParams = $validated;
        return $this;
    }

    /**
     * @param string $paramName
     *
     * @return bool
     */
    public function isParamExists(string $paramName): bool
    {
        /** @var QueryParam $queryParam */
        foreach ($this->queryParams as $queryParam) {
            if ($queryParam->getName() === $paramName) {
                return true;
            }
        }

        return false;
    }

    public function addBodyPart(QueryBody $body): Builder
    {
        array_push($this->body, $body);
        return $this;
    }

    /**
     * @return array|QueryBody[]
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param QueryBody $body
     *
     * @return Builder
     */
    public function setBody(QueryBody $body): Builder
    {
        $this->body = [$body];
        return $this;
    }

    /**
     * @return string
     * @throws ParserException
     */
    protected function getBodyAsString(): string
    {
        $result = '';
        foreach ($this->body as $item) {
            if (($varName = $item->getVariableName()) !== null) {
                $result .= $varName . ': ';
            }

            $result .= $item->build() . self::PARSER_EOL;
        }

        return $result;
    }

    /**
     * @return string
     * @throws ParserException
     */
    protected function getParamsAsString(): string
    {
        $result = [];
        foreach ($this->queryParams as $param) {
            $result[] = $this->parseQueryParam($param);
        }

        return implode(', ', $result);
    }

    /**
     * @return string
     * @throws ParserException
     */
    public function build(): string
    {
        $result = 'query ' . $this->getName();
        $params = $this->getParamsAsString();
        if (!empty($params)) {
            $result .= '(' . $params . ')';
        }

        $result .= ' ' . self::PARSER_L_BRACE . self::PARSER_EOL
                   . $this->getBodyAsString()
                   . self::PARSER_R_BRACE;

        return $result;
    }
}
