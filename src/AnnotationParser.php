<?php


namespace xiaozhuai;


use ReflectionClass;
use ReflectionException;
use Symfony\Component\Yaml\Yaml;

class AnnotationParser
{
    protected $classMap;
    protected $scopes = [];

    /**
     * AnnotationParser constructor.
     *
     * @param array | null $classMap
     */
    public function __construct($classMap = null)
    {
        $this->classMap = $classMap;
    }

    /**
     * @param string $str
     * @return array
     * @throws ReflectionException
     */
    public function parse($str)
    {
        preg_match_all('/@(\S+)\s*\{(.*?)\}/s', $str, $matches);
        if (count($matches) !== 3) {
            return [];
        }

        $scopes = [];
        $scopeCount = count($matches[0]);
        for ($i = 0; $i < $scopeCount; $i++) {
            $scopeName = $matches[1][$i];
            $scopeContent = $matches[2][$i];
            $scope = $this->parseScope($scopeContent);
            if ($scope === null) {
                continue;
            }
            if ($this->classMap !== null && isset($this->classMap[$scopeName])) {
                $scope = $this->toObject($this->classMap[$scopeName], $scope);
            }

            $scopes[$scopeName] = $scope;
        }

        $this->scopes = $scopes;
        return $this->scopes;
    }

    /**
     * @return array
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    public function getScope($key, $default = null)
    {
        if (!array_key_exists($key, $this->scopes)) {
            return $default;
        }
        return $this->scopes[$key];
    }

    /**
     * @param string $className
     * @param array $arr
     * @return object
     * @throws ReflectionException
     */
    protected function toObject($className, $arr)
    {
        $reflectionClass = new ReflectionClass($className);
        if ($reflectionClass->hasMethod('__construct')) {
            return $reflectionClass->newInstanceArgs([$arr]);
        }

        $obj = $reflectionClass->newInstance();
        foreach ($arr as $key => $value) {
            if ($reflectionClass->hasProperty($key)) {
                $reflectionClass->getProperty($key)->setValue($obj, $value);
            }
        }
        return $obj;

    }

    /**
     * @param string $str
     * @return array | null
     */
    protected function parseScope(string $str)
    {
        $lines = explode("\n", $str);

        $lines = array_map(static function ($item) {
            $item = trim($item);
            $item = preg_replace('/^(\s|\*)*/', '', $item);
            return $item;
        }, $lines);

        $lines = array_values(array_filter($lines));

        $content = implode("\n", $lines);
        return $this->parseYaml($content);
    }

    /**
     * @param string $str
     * @return array | null
     */
    protected function parseYaml(string $str)
    {
        if (function_exists('yaml_parse')) {
            return yaml_parse($str);
        }

        return Yaml::parse($str);
    }
}
