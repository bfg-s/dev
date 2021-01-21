<?php

namespace Bfg\Dev;

/**
 * Class FormRequest
 * @package Bfg\Dev
 */
class FormRequest extends \Illuminate\Foundation\Http\FormRequest
{
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