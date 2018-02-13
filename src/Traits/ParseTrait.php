<?php
/**
 * User: andrew
 * Date: 30.01.2018
 * Time: 19:19
 */

namespace afsc\QueryBuilder\Traits;

use afsc\QueryBuilder\Builder;
use afsc\QueryBuilder\QueryParam;

/**
 * Trait for parse common objects / arrays to GQL strings
 *
 * @package afsc\QueryBuilder\Traits
 */
trait ParseTrait
{
    /**
     * Parse parameter for query
     *
     * @param \afsc\QueryBuilder\QueryParam $param
     *
     * @return string
     * @throws \afsc\QueryBuilder\Exceptions\ParserException
     */
    public function parseQueryParam(QueryParam $param): string
    {
        $result = '$' . $param->getName() . ': ';
        $type = $param->getType();
        if ($param->isRequired()) {
            $type = $type . Builder::REQUIRED_SYMBOL;
        }

        if ($param->isArray()) {
            $type = '[' . $type . ']';
        }

        return $result . $type;
    }

    /**
     * @param $param
     *
     * @return string
     */
    public function parseBodyParam($param): string
    {
        return $param->name . ': $' . $param->type;
    }

    public function parseBody(array $body): string
    {
        $bodyString = Builder::PARSER_L_BRACE . Builder::PARSER_EOL;
        $this->processBody($body, $bodyString);
        $bodyString .= Builder::PARSER_R_BRACE . Builder::PARSER_EOL;

        return $bodyString;
    }

    /**
     * @param $elements
     * @param $bodyString
     */
    protected function processBody(array $elements, &$bodyString): void
    {
        foreach ($elements as $key => $element) {
            if (is_int($key)) {
                $bodyString .= $element . Builder::PARSER_EOL;
            } elseif (is_string($key) && is_array($element)) {
                $bodyString .= $key . ' ' . Builder::PARSER_L_BRACE . Builder::PARSER_EOL;
                $this->processBody($element, $bodyString);
                $bodyString .= Builder::PARSER_R_BRACE . Builder::PARSER_EOL;
            }
        }
    }
}
