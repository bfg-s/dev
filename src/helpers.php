<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

if (!function_exists("is_image")) {

    /**
     * Is Image
     *
     * @param $path
     * @return bool
     */
    function is_image($path)
    {
        try {

            return !!exif_imagetype($path);

        } catch (Exception $exception) {}

        return false;
    }
}

if (! function_exists("is_embedded_call") ) {

    /**
     * @param mixed $subject
     * @return bool
     */
    function is_embedded_call ($subject) {

        return is_string($subject) ? class_exists($subject) : is_callable($subject);
    }
}

if (! function_exists("resulted_event") ) {

    /**
     * Dispatch an event with save a last true result of listener.
     * @param  object  $event
     * @return object
     */
    function resulted_event (object $event) {

        $event->result = event($event);

        if (count($event->result)) {
            $event->result = array_filter($event->result, fn ($i) => !!$i);
            $event->result = $event->result[array_key_last($event->result)];
            $event->result = is_object($event->result) ? $event->result : (object)\Arr::wrap($event->result);
        }

        return $event->result;
    }
}

if (!function_exists('pipeline')) {

    /**
     * @param $send
     * @param  array  $pipes
     * @return mixed|$send
     */
    function pipeline ($send, array $pipes) {

        return app(\Illuminate\Pipeline\Pipeline::class)
            ->send($send)
            ->through($pipes)
            ->thenReturn();
    }
}

if (! function_exists("is_functional_call") ) {

    /**
     * @param mixed $subject
     * @return bool
     */
    function is_functional_call ($subject) {

        return (is_array($subject) && is_callable($subject) || $subject instanceof Closure);
    }
}

if (! function_exists("is_assoc") ) {

    /**
     * @param  array  $arr
     * @return bool
     */
    function is_assoc(array $arr)
    {
        if ([] === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}

if (!function_exists("is_json")) {

    /**
     * @param $string
     * @param bool $return_data
     * @return bool|mixed
     */
    function is_json($string, $return_data = false) {

        if (!is_string($string)) {

            return false;
        }

        $data = json_decode($string, 1);

        return (json_last_error() == JSON_ERROR_NONE) ? ($return_data ? $data : TRUE) : FALSE;
    }
}

if (! function_exists("array_merge_recursive_distinct") ) {

    /**
     * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
     * keys to arrays rather than overwriting the value in the first array with the duplicate
     * value in the second array, as array_merge does. I.e., with array_merge_recursive,
     * this happens (documented behavior):
     *
     * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('org value', 'new value'));
     *
     * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
     * Matching keys' values in the second array overwrite those in the first array, as is the
     * case with array_merge, i.e.:
     *
     * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('new value'));
     *
     * Parameters are passed by reference, though only for performance reasons. They're not
     * altered by this function.
     *
     * @param array $array1
     * @param array $array2
     * @return array
     * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
     * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
     */
    function array_merge_recursive_distinct ( array &$array1, array &$array2 )
    {
        $merged = $array1;

        foreach ( $array2 as $key => &$value )
        {
            if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
            {
                $merged [$key] = array_merge_recursive_distinct ( $merged [$key], $value );
            }
            else
            {
                $merged [$key] = $value;
            }
        }

        return $merged;
    }
}

if (!function_exists("lang_in_text")) {

    /**
     * @param $string
     * @return bool|mixed
     */
    function lang_in_text($string) {

        if (is_string($string)) {

            $string = preg_replace_callback('/\@([a-zA-Z0-9\_\-\.]+)/', function ($m) {
                return __($m[1]);
            }, $string);
        }

        return $string;
    }
}

if (!function_exists('array_dots_uncollapse')) {

    /**
     * @param  array  $array
     * @return array
     */
    function array_dots_uncollapse(array $array) {

        $result = [];

        foreach ($array as $key => $value) {

            Arr::set($result, $key, $value);
        }

        return $result;
    }
}

if (! function_exists("embedded_call") ) {

    /**
     * @param $subject
     * @param  array  $arguments
     * @param  null  $throw_event
     * @return mixed
     */
    function embedded_call (callable|array $subject, array $arguments = [], $throw_event = null) {

        return (new \Bfg\Dev\Support\Behavior\EmbeddedCall($subject, $arguments, $throw_event))->call();
    }
}

if (! function_exists("save_model") ) {

    /**
     * @param  Model|Relation|Builder|string  $model
     * @param  array|string  $data
     * @return bool|Builder|Model|Relation|\Illuminate\Support\Collection|mixed|string|void
     */
    function save_model (Model|Relation|Builder|string $model, array|string $data = []) {

        return \Bfg\Dev\Support\Eloquent\ModelInjector::do($model, $data);
    }
}