<?php

namespace Bfg\Dev\Support\Http;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Class FormRequest
 * @package Bfg\Dev\Support\Http
 */
class FormRequest extends \Illuminate\Foundation\Http\FormRequest
{
    /**
     * Sets true only after successfully of save
     * @var bool
     */
    public $saved = false;

    /**
     * Model
     * @var string
     */
    protected $model;

    /**
     * Model getter
     * @return string
     */
    public function model()
    {
        return $this->model;
    }

    /**
     * Save any model with condition and request transform
     * @param  callable|bool  $condition
     * @param  Model|Relation|Builder|string  $model
     * @return bool|Builder|Model|Relation|\Illuminate\Support\Collection|mixed|string|void
     */
    public function saveIf(callable|bool $condition, Model|Relation|Builder|string $model)
    {
        $condition = is_callable($condition) ? embedded_call($condition) : $condition;
        return !!$condition ? $this->save($model) : false;
    }
    
    /**
     * Save any model with request transform 
     * @param  Model|Relation|Builder|string  $model
     * @return bool|Builder|Model|Relation|\Illuminate\Support\Collection|mixed|string|void
     */
    public function save(Model|Relation|Builder|string $model)
    {
        $return = save_model($model, $this->transform());
        $this->saved = !!$return;
        return $return;
    }

    /**
     * Transform and get a request validated result
     * @return array
     */
    public function transform()
    {
        return $this->transformation($this->validated());
    }

    /**
     * Transformation callback for request validated result
     * @param  array  $validated
     * @return array
     */
    protected function transformation(array $validated): array {

        return $validated;
    }
}