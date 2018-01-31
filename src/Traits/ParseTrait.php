<?php
/**
 * User: andrew
 * Date: 30.01.2018
 * Time: 19:19
 */

namespace QueryBuilder\Traits;

use QueryBuilder\Builder;
use QueryBuilder\Exceptions\ParserException;

trait ParseTrait
{
    /**
     * @param $param
     *
     * @return string
     * @throws \QueryBuilder\Exceptions\ParserException
     */
    public function parseQueryParam($param): string
    {
        $param = Builder::validateQueryParam($param);

        $result = '$' . $param->name . ': ';
        $type = $param->type;
        if ($param->required) {
            $type = $type . Builder::REQUIRED_SYMBOL;
        }

        if ($param->isArray) {
            $type = '[' . $type . ']';
        }

        return $result . $type;
    }

    /**
     * @param $param
     *
     * @return string
     * @throws \QueryBuilder\Exceptions\ParserException
     */
    public function parseBodyParam($param): string
    {
        if (array_keys((array) $param) !== ['name', 'type']) {
            throw new ParserException("Wrong body param structure");
        }

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
                $bodyString .= $key . Builder::PARSER_L_BRACE . Builder::PARSER_EOL;
                $this->processBody($element, $bodyString);
                $bodyString .= Builder::PARSER_R_BRACE . Builder::PARSER_EOL;
            }
        }
    }
}
