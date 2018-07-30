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
