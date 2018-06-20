<?php
namespace Atlas\Table\Filter;

class SqlsrvFilter extends Filter
{
    const INVALID_BIT = 'INVALID_BIT';

    protected function validateBit($value, array $info) : ?string
    {
        $bits = [0, 1, true, false];
        return in_array($value, $bits, true) ? null : static::INVALID_BIT;
    }

    protected function validateTinyint($value, array $info) : ?string
    {
        return $this->isInteger($value, ...static::INT1_RANGE);
    }

    protected function validateCharVarying($value, array $info) : ?string
    {
        return $this->isBytelen($value, $info['size']);
    }

    protected function validateBinary($value, array $info) : ?string
    {
        return $this->isBytelen($value, $info['size']);
    }

    protected function validateVarbinary($value, array $info) : ?string
    {
        return $this->isBytelen($value, $info['size']);
    }

    protected function validateText($value, array $info) : ?string
    {
        return $this->isBytelen($value, static::INT4_RANGE[1] - 1);
    }

    protected function validateDateTime($value, array $info) : ?string
    {
        $message = $this->isDateTime($value, 3);
        if (is_string($message)) {
            return $message;
        }

        // FSP must be rounded to increments of .000, .003, or .007
        $fsp = strrchr($value, '.');
        $len = strlen($fsp);
        if ($len == 3 && ! in_array(substr($fsp, -1), ['0', '3', '7'])) {
            return static::INVALID_FSP;
        }

        return ($value >= '1753-01-01') ? null : static::INVALID_DATE;
    }

    protected function validateDateTime2($value, array $info) : ?string
    {
        return $this->isDateTime($value, $info['size']);
    }
}
