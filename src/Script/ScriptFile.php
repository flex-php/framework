<?php

namespace Flex\Script;

use Flex\Exceptions\ScriptFunctionNotFoundException;
use Symfony\Component\Finder\SplFileInfo;

class ScriptFile
{
    public function __construct(protected string $filePath, protected array $data = [])
    {
        if(isset($data["request"])){
            $this->data["session"] = $data["request"]->getSession();
        }
    }

    public function getReturn(): mixed
    {
        return (function($path, $data){
            extract($data);
            return include $path;
        })($this->filePath, $this->data);
    }

    /**
     * @param string $functionName
     * @param array $arguments
     * @return mixed
     * @throws \Exception
     */
    public function callFunction(string $functionName = null, array $arguments = [], mixed $defaultOutput = null): mixed
    {
        return (function($path, $functionName, $arguments, $defaultOutput){
            $response = include $path;

            if($functionName === null && is_callable($response)){
                return $response(...$arguments);
            }

            if(!is_array($response) || !array_key_exists($functionName, $response)){
                if($defaultOutput !== null){
                    return $defaultOutput;
                }

                throw new ScriptFunctionNotFoundException('Function does not exist');
            }

            return $response[$functionName](...$arguments);
        })($this->filePath, $functionName, $arguments, $defaultOutput);
    }
}