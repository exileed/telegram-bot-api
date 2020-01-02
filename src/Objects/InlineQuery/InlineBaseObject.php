<?php

namespace Telegram\Bot\Objects\InlineQuery;

use BadMethodCallException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class InlineBaseObject.
 */
abstract class InlineBaseObject extends Collection
{
    /**
     * Magic method to set properties dynamically.
     *
     * @param $name
     * @param $arguments
     *
     * @return $this|mixed
     */
    public function __call($name, $arguments)
    {
        $action = substr($name, 0, 3);

        if ($action === 'set') {
            $property = Str::snake(substr($name, 3));
            $this->put($property, $arguments[0]);

            return $this;
        }

        throw new BadMethodCallException("Method {$name} does not exist.");
    }
}
