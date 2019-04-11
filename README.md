# php_simple_annotation
一个小巧且性感的php注解库, 使用yaml增加可读性.

默认使用`symfony/yaml`来解析yaml, 如果安装了yaml扩展, 则使用`ext-yaml`扩展库来解析yaml以提高性能.

## 注解语法
```
@<scope> {
    <yaml content>
}
```

## 示例

### code
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

### output
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
