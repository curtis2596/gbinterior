<?php

namespace App\Helpers;

class Util
{
    public static function numToChar($num)
    {
        $neg = $num < 0;

        $num = abs($num);

        $str = "";

        $alpha = [
            'A', 'B', 'C', 'D', 'E',
            'F', 'G', 'H', 'I', 'J',
            'K', 'L', 'M', 'N', 'O',
            'P', 'Q', 'R', 'S', 'T',
            'U', 'V', 'W', 'X', 'Y',
            'Z'
        ];

        $len = count($alpha);

        if ($num < $len) {
            $str = $alpha[$num];
        } else {
            while ($num > 0) {
                $mod = ($num % $len);
                $num = (int) ($num / $len);

                $str = $alpha[$mod] . $str;
            }
        }

        if ($neg) {
            $str = "-" . $str;
        }

        return $str;
    }

    /**
     * remove the slashs in path
     * @param string $path
     * @param string $side FIRST, LAST
     * @return string
     */
    public static function removePathSlashs($path, $side = '')
    {
        $side = strtoupper($side);
        $path = trim(str_replace('\\', '/', $path));

        if ($side == 'FIRST' || $side == 'START' || empty($side)) {
            if (substr($path, 0, 1) == "/") {
                $path = substr($path, 1, strlen($path));
            }
        }

        if ($side == 'LAST' || $side == 'END' || empty($side)) {
            if (substr($path, -1) == "/") {
                $path = substr($path, 0, strrpos($path, "/"));
            }
        }
        return $path;
    }

    /**
     * Add slashs in path
     * @param string $path
     * @param string $side FIRST, LAST
     * @return string
     */
    public static function addPathSlashs($path, $side = '')
    {
        $side = strtoupper($side);
        $path = trim(str_replace('\\', '/', $path));

        if ($side == 'FIRST' || $side == 'START' || empty($side)) {
            if (substr($path, 0, 1) != "/") {
                $path = "/" . $path;
            }
        }

        if ($side == 'LAST' || $side == 'END' || empty($side)) {
            if (substr($path, -1) != "/") {
                $path .= "/";
            }
        }

        return $path;
    }

    /**
     * Following function convert any type of object to array
     * it can convert xml, json object to array
     * 
     * @param object $obj
     * @return array
     */
    public static function objToArray($obj)
    {
        $arr = array();
        if (gettype($obj) == "object") {
            $arr = self::objToArray(get_object_vars($obj));
        } else if (gettype($obj) == "array") {
            foreach ($obj as $k => $v) {
                $arr[$k] = self::objToArray($v);
            }
        } else {
            $arr = $obj;
        }

        return $arr;
    }

    /**
     * sort array on basis char len
     * @param array $arr
     * @return array
     */
    public static function sortArrayOnValueStringLength($arr)
    {
        $temp_list = array_flip($arr);
        $arr = array_keys($temp_list);

        $n = count($arr);
        for ($i = 0; $i < $n; $i++) {
            for ($a = $i + 1; $a < $n; $a++) {
                if (strlen($arr[$a]) < strlen($arr[$i])) {
                    $temp = $arr[$i];
                    $arr[$i] = $arr[$a];
                    $arr[$a] = $temp;
                }
            }
        }

        $ret = array();

        foreach ($arr as $v) {
            if (isset($temp_list[$v])) {
                $ret[$temp_list[$v]] = $v;
            }
        }
        return $ret;
    }

