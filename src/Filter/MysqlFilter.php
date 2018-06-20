<?php
namespace Atlas\Table\Filter;

/**
 * @todo check that boolean defs are reported as boolean, not tinyint.
 */
class MysqlFilter extends Filter
{
    const INVALID_YEAR = 'INVALID_YEAR';
    const INVALID_UNIXTIME = 'INVALID_UNIXTIME';

    const INT3 = 16777215;
    const INT3_RANGE = [-8388608, 8388607];


    protected function validateBoolean($value, array $info) : ?string
    {
        return $this->isBoolean($value, $options, [0, 1, '0', '1', true, false]);
    }

    protected function validateBool($value, array $info) : ?string
    {
        return $this->validateBoolean($value, $info);
    }

    protected function validateTinyint($value, array $info) : ?string
    {
        return $this->isInteger($value, ...static::INT1_RANGE);
    }

    protected function validateTinyintUnsigned($value, array $info) : ?string
    {
        return $this->isInteger($value, 0, static::INT1);
    }

    protected function validateSmallintUnsigned($value, array $info) : ?string
    {
        return $this->isInteger($value, 0, static::INT2);
    }

    protected function validateMediumint($value, array $info) : ?string
    {
        return $this->isInteger($value, ...static::INT3_RANGE);
    }

    protected function validateMediumintUnsigned($value, array $info) : ?string
    {
        return $this->isInteger($value, 0, static::INT3_RANGE);
    }

    protected function validateIntUnsigned($value, array $info) : ?string
    {
        return $this->isInteger($value, 0, static::INT4);
    }

    protected function validateIntegerUnsigned($value, array $info) : ?string
    {
        return $this->validateIntUnsigned($value, $info);
    }

    protected function validateBigintUnsigned($value, array $info) : ?string
    {
        return $this->isInteger($value, 0, static::INT8);
    }

    protected function validateDec($value, array $info) : ?string
    {
        return $this->validateDecimal($value, $info);
    }

    protected function validateFixed($value, array $info) : ?string
    {
        return $this->isFixedPoint($value, $info['size'], $info['scale']);
    }

    protected function validateBinary($value, array $info) : ?string
    {
        return $this->isBytelen($value, $info['size']);
    }

    protected function validateVarbinary($value, array $info) : ?string
    {
        return $this->isBytelen($value, $info['size']);
    }

    protected function validateTinytext($value, array $info) : ?string
    {
        return $this->isBytelen($value, static::INT1);
    }

    protected function validateText($value, array $info) : ?string
    {
        return $this->isBytelen($value, static::INT2);
    }

    protected function validateMediumtext($value, array $info) : ?string
    {
        return $this->isBytelen($value, static::INT3);
    }

    protected function validateLongtext($value, array $info) : ?string
    {
        return $this->isBytelen($value, static::INT4);
    }

    protected function validateTinyblob($value, array $info) : ?string
    {
        return $this->isBytelen($value, static::INT1);
    }

    protected function validateBlob($value, array $info) : ?string
    {
        return $this->isBytelen($value, static::INT2);
    }

    protected function validateMediumblob($value, array $info) : ?string
    {
        return $this->isBytelen($value, static::INT3);
    }

    protected function validateLongblob($value, array $info) : ?string
    {
        return $this->isBytelen($value, static::INT4);
    }

    protected function validateDate($value, array $info) : ?string
    {
        $message = $this->isDate($value);
        if (is_string($message)) {
            return $message;
        }

        return ($value >= '1000-01-01') ? null : static::INVALID_DATE;
    }

    protected function validateDateTime($value, array $info) : ?string
    {
        return $this->isDateTime($value, (int) $info['size']);
    }

    protected function validateTimestamp($value, array $info) : ?string
    {
        $good = $value >= 0 && $value < static::INT4_RANGE[1] + 1;

        $fsp = $info['size'];
        if (strlen(strrchr($value, '.')) > $fsp) {
            return static::INVALID_FSP;
        }

        return ($good) ? null : static::INVALID_UNIXTIME;
    }

    protected function validateYear($value, array $info)
    {
        $good = is_numeric($value)
            && (int) $value == $value
            && $value >= 1901
            && $value <= 2155;

        $zero = $value === '0000';

        return ($good || $zero) ? null : static::INVALID_YEAR;
    }
}
