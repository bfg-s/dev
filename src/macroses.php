<?php

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;

if (!Collection::hasMacro('paginate')) {

    Collection::macro('paginate', function ($perPage = 15, $pageName = 'page', $page = null) {

        $page = $page ?: (Paginator::resolveCurrentPage($pageName) ?: 1);

        return (new LengthAwarePaginator(
            $this->forPage($page, $perPage),
            $this->count(),
            $perPage,
            $page,
            ['pageName' => $pageName]
        ))->withPath('');
    });
}

if (!Collection::hasMacro('line_validate')) {

    Collection::macro('line_validate', function (array $rules, array $messages = []) {

        /** @var $this Collection */

        $result = \Illuminate\Support\Facades\Validator::make($this->all(), $rules, $messages);

        if ($result->fails()) {

            foreach ($result->errors()->messages() as $key => $message) {

                foreach ($message as $item) {

                    \Lar\Layout\Respond::glob()->toast_error($item);
                }
            }

            return false;
        }


        return $this;

    });
}

if (!Collection::hasMacro('validate')) {

    Collection::macro('validate', function (array $rules, array $messages = []) {

        /** @var $this Collection */
        return $this->values()->reject(function ($array) use ($rules, $messages) {

            return \Illuminate\Support\Facades\Validator::make($array, $rules, $messages)->fails();
        });
    });
}

if (!Router::hasMacro('gets')) {

    Router::macro('gets', function ($gets, ...$props) {

        if (!is_array($gets)) {

            $gets = func_get_args();
        }

        $gets = implode(',', $gets);

        return $this->middleware("gets:{$gets}");
    });
}