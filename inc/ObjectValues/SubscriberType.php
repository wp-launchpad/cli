<?php

namespace PSR2PluginBuilder\ObjectValues;

class SubscriberType
{
    const COMMON = 'COMMON';
    const FRONT = 'FRONT';

    const ADMIN = 'ADMIN';

    const VALUES = [self::ADMIN, self::FRONT, self::COMMON];

    protected $value;

    /**
     * @param $value
     */
    public function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function validate(string $value) {
        if(! in_array($value, self::VALUES, true)) {
            throw new InvalidValue();
        }
    }

}