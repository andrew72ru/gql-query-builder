<?php
/**
 * User: andrew
 * Date: 31.01.2018
 * Time: 8:40
 */

namespace andrew72ru\QueryBuilder\Exceptions;

use Throwable;

/**
 * Class ParserException
 * @package andrew72ru\QueryBuilder\Exceptions
 */
class ParserException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        if (empty($message)) {
            $message = "Parser exception";
        }

        if ($code === 0) {
            $code = 500;
        }
        parent::__construct($message, $code, $previous);
    }
}
