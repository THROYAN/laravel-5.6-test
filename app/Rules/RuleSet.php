<?php

namespace App\Rules;

use App\Functions\Expression;

class RuleSet
{
    /** @var string */
    public $field;
    /** @var array<string, RuleSet | mixed> */
    public $results;
    /** @var \Closure|null */
    public $mapValue;

    public function __construct(
        string $field,
        /** @var array<string, RuleSet | mixed> */
        $results,
        ?callable $mapValue = null
    ) {
        $this->field = $field;
        $this->results = $results;
        $this->mapValue = $mapValue;
    }

    public function getResult(array $fields)
    {
        $value = $fields[$this->field];
        if ($this->mapValue) {
            $value = \call_user_func($this->mapValue, $value, $fields);
        }

        if (!is_array($this->results) && Expression::isValid($this->results)) {
            return Expression::calculate($value, $this->results);
        }

        if (\array_key_exists("".$value, $this->results)) {
            $result = $this->results[$value];

            return $result instanceof self
                ? $result->getResult($fields)
                : $result;
        }

        foreach ($this->results as $expr => $result)
        {
            if (!Expression::isValid($expr)) {
                continue;
            }

            if (Expression::calculate($value, $expr)) {
                return $result instanceof self
                    ? $result->getResult($fields)
                    : $result;
            }
        }

        return null;
    }

    public static function fromArray(array $array)
    {
        // $cell = new self($array['field'], )
        if (!isset($array['field']) && count($array) != 1) {
            throw new \RuntimeException('Can\'t create RuleSet');
        }

        $field = $array['field'] ?? array_keys($array)[0];
        $results = isset($array['field'])
            ? $array['results']
            : $array[$field];
        if (!is_array($results)) {
            $temp = [];
            $temp[$results] = true;
            $temp[Expression::getOpposite($results)] = false;

            $results = $temp;
        }
        $results = array_map(
            function ($r) { return (is_scalar($r) || $r instanceof self) ? $r : self::fromArray($r); },
            $results
        );

        $map = isset($array['field'], $array['map'])
            ? $array['map']
            : null;
        
        return new self($field, $results, $map);
    }
}