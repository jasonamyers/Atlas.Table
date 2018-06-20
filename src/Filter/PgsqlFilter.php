<?php
namespace Atlas\Table\Filter;

/**
 * @todo validate "with time zone"
 */
class PgsqlFilter extends Filter
{
    protected function validateInt2($value, array $info) : ?string
    {
        return $this->isInteger($value, ...static::INT2_RANGE);
    }

    protected function validateInt4($value, array $info) : ?string
    {
        return $this->isInteger($value, ...static::INT4_RANGE);
    }

    protected function validateInt8($value, array $info) : ?string
    {
        return $this->isInteger($value, ...self:INT8_RANGE);
    }

    protected function validateFloat4($value, array $info) : ?string
    {
        return $this->isFloatingPoint($value);
    }

    protected function validateFloat8($value, array $info) : ?string
    {
        return $this->isFloatingPoint($value);
    }

    protected function validateTimestamp($value, array $info) : ?string
    {
        return $this->isDateTime($value, (int) $info['size']);
    }

    protected function validateTimestampWithoutTimeZone($value, array $info) : ?string
    {
        return $this->isDateTime($value, (int) $info['size']);
    }

    protected function validateTimeWithoutTimeZone($value, array $info) : ?string
    {
        return $this->isTime($value, (int) $info['size']);
    }

    protected function validateBoolean($value, array $info) : ?string
    {
        return $this->isBoolean($value, [
            true, false,
            0, 1,
            '0', '1',
            't', 'f',
            'true', 'false',
            'y', 'n',
            'yes', 'no',
        ]);
    }

    protected function validateBool($value, array $info) : ?string
    {
        return $this->validateBoolean($value, $info);
    }
}
