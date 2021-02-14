<?php

namespace Bfg\Dev\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Class CoreRepository
 *
 * @package Bfg\Dev
 *
 * Repository for working with an entity.
 * Can issue datasets, cannot create/modify entities.
 */
abstract class CoreRepository
{
    /**
     * @var Model
     */
    private Model $model;

    /**
     * Resource for wrap data
     * @var string|null
     */
    private $resource;

    /**
     * Cache singleton requests
     * @var array
     */
    static protected array $cache = [];

    /**
     * CoreRepository constructor.
     */
    public function __construct()
    {
        $this->model = app($this->getModelClass());
    }

    /**
     * @param  string  $name
     * @return bool
     */
    public function has_cache(string $name)
    {
        return array_key_exists($name, static::$cache);
    }

    /**
     * Cache and get method data
     * @param  string  $name
     * @param  array  $arguments
     * @return mixed
     */
    public function cache(string $name, array $arguments = [])
    {
        if ($this->resource) {

            $resource = $this->resource;

            $this->resource = null;

            return $this->wrap($resource, $name, $arguments);

        } else if (!$this->has_cache($name)) {

            if (method_exists($this, $name)) {

                static::$cache[$name] = embedded_call([$this, $name], $arguments);
            }

            else {

                return null;
            }
        }

        return static::$cache[$name];
    }

    /**
     * Remove and cache again and get data
     * @param  string  $name
     * @param  array  $arguments
     * @return mixed
     */
    public function re_cache(string $name, array $arguments = [])
    {
        if ($this->has_cache($name)) {
            unset(static::$cache[$name]);
        }

        return $this->cache($name, $arguments);
    }

    /**
     * @param  string  $name
     * @param  array  $arguments
     * @return $this
     */
    public function init_cache(string $name, array $arguments = [])
    {
        $this->re_cache($name, $arguments);

        return $this;
    }

    /**
     * @param $equal
     * @param  string  $name
     * @param  array  $arguments
     * @return $this
     */
    public function init_eq_cache($equal, string $name, array $arguments = [])
    {
        if ($equal) {

            $this->re_cache($name, $arguments);
        }

        return $this;
    }


    public function resource(string $resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * @param  string  $resource
     * @param  string|null  $method
     * @param  array  $arguments
     * @return $this|mixed
     */
    public function wrap(string $resource, string $method = null, array $arguments = [])
    {
        if ($method) {

            $result = $this->cache($method, $arguments);

            if (($result instanceof Collection || $result instanceof LengthAwarePaginator) && method_exists($resource, 'collection')) {

                $result = $resource::collection($result);

            } else if (method_exists($resource, 'make')) {

                $result = $resource::make($result);

            } else {

                $result = new $resource($result);
            }

            return $result;
        }

        return $this->resource($resource);
    }

    /**
     * Model class namespace getter
     *
     * @return string
     */
    abstract protected function getModelClass(): string;

    /**
     * @return Model
     */
    public function model(): Model
    {
        return clone $this->model;
    }

    /**
     * Cache and get
     * @param  string  $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->cache($name);
    }

    /**
     * @param  string  $name
     * @param $value
     */
    public function __set(string $name, $value): void
    {
        static::$cache[$name] = $value;
    }

    /**
     * @param  string  $name
     * @param  array  $arguments
     * @return mixed
     */
    public function __invoke(string $name, array $arguments = [])
    {
        return $this->re_cache($name, $arguments);
    }
}