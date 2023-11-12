<?php

use Pkg6\VarDumper\VarDumper;

if (!function_exists('d')) {
    /**
     * Prints variables.
     *
     * @param mixed ...$variables Variables to be dumped.
     *
     * @see \Pkg6\VarDumper\VarDumper::dump()
     *
     * @psalm-suppress MixedAssignment
     */
    function d(...$variables): void
    {
        $highlight = PHP_SAPI !== 'cli';

        foreach ($variables as $variable) {
            VarDumper::dump($variable, 10, $highlight);
            echo $highlight ? '<br>' : PHP_EOL;
        }
    }
}


if (!function_exists('dd')) {
    /**
     * Prints variables and terminate the current script.
     *
     * @param mixed ...$variables Variables to be dumped.
     *
     * @see \Pkg6\VarDumper\VarDumper::dump()
     *
     * @psalm-suppress MixedAssignment
     */
    function dd(...$variables): void
    {
        d(...$variables);

        die(0);
    }
}