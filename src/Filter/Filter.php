<?php
namespace Atlas\Table\Filter;

use Atlas\Table\Exception;

/**
 * This is intended as a final fallback safeguard to check SQL data type
 * compliance, not a general purpose validation/sanitizing system. It helps
 * to prevent silent modifications of data by the SQL backend.
 */
class Filter
{
    const INVALID_BOOLEAN = 'INVALID_BOOLEAN';
    const INVALID_DATE = 'INVALID_DATE';
    const INVALID_FSP = 'INVALID_FSP';
    const INVALID_INTEGER = 'INVALID_INTEGER';
    const INVALID_LENGTH = 'INVALID_LENGTH';
    const INVALID_RANGE = 'INVALID_RANGE';
    const INVALID_NOTNULL = 'INVALID_NOTNULL';
    const INVALID_NUMBER = 'INVALID_NUMBER';
    const INVALID_PRECISION = 'INVALID_PRECISION';
    const INVALID_SEPARATOR = 'INVALID_SEPARATOR';
    const INVALID_SCALE = 'INVALID_SCALE';
    const INVALID_TIME = 'INVALID_TIME';

    const INT1 = 255;
    const INT1_RANGE = [-128, 127];

    const INT2 = 65535;
    const INT2_RANGE = [-32768, 32767];

    const INT4 = 4294967295;
    const INT4_RANGE = [-2147483648, 2147483647];

    const INT8 = 18446744073709551615;
    const INT8_RANGE = [-9223372036854775808, 9223372036854775807];

    protected $columns;

    public function __construct(array $columns)
    {
        $this->columns = $columns;
    }

    public function assert(array &$data) : void
    {
        foreach ($data as $name => &$value) {
            $this->validate($value, $this->columns[$name]);
        }
    }

    protected function validate(&$value, array $info) : void
    {
        if ($info['autoinc'] === true && $value === null) {
            return;
        }

        if ($info['notnull'] === true && $value === null) {
            throw Exception::invalidData(static::INVALID_NOTNULL, $info['name']);
        }

        $method = 'validate' . str_replace(' ', '', $info['type']);
        if (! method_exists($this, $method)) {
            return;
        }

        $failure = $this->$method($value, $info);
        if ($failure !== null) {
            throw Exception::invalidData($failure, $info['name']);
        }
    }

    protected function isInteger($value, int $min, int $max) : ?string
    {
        if (! is_numeric($value)) {
            return static::INVALID_NUMBER;
        }

        if ((int) $value != $value) {
            return static::INVALID_INTEGER;
        }

        if ($value < $min || $value > $max) {
            return static::INVALID_RANGE;
        }

        return null;
    }

    protected function isFixedPoint($value, int $precision, int $scale) : ?string
    {
        if (! is_numeric($value)) {
            return static::INVALID_NUMBER;
        }

        if (strlen(str_replace('.', '', $value)) > $precision) {
            return static::INVALID_PRECISION;
        }

        if (strlen(strrchr($value, '.')) > $scale) {
            return static::INVALID_SCALE;
        }
    }

    protected function isFloatingPoint($value) : ?string
    {
        return is_numeric($value) ? null : INVALID_NUMBER;
    }

    protected function isBytelen($value, int $length) : ?string
    {
        return strlen($value) <= $length ? null : static::INVALID_LENGTH;
    }

    protected function isCharlen($value, int $length) : ?string
    {
        return mb_strlen($value) <= $length ? null : static::INVALID_LENGTH;
    }

    protected function isDate($value) : ?string
    {
        $expr = '/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/';
        $good = preg_match($expr, $value, $match)
            && checkdate($match[2], $match[3], $match[1]);

        return ($good) ? null : static::INVALID_DATE;
    }

    protected function isTime($value, int $fsp = 0) : ?string
    {
        $expr = '/^(([0-1][0-9])|(2[0-3])):[0-5][0-9]:[0-5][0-9](\.([0-9]+))?$/';
        $good = preg_match($expr, $value, $match);

        if (isset($match[4]) && strlen($match[4]) > $fsp) {
            return static::INVALID_FSP;
        }

        return ($good) ? null : static::INVALID_TIME;
    }

    protected function isDateTime($value, int $fsp = 0) : ?string
    {
        $date = substr($value, 0, 10);
        $sep = substr($value, 10, 1);
        $time = substr($value, 11);

        if ($sep !== ' ' && $sep !== 'T') {
            return static::INVALID_SEPARATOR;
        }

        return $this->isDate($date)
            ?? $this->isTime($time, $fsp);
    }

    protected function isBoolean($value, array $options)
    {
        return in_array($value, $options, true) ? null : static::INVALID_BOOLEAN;
    }

    protected function validateSmallint($value, array $info) : ?string
    {
        return $this->isInteger($value, ...static::INT2_RANGE);
    }

    protected function validateInt($value, array $info) : ?string
    {
        return $this->isInteger($value, ...static::INT4_RANGE);
    }

    protected function validateInteger($value, array $info) : ?string
    {
        return $this->isInteger($value, ...static::INT4_RANGE);
    }

    protected function validateBigint($value, array $info) : ?string
    {
        return $this->isInteger($value, ...self::INT8_RANGE);
    }

    protected function validateDecimal($value, array $info) : ?string
    {
        return $this->isFixedPoint($value, $info['size'], $info['scale']);
    }

    protected function validateNumeric($value, array $info) : ?string
    {
        return $this->isFixedPoint($value, $info['size'], $info['scale']);
    }

    protected function validateFloat($value, array $info) : ?string
    {
        return $this->isFloatingPoint($value);
    }

    protected function validateDoublePrecision($value, array $info) : ?string
    {
        return $this->isFloatingPoint($value);
    }

    protected function validateReal($value, array $info) : ?string
    {
        return $this->isFloatingPoint($value);
    }

    protected function validateChar($value, array $info) : ?string
    {
        return $this->isBytelen($value, $info['size']);
    }

    protected function validateCharacter($value, array $info) : ?string
    {
        return $this->isBytelen($value, $info['size']);
    }

    protected function validateCharacterVarying($value, array $info) : ?string
    {
        return $this->isBytelen($value, $info['size']);
    }

    protected function validateVarchar($value, array $info) : ?string
    {
        return $this->isBytelen($value, $info['size']);
    }

    protected function validateNchar($value, array $info) : ?string
    {
        return $this->isCharlen($value, $info['size']);
    }

    protected function validateNationalCharacter($value, array $info) : ?string
    {
        return $this->isCharlen($value, $info['size']);
    }

    protected function validateNvarchar($value, array $info) : ?string
    {
        return $this->isCharlen($value, $info['size']);
    }

    protected function validateNationalCharacterVarying($value, array $info) : ?string
    {
        return $this->isCharlen($value, $info['size']);
    }

    protected function validateDate($value, array $info) : ?string
    {
        return $this->isDate($value);
    }

    protected function validateTime($value, array $info) : ?string
    {
        return $this->isTime($value, (int) $info['size']);
    }
}
