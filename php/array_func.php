<?php

       /**
	 * 根据数组中value进行排序
	 *
	 * @param $arr          array           待排序数据
	 * @param $sortValue    string or int   依据的value值
	 * @param int $sortFlag                 倒序 OR 正序
	 *
	 * @return array
	 */
    public static function arraySort($arr, $sortValue, $sortFlag = SORT_ASC)
    {
	    if (empty($arr)) return [];

	    foreach ($arr as $key => $value) {
	    	if (empty($value[$sortValue])) continue;
		    $sortFoundation[$key]  = $value[$sortValue];
	    }
	    array_multisort($sortFoundation, $sortFlag, $arr);

	    return $arr;
    }


     /**
     * 从 list 中 取出$KeyStr的item 返回 array(1212, 1212, 1212) 类似数据;
     *
     * @param $list
     * @param $KeyStr
     * @return array|bool
     */
    public static function arrayGet($list, $KeyStr)
    {

        if (empty($list) || empty($KeyStr)) return false;

        $ls = array();
        foreach ($list as $item) {
            $ls[] = $item[$KeyStr];
        }

        return $ls;
    }


    /**
     * 强制将某数组中的某些key的value转换成数组格式
     *
     * @author: MaHui 397091486@qq.com 2019-03-04 - 15:40
     *
     * @param $array
     * @param array $keys
     *
     * @return mixed
     */
    public function forceConvertArray($array, $keys = [])
    {
        if (empty($keys)) return $array;

        array_walk($array, function (&$item, $key) use ($keys) {
            $item = in_array($key, $keys) ? (array)$item : $item;
        });

        return $array;
    }
