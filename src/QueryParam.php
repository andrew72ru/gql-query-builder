<?php
/**
 * User: andrew
 * Date: 13.02.2018
 * Time: 12:15.
 */

namespace andrew72ru\QueryBuilder;

use andrew72ru\QueryBuilder\Exceptions\ParserException;

/**
 * Separate class for build query parameter for builder.
 */
class QueryParam
{
    /**
     * @var string Parameter name
     */
    private $name;

    /**
     * @var string parameter type
     */
    private $type;

    /**
     * @var bool is parameter required. If true, parser will adds the required mark (in regular, «!») to this parameter in result query
     */
    private $required = false;

    /**
     * @var bool is parameter an array. If true, parser will adds the «array» attribute («[]») to this parameter in reqult query
     */
    private $isArray = false;

    /**
     * QueryParam constructor.
     *
     * @param array $params
     * @param bool  $validate
     *
     * @throws ParserException
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
            throw new ParserException('Bad parameter format');
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
    public function setName(string $name): self
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
    public function setType(string $type): self
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
    public function setRequired(bool $required): self
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
    public function setIsArray(bool $isArray): self
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
