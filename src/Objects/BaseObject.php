<?php

namespace Telegram\Bot\Objects;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class BaseObject.
 */
abstract class BaseObject extends Collection
{
    /**
     * Builds collection entity.
     *
     * @param array|mixed $data
     */
    public function __construct($data)
    {
        parent::__construct($this->getRawResult($data));

        $this->mapRelatives();
    }

    /**
     * Property relations.
     *
     * @return array
     */
    abstract public function relations();

    /**
     * Get an item from the collection by key.
     *
     * @param mixed $key
     * @param mixed $default
     *
     * @return mixed|static
     */
    public function get($key, $default = null)
    {
        if ($this->offsetExists($key)) {
            return is_array($this->items[$key]) ? new static($this->items[$key]) : $this->items[$key];
        }

        return value($default);
    }

    /**
     * Map property relatives to appropriate objects.
     *
     * @return array|bool
     */
    public function mapRelatives()
    {
        $relations = collect($this->relations());

        if ($relations->isEmpty()) {
            return false;
        }

        return $this->items = collect($this->all())
            ->map(function ($value, $key) use ($relations) {
                if ($relations->has($key)) {
                    $className = $relations->get($key);

                    return new $className($value);
                }

                return $value;
            })
            ->all();
    }

    /**
     * Returns raw response.
     *
     * @return array|mixed
     */
    public function getRawResponse()
    {
        return $this->items;
    }

    /**
     * Returns raw result.
     *
     * @param $data
     *
     * @return mixed
     */
    public function getRawResult($data)
    {
        return Arr::get($data, 'result', $data);
    }

    /**
     * Get Status of request.
     *
     * @return mixed
     */
    public function getStatus()
    {
        return Arr::get($this->items, 'ok', false);
    }

    /**
     * Magic method to get properties dynamically.
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $action = substr($name, 0, 3);

        if ($action === 'get') {
            $property = Str::snake(substr($name, 3));
            $response = $this->get($property);

            // Map relative property to an object
            $relations = $this->relations();
            if ($response !== null && isset($relations[$property])) {
                return new $relations[$property]($response);
            }

            return $response;
        }

        return false;
    }
}
