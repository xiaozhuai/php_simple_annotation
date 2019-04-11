# php_simple_annotation
A simple and sexy annotation library for php based on yaml.

If `ext-yaml` is installed, use it to parse yaml , otherwise use `symfony/yaml`.

## Installation
``` bash
$ composer require xiaozhuai/php_simple_annotation
```

## Syntax
```
@<scope> {
    <yaml content>
}
```

## Sample Code
``` php
<?php

require __DIR__ . '/vendor/autoload.php';

use xiaozhuai\AnnotationParser;


final class RouteParams
{
    public $group;
    public $method;
    public $pattern;
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
     *      method      : POST
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

// return array if no classmap provided
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
```

## Output
```
Array
(
    [Route] => RouteParams Object
        (
            [group] => /order
            [method] => 
            [pattern] => 
        )

)
Array
(
    [Route] => RouteParams Object
        (
            [group] => 
            [method] => POST
            [pattern] => /create
        )

    [Auth] => AuthParams Object
        (
            [auth] => 1
            [permission] => 
            [desc] => 
        )

)
Array
(
    [Route] => RouteParams Object
        (
            [group] => 
            [method] => GET
            [pattern] => /delete
        )

    [Auth] => AuthParams Object
        (
            [auth] => 1
            [permission] => delete_order
            [desc] => delete order
        )

)
```
