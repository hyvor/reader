<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context): Kernel {
    $env = isset($context['APP_ENV']) && is_string($context['APP_ENV']) ? $context['APP_ENV'] : 'dev';
    $debug = !empty($context['APP_DEBUG']);
    return new Kernel($env, $debug);
};
