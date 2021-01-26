<?php

namespace Bfg\Dev\Support\Eloquent;

use Bfg\Dev\Support\Http\FormRequest;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\UploadedFile;

/**
 * Class ModelInjector
 * @package Lar\Developer\Core
 */
class ModelInjector
{
    /**
     * Save model
     *
     * @var Model
     */
    protected $model;

    /**
     * Save data
     *
     * @var array
     */
    protected $data;

    /**
     * @var bool
     */
    protected $has_delete = false;

    /**
     * @var callable[]
     */
    protected static $on_save = [];

    /**
     * @var callable[]
     */
    protected static $on_saved = [];

    /**
     * @var callable[]
     */
    protected static $on_create = [];

    /**
     * @var callable[]
     */
    protected static $on_created = [];

    /**
     * @var callable[]
     */
    protected static $on_update = [];

    /**
     * @var callable[]
     */
    protected static $on_updated = [];

    /**
     * @var callable[]
     */
    protected static $on_delete = [];

    /**
     * @var callable[]
     */
    protected static $on_deleted = [];

    /**
     * ModelInjector constructor.
     *
     * @param  Model|Relation|Builder|string  $model
     * @param  array|string  $data  You can send a FormRequest
     */
    public function __construct(Model|Relation|Builder|string $model, array|string $data = [])
    {
        if (is_string($model)) {

            $model = app($model);
        }

        if (is_string($data)) {

            $data = app($data);

            if ($data instanceof FormRequest) {

                $data = $data->transform();

            } else { $data = []; }
        }

        $this->model = $model;

        $this->data = $data;
    }

    /**
     * Save method
     *
     * @return bool|void|mixed
     */
    public function save()
    {
        list($data, $add) = $this->getDatas();

        if ($this->model instanceof Model) {

            if ($this->model->exists) {

                return $this->update_model($data, $add);
            }

            else if (isset($data['id']) && $m = $this->model->find($data['id'])) {

                $this->model = $m;

                return $this->update_model($data, $add);
            }

            else {

                return $this->create_model($data, $add);
            }
        }

        else {

            return $this->create_model($data, $add);
        }
    }

