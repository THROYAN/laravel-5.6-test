<?php

namespace App\Functions;

class Expression
{
    const REGEXP = "/^([<>]=?)(\d+)$/";

    public static function getOpposite($expression)
    {
        if (!\preg_match(self::REGEXP, $expression, $matches)) {
            return $expression;
        }

        $operation = $matches[1];
        $result = '';
        switch (substr($operation, 0, 1)) {
            case '<':
                $result .= '>';
                break;
            case '>':
                $result .= '<';
                break;
        }

        if (!\ends_with($operation, '=')) {
            $result .= '=';
        }

        $result .= $matches[2];

        return $result;
    }

    public static function calculate($value, $expression): bool
    {
        if (!\preg_match(self::REGEXP, $expression, $matches)) {
            return false;
        }

        $operation = $matches[1];
        $comparant = $matches[2];
        $result = false;
        switch (\substr($operation, 0, 1)) {
            case '<':
                $result |= $value < $comparant;
                break;
            case '>':
                $result |= $value > $comparant;
                break;
        }

        if (\ends_with($operation, '=')) {
            $result |= $value == $comparant;
        }

        return $result;
    }

    public static function isValid($expression): bool
    {
        return \preg_match(self::REGEXP, $expression);
    }
}