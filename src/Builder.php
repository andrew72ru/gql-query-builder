<?php
/**
 * User: andrew
 * Date: 30.01.2018
 * Time: 18:41
 */

namespace QueryBuilder;

use QueryBuilder\Exceptions\ParserException;

/**
 * Class Builder
 * @package QueryBuilder
 */
class Builder
{
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
     * @var \QueryBuilder\QueryBody[]
     */
    private $body;

    /**
     * Builder constructor.
     */
    public function __construct()
    {
        $this->body = [new QueryBody($this)];
    }

    /**
     * @param string $name
     *
     * @return \QueryBuilder\Builder
     */
    public function setName(string $name): Builder
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param array $params Array of arrays / objects with ['name' => string, 'type' => string, 'required' => bool, 'isArray' => bool] structure
     *
     * @return \QueryBuilder\Builder
     * @throws \QueryBuilder\Exceptions\ParserException
     */
    public function setQueryParams(array $params): Builder
    {
        $validated = [];
        foreach ($params as $param) {
            $validated[] = self::validateQueryParam($param);
        }
        $this->queryParams = $validated;
        return $this;
    }

    /**
     * @param string $param
     * @param string $type
     * @param bool   $required
     * @param bool   $isArray
     *
     * @return \QueryBuilder\Builder
     */
    public function addQueryParam(string $param, string $type, bool $required = false, bool $isArray = false): Builder
    {
        array_push($this->queryParams, (object) [
            'name' => $param,
            'type' => $type,
            'required' => $required,
            'isArray' => $isArray,
        ]);
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
     * @param \QueryBuilder\QueryBody $body
     *
     * @return \QueryBuilder\Builder
     */
    public function setBody(QueryBody $body): Builder
    {
        $this->body = [$body];
        return $this;
    }

    public function addBodyPart(QueryBody $body): Builder
    {
        array_push($this->body, $body);
        return $this;
    }

    /**
     * @return array|\QueryBuilder\QueryBody[]
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return string
     * @throws \QueryBuilder\Exceptions\ParserException
     */
    public function getBodyAsString(): string
    {
        $result = '';
        foreach ($this->body as $item) {
            if (($varName = $item->getVariableName()) !== null) {
                $result .= $varName . ': ';
            }

            $result .= $item->build();
        }

        return $result;
    }

    /**
     * @param $param
     *
     * @return object
     * @throws \QueryBuilder\Exceptions\ParserException
     */
    public static function validateQueryParam($param)
    {
        if (array_keys((array) $param) !== ['name', 'type', 'required', 'isArray']) {
            throw new ParserException("Bad parameter format");
        }

        return (object) $param;
    }
}
