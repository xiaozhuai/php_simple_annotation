<?php

require __DIR__ . '/vendor/autoload.php';

use xiaozhuai\AnnotationParser;


final class RouteParams
{
    public $group;
    public $method;
    public $pattern;
    public $isGroup = false;

    const ALL_METHODS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

    public function __construct($args)
    {
        $this->pattern = $args['pattern'] ?? '';
        $this->group = $args['group'] ?? null;
        $this->isGroup = !empty($this->group);

        $this->method = $args['method'] ?? null;

        if ($this->method !== null) {
            if ($this->method === 'ANY') {
                $this->method = static::ALL_METHODS;
            }

            if (!is_array($this->method)) {
                $this->method = [$this->method];
            }

            $this->method = array_map('strtoupper', $this->method);

            foreach ($this->method as $m) {
                if (!$this->isGroup && !in_array($m, static::ALL_METHODS, true)) {
                    throw new RuntimeException('Invalid method ' . $m);
                }
            }
        }
    }
}

class AuthParams
{
    public $auth;
    public $permission;
    public $desc;
}

/**
 * @Route {
 *      group : /order
 * }
 */
class TestAnnotationOrderController
{
    /**
     * @Route {
     *      method      : post
     *      pattern     : /create
     * }
     * @Auth {
     *      auth        : true
     * }
     */
    public function create()
    {

    }

    /**
     * @Route {
     *      method      : GET
     *      pattern     : /delete
     * }
     * @Auth {
     *      auth        : true
     *      permission  : delete_order
     *      desc        : delete order
     * }
     */
    public function delete()
    {

    }
}


$parser = new AnnotationParser([
    'Route' => RouteParams::class,
    'Auth' => AuthParams::class,
]);

$reflectionClass = new ReflectionClass('TestAnnotationOrderController');

print_r($parser->parse($reflectionClass->getDocComment()));

$methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
foreach ($methods as $method) {
    print_r($parser->parse($method->getDocComment()));
}
