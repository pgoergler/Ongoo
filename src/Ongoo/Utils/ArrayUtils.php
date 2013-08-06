<?php

namespace Ongoo\Utils;

/**
 * Description of ArrayUtils
 *
 * @author paul
 */
class ArrayUtils
{

    public static function merge(array $configuration, $mode = 'all')
    {
        $cfg = isset($configuration['all']) ? $configuration['all'] : array();

        if (isset($configuration[$mode]) && is_array($configuration[$mode]))
        {
            $cfg = static::merge_recursive_simple($cfg, $configuration[$mode]);
        }
        return $cfg;
    }

    public static function merge_recursive_simple(array $a1, array $a2)
    {
        if (func_num_args() < 2)
        {
            trigger_error(__FUNCTION__ . ' needs two or more array arguments', E_USER_WARNING);
            return;
        }
        $arrays = func_get_args();
        $merged = array();
        while ($arrays)
        {
            $array = array_shift($arrays);
            if (!is_array($array))
            {
                trigger_error(__FUNCTION__ . ' encountered a non array argument', E_USER_WARNING);
                return;
            }
            if (!$array)
                continue;
            foreach ($array as $key => $value)
                if (is_string($key))
                    if (is_array($value) && array_key_exists($key, $merged) && is_array($merged[$key]))
                        $merged[$key] = static::merge_recursive_simple($merged[$key], $value);
                    else
                        $merged[$key] = $value;
                else
                    $merged[$key] = $value;
        }
        return $merged;
    }

    protected static function findKeys($configuration, $root = null)
    {
        $keys = array();
        foreach ($configuration as $key => $value)
        {
            $key = str_replace('.', '\\+', $key);
            if (!is_null($root))
            {
                $key = "$root.$key";
            }
            $keys[] = $key;


            if (is_array($value) && count($value) > 0)
            {
                $keys = array_merge($keys, self::findKeys($value, $key));
            }
        }
        return $keys;
    }

    public static function flatternPathConfiguration($configuration)
    {
        $keys = static::findKeys($configuration);

        foreach ($keys as $key)
        {
            $php = "['" . str_replace('.', "']['", $key) . "']";
            $php = str_replace('\\+', '.', $php);
            $key = str_replace('\\+', '.', $key);

            eval('$configuration[$key] = $configuration' . $php . ';');
        }
        return $configuration;
    }

}

?>