    /**
     * Update model
     *
     * @param $data
     * @param $add
     * @return bool|void
     */
    protected function update_model($data, $add)
    {
        $r1 = $this->call_on('on_save', $this->data, $this->model);
        $r2 = $this->call_on('on_update', $this->data, $this->model);

        $result = $this->model->update(array_merge($data, $r1, $r2));

        $this->call_on('on_saved', $this->data, $result);
        $this->call_on('on_updated', $this->data, $result);

        if ($result) {

            foreach ($add as $key => $param) {

                if (is_array($param) && method_exists($this->model, $key)) {

                    $builder = $this->model->{$key}();

                    if ($builder instanceof BelongsToMany) {

                        $builder->sync($param);
                    }

                    else if ($builder instanceof HasMany) {
                        if (is_array($param) && isset($param[array_key_first($param)]) && is_array($param[array_key_first($param)])) {
                            $param = collect($param);
                            $params_with_id = $param->where('id');
                            $ids = $params_with_id->pluck('id')->toArray();
                            $has = $builder->whereIn('id', $ids)->get();
                            foreach ($params_with_id as $with_id_key => $with_id) {
                                if ($model = $has->where('id', $with_id['id'])->first()) {
                                    (new static($model, $with_id))->save();
                                } else {
                                    unset($ids[$with_id_key]);
                                }
                            }
                            foreach ($param->whereNotIn('id', $ids) as $item) {
                                (new static($this->model->{$key}(), $item))->save();
                            }
                        }
                        else {

                            (new static($this->model->{$key}(), $param))->save();
                        }
                    }

                    else {

                        (new static($this->model->{$key} ?? $builder, $param))->save();
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Create model
     * @param $data
     * @param $add
     * @return Model
     */
    protected function create_model($data, $add)
    {
        $r1 = $this->call_on('on_save', $this->data, $this->model);
        $r2 = $this->call_on('on_create', $this->data, $this->model);

        $this->model = $this->model->create(array_merge($data, $r1, $r2));

        if ($this->model) {

            $this->call_on('on_saved', $this->data, $this->model);
            $this->call_on('on_created', $this->data, $this->model);

            foreach ($add as $key => $param) {

                if (is_array($param) && method_exists($this->model, $key)) {

                    $builder = $this->model->{$key}();

                    if ($builder instanceof BelongsToMany) {

                        $builder->sync($param);
                    }

                    else if ($builder instanceof HasMany) {
                        if (is_array($param) && isset($param[array_key_first($param)]) && is_array($param[array_key_first($param)])) {
                            $param = collect($param);
                            $params_with_id = $param->where('id');
                            $ids = $params_with_id->pluck('id')->toArray();
                            $has = $builder->whereIn('id', $ids)->get();
                            foreach ($params_with_id as $with_id_key => $with_id) {
                                if ($model = $has->where('id', $with_id['id'])->first()) {
                                    (new static($model, $with_id))->save();
                                } else {
                                    unset($ids[$with_id_key]);
                                }
                            }
                            foreach ($param->whereNotIn('id', $ids) as $item) {
                                (new static($this->model->{$key}(), $item))->save();
                            }
                        }
                        else {

                            (new static($this->model->{$key}(), $param))->save();
                        }
                    }

                    else {

                        (new static($this->model->{$key} ?? $builder, $param))->save();
                    }
                }
            }

            return $this->model;
        }

        else {

            return $this->model;
        }
    }

    /**
     * @return array
     */
    protected function getFields()
    {
        $table = $this->getModelTable();

        if (!$table) {

            return [];
        }

        $fields = $this->model->getConnection()->getSchemaBuilder()->getColumnListing($table);

        return $fields;
    }

    /**
     * @return array[]
     */
    protected function getDatas()
    {
        $data = [];
        foreach ($this->data as $key => $datum) {

            if (is_object($datum) && $datum instanceof UploadedFile) {

                $data[$key] = LteFileStorage::makeFile($datum);
            }

            else {

                $data[$key] = $datum;
            }
        }
        $nullable = $this->getNullableFields();
        $result = [[]];
        foreach ($this->getFields() as $field) {
            if (array_key_exists($field, $data)) {
                if ($data[$field] !== '') {
                    $result[0][$field] = $data[$field];
                } else if (isset($nullable[$field]) && $nullable[$field]) {
                    $result[0][$field] = null;
                } else {
                    $result[0][$field] = $data[$field];
                }
                unset($data[$field]);
            }
        }
        $result[1] = $data;
        return $result;
    }

    /**
     * @return array
     */
    protected function getNullableFields()
    {
        $table = $this->getModelTable();

        if (!$table) {

            return [];
        }

        $fields = \DB::select(
            "SELECT COL.COLUMN_NAME, COL.IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS COL WHERE COL.TABLE_NAME = '{$table}'"
        );

        $clear_fields = [];

        foreach ($fields as $field) {

            $clear_fields[$field->COLUMN_NAME] = $field->IS_NULLABLE === 'YES';
        }

        return $clear_fields;
    }

    /**
     * @param  string  $name
     * @param  mixed  ...$params
     */
    protected function call_on(string $name, ...$params)
    {
        $events = static::$$name;
        $model = $this->getModel();
        $class = $model ? get_class($model) : false;

        $result = [];

        if ($class && isset($events[$class])) {
            foreach ($events[$class] as $item) {
                $r = call_user_func_array($item, $params);
                if (is_array($r) && count($r)) $result = array_merge_recursive($result, $r);
            }
        }

        return $result;
    }

    /**
     * @return string|null
     */
    public function getModelKeyName()
    {
        $model = $this->getModel();
        return $model ? $model->getKeyName() : null;
    }

    /**
     * @return string|null
     */
    public function getModelTable()
    {
        $model = $this->getModel();
        return $model ? $model->getTable() : null;
    }

    /**
     * @return Model|null
     */
    public function getModel()
    {
        $model = null;

        if ($this->model instanceof Relation) {

            $model = $this->model->getModel();

        } else if ($this->model instanceof Model) {

            $model = $this->model;

        } else if ($this->model instanceof Builder) {

            $model = $this->model->getModel();
        }

        return $model;
    }


    /**
     * @param  Model|Relation|Builder|string  $model
     * @param  array|string  $data
     * @return bool|Builder|Model|Relation|\Illuminate\Support\Collection|mixed|string|void
     */
    public static function do(Model|Relation|Builder|string $model, array|string $data = [])
    {
        if (is_array($data) && !is_assoc($data)) {

            $results = collect();

            foreach ($data as $datum) {

                if ($datum instanceof Arrayable) {

                    $datum = $datum->toArray();
                }

                if (is_array($datum) && count($datum) || is_string($datum)) {

                    $results->push((new static($model, $datum))->save());
                }
            }

            return $results;
        }

        return (new static($model, $data))->save();
    }

    /**
     * @param  string|callable  $model
     * @param  callable|null  $call
     */
    public static function on_save($model, callable $call = null)
    {
        static::on('save', $model, $call);
    }

    /**
     * @param  string|callable  $model
     * @param  callable|null  $call
     */
    public static function on_saved($model, callable $call = null)
    {
        static::on('saved', $model, $call);
    }

    /**
     * @param  string|callable  $model
     * @param  callable|null  $call
     */
    public static function on_create($model, callable $call = null)
    {
        static::on('create', $model, $call);
    }

    /**
     * @param  string|callable  $model
     * @param  callable|null  $call
     */
    public static function on_created($model, callable $call = null)
    {
        static::on('created', $model, $call);
    }

    /**
     * @param  string|callable  $model
     * @param  callable|null  $call
     */
    public static function on_update($model, callable $call = null)
    {
        static::on('update', $model, $call);
    }

    /**
     * @param  string|callable  $model
     * @param  callable|null  $call
     */
    public static function on_updated($model, callable $call = null)
    {
        static::on('updated', $model, $call);
    }

    /**
     * @param  string|callable  $model
     * @param  callable|null  $call
     */
    public static function on_delete(string $model, callable $call = null)
    {
        static::on('delete', $model, $call);
    }

    /**
     * @param  string|callable  $model
     * @param  callable|null  $call
     */
    public static function on_deleted(string $model, callable $call = null)
    {
        static::on('deleted', $model, $call);
    }

    /**
     * @param  string  $event
     * @param $model
     * @param  callable|null  $call
     */
    public static function on(string $event, $model, callable $call = null)
    {
        if (!$call && is_callable($model)) {

            $call = $model;

            $model = lte_controller_model();
        }

        $event = "on_$event";

        if ($model && property_exists(static::class, $event) && is_callable($call)) {

            $events = static::$$event;

            $events[$model][] = $call;

            static::$$event = $events;
        }
    }
}