<?php


namespace xiaozhuai;


use ReflectionClass;
use ReflectionException;
use Symfony\Component\Yaml\Yaml;

class AnnotationParser
{
    protected $classMap;

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

        $data = [];
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

            $data[$scopeName] = $scope;
        }

        return $data;
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
            return $reflectionClass->newInstanceArgs($arr);
        } else {
            $obj = $reflectionClass->newInstance();
            foreach ($arr as $key => $value) {
                if ($reflectionClass->hasProperty($key)) {
                    $reflectionClass->getProperty($key)->setValue($obj, $value);
                }
            }
            return $obj;
        }

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
