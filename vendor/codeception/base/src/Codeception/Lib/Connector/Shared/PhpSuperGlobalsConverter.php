<?php
namespace Codeception\Lib\Connector\Shared;


trait PhpSuperGlobalsConverter
{
    
    protected function remapFiles(array $requestFiles)
    {
        $files = $this->rearrangeFiles($requestFiles);

        return $this->replaceSpaces($files);
    }

    
    protected function remapRequestParameters(array $parameters)
    {
        return $this->replaceSpaces($parameters);
    }

    private function rearrangeFiles($requestFiles)
    {
        $files = [];
        foreach ($requestFiles as $name => $info) {
            if (!is_array($info)) {
                continue;
            }

            
            $hasInnerArrays = count(array_filter($info, 'is_array'));

            if ($hasInnerArrays || !isset($info['tmp_name'])) {
                $inner = $this->remapFiles($info);
                foreach ($inner as $innerName => $innerInfo) {
                    
                    $innerInfo = array_map(
                        function ($v) use ($innerName) {
                            return [$innerName => $v];
                        },
                        $innerInfo
                    );

                    if (empty($files[$name])) {
                        $files[$name] = [];
                    }

                    $files[$name] = array_replace_recursive($files[$name], $innerInfo);
                }
            } else {
                $files[$name] = $info;
            }
        }

        return $files;
    }

    
    private function replaceSpaces($parameters)
    {
        $qs = http_build_query($parameters, '', '&');
        parse_str($qs, $output);

        return $output;
    }
}
