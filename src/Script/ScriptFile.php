<?php

namespace Flex\Script;

use Flex\Exceptions\ScriptFunctionNotFoundException;
use Symfony\Component\Finder\SplFileInfo;

class ScriptFile
{
    public function __construct(protected string $filePath)
    {
    }

    public function getContents(): string
    {
        return file_get_contents($this->filePath);
    }

    public function getOutput(): string
    {
        try {
            $level = ob_get_level();
            ob_start();

            (function($path){
                include_once $path;
            })($this->filePath);

            $output = ob_get_clean();

            if($output === false){
                throw new \Exception('Output buffer is empty');
            }

            return $output;
        } catch (\Throwable $e) {
            while (ob_get_level() > $level) ob_end_clean();
            throw $e;
        }
    }

    public function getReturn(): mixed
    {
        return include_once $this->filePath;
    }

    /**
     * @param string $functionName
     * @param array $arguments
     * @return mixed
     * @throws \Exception
     */
    public function callFunction(string $functionName, array $arguments = [], mixed $defaultOutput = null): mixed
    {
        return (function($path, $functionName, $arguments, $defaultOutput){
            include_once $path;

            if(!function_exists($functionName)){
                if($defaultOutput !== null){
                    return $defaultOutput;
                }

                throw new ScriptFunctionNotFoundException('Function does not exist');
            }

            return $functionName(...$arguments);
        })($this->filePath, $functionName, $arguments, $defaultOutput);
    }
}