<?php

namespace Feature\AppApi;

class AppApi
{

    public static function call(
        string $method,
        string $endpoint,
        array $data = [],
    ) {
        $endpoint = ltrim($endpoint, '/');
        $endpoint = "/api/{$endpoint}";

        return test()->call(
            method: $method,
            uri: $endpoint,
            data: $data,
        );
    }

}
