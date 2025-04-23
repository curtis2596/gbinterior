<?php

namespace App\Helpers;

use Exception;

class ArrayHelper
{
    private Array $data; 
    public function __construct(Array $data)
    {
        $this->data = $data;
    }

    protected function throw_exception($msg)
    {
        if (function_exists("throw_exception"))
        {
            throw_exception($msg);
        }
        else
        {
            $callBy = debug_backtrace()[1];

            $prefix = "ArrayHelper -> ";
            
            if (isset($callBy['function']))
            {
                $call_fn_name = $callBy['function'];

                $prefix .= $call_fn_name . "() : ";
            }

            throw new Exception($prefix . $msg);
        }
    }

    public function getOnlyWhichHaveKeys(Array $keys, bool $recursive = false, bool $will_throw_exception_on_no_key_found = false)
    {
        $ret = [];

        foreach($keys as $k)
        {
            if (isset($this->data[$k]))
            {
                $ret[$k] = $this->data[$k];
            }
            else
            {
                if ($will_throw_exception_on_no_key_found)
                {
                    throw new Exception("$k not found in Array");
                }
            }
        }

        return $ret;
    }
    public function ignoreKeys($keys)
    {
        $ret = $this->data;

        foreach($keys as $k)
        {
            unset($ret[$k]);
        }

        return $ret;
    }
}