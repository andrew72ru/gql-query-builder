<?php
/**
 * User: andrew
 * Date: 30.01.2018
 * Time: 18:41.
 */

namespace andrew72ru\QueryBuilder;

use andrew72ru\QueryBuilder\Exceptions\ParserException;
use andrew72ru\QueryBuilder\Traits\ParseTrait;

/**
 * The main class for a build GraphQL queries.
 *
 * Simple usage:
 *
 * For example, wee need to get this query
 *
 * ```
 * query TradingSchema($pools: [String!], $symbols: [String!], $yesterday: DateTime) {
 *  today: quotes(pools: $pools, symbols: $symbols) {
 *      symbol {
 *          symbol
 *          expirationDate
 *      }
 *      time
 *      ask
 *      bid
 *  }
 *  yesterday: quotes(pools: $pools, symbols: $symbols, time: $yesterday) {
 *    symbol {
 *          symbol
 *          expirationDate
 *      }
 *      time
 *      ask
 *    }
 *  }
 *
 * The code above builds this query:
 *
 * ```php
 * $builder = new Builder();
 * $builder->setName('TradingSchema');
 * $builder->addQueryParam('pools', Builder::TYPE_STRING, true, true)
 *      ->addQueryParam('symbols', Builder::TYPE_STRING, true, true)
 *      ->addQueryParam('yesterday', Builder::TYPE_DATE_TIME, false, false);
 *
 * $bodyToday = new QueryBody($builder);
 * $bodyToday->setName('quotes')
 *      ->setVariableName('today')
 *      ->addBodyPart(['symbol' => ['symbol', 'expirationDate']])
 *      ->addBodyPart('time')
 *      ->addBodyPart('ask')
 *      ->addBodyPart('bid');
 *
 * $bodyToday->addNameParam('pools', 'pools')
 *      ->addNameParam('symbols', 'symbols');
 *
 * $bodyYesterday = new QueryBody($builder);
 * $bodyYesterday->setName('quotes')
 *      ->setVariableName('yesterday');
 *
 * $body = [
 *      'symbol' => [
 *          'symbol',
 *          'expirationDate',
 *      ],
 *      'time',
 *      'ask'
 *  ];
 * $bodyYesterday->setBody($body);
 * $bodyYesterday->setNameParams($params);
 *
 * $builder->setBody($bodyToday)->addBodyPart($bodyYesterday);
 *
 * return $builder->build();
 *
 * ```
 */
class Builder
{
    use ParseTrait;

    /**
     * Type in query variable.
     */
    const TYPE_STRING = 'String';

    /**
     * Type in query variable.
     */
    const TYPE_DATE_TIME = 'DateTime';

    /**
     * Symbol determinate a required query variable.
     */
    const REQUIRED_SYMBOL = '!';

    /**
     * End of line symbol for build query.
     */
    const PARSER_EOL = "\n";

    /**
     * Left brace.
     */
    const PARSER_L_BRACE = '{';

    /**
     * Right brace.
     */
    const PARSER_R_BRACE = '}';

    /**
     * @var string Name for query
     */
    private $name;

    /**
     * @var array Array of QueryParam objects for build query
     */
    private $queryParams = [];

    /**
     * @var QueryBody[] Object for query body
     */
    private $body;

    /**
     * @var array Array for GraphQL variables
     */
    private $gqlVariables = [];

    /**
     * @return array
     */
    public function getGqlVariables(): array
    {
        return $this->gqlVariables;
    }

    /**
     * @param array $gqlVariables
     *
     * @return Builder
     */
    public function setGqlVariables(array $gqlVariables): self
    {
        $this->gqlVariables = $gqlVariables;

        return $this;
    }

    /**
     * Builder constructor.
     */
    public function __construct()
    {
        $this->body = [new QueryBody($this)];
    }

    public function makeBody(): QueryBody
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
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Adds a single query parameter.
     *
     * @param string $param
     * @param string $type
     * @param bool   $required
     * @param bool   $isArray
     *
     * @return Builder
     *
     * @throws ParserException
     */
    public function addQueryParam(string $param, string $type, bool $required = false, bool $isArray = false): self
    {
        $param = new QueryParam([
            'name' => $param,
            'type' => $type,
            'required' => $required,
            'isArray' => $isArray,
        ], true);

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
     * Sets the array of query parameters. All existing parameters will be overwrite!
     *
     * @param array $params Array of arrays / objects with ['name' => string, 'type' => string, 'required' => bool,
     *                      'isArray' => bool] structure
     *
     * @return Builder
     *
     * @throws ParserException
     */
    public function setQueryParams(array $params): self
    {
        $validated = [];
        foreach ($params as $param) {
            $obj = new QueryParam($param);
            if (!$obj->validate()) {
                throw new ParserException('Bad parameter format');
            }

            $validated[] = $obj;
        }
        $this->queryParams = $validated;

        return $this;
    }

    /**
     * Whether the parameter exists.
     *
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

    /**
     * Adds the part to query body.
     *
     * @param QueryBody $body
     *
     * @return Builder
     */
    public function addBodyPart(QueryBody $body): self
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
     * Sets the complete query body.
     *
     * @param QueryBody $body
     *
     * @return Builder
     */
    public function setBody(QueryBody $body): self
    {
        $this->body = [$body];

        return $this;
    }

    /**
     * Returns the query body as parsed string.
     *
     * @return string
     *
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
     * Returns all query params as string.
     *
     * @return string
     *
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
     * Main function to complete parse all query parts.
     *
     * @return string
     *
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

    /**
     * Standard toString implementation.
     *
     * @return string
     *
     * @throws ParserException
     */
    public function __toString()
    {
        return $this->build();
    }
}