    /**
     * get rondom string in given char string
     * @param int $length
     * @param String $valid_chars
     * @return string
     */
    public static function getRandomString($length, $valid_chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890")
    {
        $random_string = "";
        $num_valid_chars = strlen($valid_chars);
        for ($i = 0; $i < $length; $i++) {
            $random_pick = mt_rand(1, $num_valid_chars);
            $random_char = trim($valid_chars[$random_pick - 1]);

            if (!$random_char) {
                $i--;
            } else {
                $random_string .= $random_char;
            }
        }
        return $random_string;
    }

    public static function urlencode($data)
    {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $data[$k] = self::urlencode($v);
            }

            return $data;
        } else {
            return urlencode($data);
        }
    }


    public static function getTreeArray(array $records, $parent_field, $parentId = 0)
    {
        $data = array();

        foreach ($records as $element) {
            if ($element[$parent_field] == $parentId) {
                $children = self::getTreeArray($records, $parent_field, $element['id']);

                if ($children) {
                    $element['children'] = $children;
                }

                $data[] = $element;
            }
        }

        return $data;
    }

    public static function getTreeListArray(array $tree, $key, $value, $only_parent = false, $only_child = true, $prefix = "", $sep = " | ")
    {
        $list = array();

        foreach ($tree as $node) {
            $id = $node[$key];
            $name = $prefix . $node[$value];

            if ($only_parent) {
                $list[$id] = $name;
            } else if ($only_child) {
                if (isset($node["children"]) && !empty($node["children"])) {
                    $list += self::getTreeListArray($node["children"], $key, $value, $only_parent, $only_child, $name . $sep, $sep);
                } else {
                    $list[$id] = $name;
                }
            } else {
                $list[$id] = $name;

                if (isset($node["children"]) && !empty($node["children"])) {
                    $list += self::getTreeListArray($node["children"], $key, $value, $only_parent, $only_child, $name . $sep, $sep);
                }
            }
        }

        return $list;
    }

    public static function niceBytes($bytes, $count = 0)
    {
        if ($bytes > 1024) {
            return self::niceBytes(round($bytes / 1024, 2), $count + 1);
        }

        $sizes = array("Bytes", "Kb", "Mb", "Gb");

        return $bytes . " " . $sizes[$count];
    }

    public static function niceTime($time)
    {
        if ($time < 60) {
            return $time . " s";
        }

        $min = floor($time / 60);
        $seconds = $time % 60;

        if ($min < 60) {
            if ($seconds) {
                return $min . " m $seconds s";
            }

            return $min . " m ";
        }

        $hour = floor($min / 60);
        $min = $min % 60;

        if ($hour < 24) {
            if ($min) {
                return $hour . " h $min m";
            }

            return $hour . " h ";
        }

        $days = floor($hour / 24);
        $hour = $hour % 24;

        if ($hour) {
            return $days . " d $hour h $min m";
        }

        return $days . " d";
    }

    public static function niceNumber($number, $count = 0)
    {
        if ($number > 1000) {
            return self::niceNumber(round($number / 1000, 2), $count + 1);
        }

        $types = array("", "K", "M", "T");

        return $number . " " . $types[$count];
    }

    public static function replaceKeys($data, $map_keys, $keep_both = false)
    {
        foreach ($map_keys as $key => $rep_key) {
            if (is_array($rep_key)) {
                if (isset($map_keys[$key])) {
                    $data[$key] = self::replaceKeys($rep_key, $map_keys[$key]);
                }
            } else if (isset($data[$key])) {
                $data[$rep_key] = $data[$key];
                if (!$keep_both) {
                    unset($data[$key]);
                }
            }
        }

        return $data;
    }

    public static function applyAll($data, array $options)
    {
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $data[$k] = self::applyAll($v, $options);
            } else {
                $v = self::StringOpearion($v, $options);
                $data[$k] = $v;
            }
        }

        return $data;
    }

    public static function applyAllforKey($data, array $options)
    {
        $ret_arr = array();
        foreach ($data as $k => $v) {
            $k = self::StringOpearion($k, $options);
            $ret_arr[$k] = $v;
        }

        return $ret_arr;
    }

    public static function StringOpearion($v, array $operations)
    {
        foreach ($operations as $operation) {
            switch (strtolower($operation)) {
                case "trim":
                    $v = trim($v);
                    break;

                case "strtolower":
                    $v = strtolower($v);
                    break;

                case "strtoupper":
                    $v = strtoupper($v);
                    break;

                case "replace_multple_space_with_single_space":
                    $v = preg_replace('!\s+!', ' ', $v);
                    break;

                case "replace_space_with_hyphine":
                    $v = str_replace(" ", "-", $v);
                    break;

                case "replace_longstring_with_dash":
                    $v = strlen($v) > 90 ? substr($v, 0, 90) . "..." : $v;
                    break;

                default:
                    throw_exception("StringOpearion : $operation not availiable");
            }
        }

        return $v;
    }


    public static function decodeHtmlSpecialChars($arr)
    {
        foreach ($arr as $k => $val) {
            if (is_array($val)) {
                $arr[$k] = self::decodeHtmlSpecialChars($val);
            } else {
                $arr[$k] = preg_replace("/%u([0-9a-f]{3,4})/i", "&#x\\1;", urldecode($val));
            }
        }

        return $arr;
    }

    function curl_get_request($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }

    function curl_post_request($url, $params, $headers = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }
}
