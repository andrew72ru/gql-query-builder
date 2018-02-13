<?php
/**
 * User: andrew
 * Date: 13.02.2018
 * Time: 12:15
 */

namespace afsc\QueryBuilder;

use afsc\QueryBuilder\Exceptions\ParserException;

/**
 * Class QueryParam
 * @package afsc\QueryBuilder
 */
class QueryParam
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var bool
     */
    private $required = false;

    /**
     * @var bool
     */
    private $isArray = false;

    /**
     * QueryParam constructor.
     *
     * @param array $params
     * @param bool  $validate
     *
     * @throws \afsc\QueryBuilder\Exceptions\ParserException
     */
    public function __construct($params = [], $validate = false)
    {
        if (!is_array($params)) {
            $params = (array) $params;
        }

        foreach (['name', 'type', 'required', 'isArray'] as $var) {
            if (isset($params[$var])) {
                $this->{$var} = $params[$var];
            }
        }

        if ($validate && !$this->validate()) {
            throw new ParserException("Bad parameter format");
        }
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
     * @return QueryParam
     */
    public function setName(string $name): QueryParam
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return QueryParam
     */
    public function setType(string $type): QueryParam
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param bool $required
     *
     * @return QueryParam
     */
    public function setRequired(bool $required): QueryParam
    {
        $this->required = $required;
        return $this;
    }

    /**
     * @return bool
     */
    public function isArray(): bool
    {
        return $this->isArray;
    }

    /**
     * @param bool $isArray
     *
     * @return QueryParam
     */
    public function setIsArray(bool $isArray): QueryParam
    {
        $this->isArray = $isArray;
        return $this;
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        return (!empty($this->name) && is_string($this->name))
            && !empty($this->type && is_string($this->type));
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'isArray' => $this->isArray,
            'required' => $this->required,
        ];
    }
}
