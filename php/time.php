<?php

/**
     * 字符串格式时间 TO UNIX时间戳
     *
     * @param $times
     * @return mixed
     */
    public  function strTime2UnixTime($times)
    {
        array_walk($times, function(&$item) {
            $item = strtotime($item);
        });

        return $times;
    }
    
    /**
     *
     * 获取当前时间前一天
     * 
     * @author: MaHui 397091486@qq.com 2019-04-26 - 13:43
     *
     * @param bool $format
     *
     * @return false|int|string
     */
    private function getBeforeDay($format=false)
    {
        $beforeDayTimestamp = strtotime('-1 day');
        if ($format)
            return date($format, $beforeDayTimestamp);

        return $beforeDayTimestamp;
    }

	/**
	 * 获取一天中 开始时间和结束时间
	 * @param $time
	 *
	 * @return mixed
	 */
    public  function getMNTime($time)
    {
    	$data['start_time'] = strtotime($time . '00:00:01');
    	$data['end_time']   = strtotime($time . '23:59:59');

    	return $data;
    }

	/**
	 * 获取某一天的开始时间
	 *
	 * @param $time
	 *
	 * @return false|int
	 */
    public  function getSTimeOfOneDay($time)
    {
	    $tm = date('Y-m-d 00:00:01', strtotime($time));
	    return strtotime($tm);
    }


    /**
	 * @param $start
	 * @param $end
	 *
	 * @return array
	 */
	public  function dateFormat($start,$end){
		$result = array();
		$result['day'] = date('m月d日',$start);
		$week = date('w',$start);
		$result['week'] = self::weekFormat($week);
		$result['time'] = date('H:i',$start).' - '.date('H:i',$end);
		return $result;
	}


	/**
	 * @param $time
	 *
	 * @return string
	 */
	private  function weekFormat($time){
		switch($time){
			case 1:
				return '星期一';
			case 2:
				return '星期二';
			case 3:
				return '星期三';
			case 4:
				return '星期四';
			case 5:
				return '星期五';
			case 6:
				return '星期六';
			case 7:
				return '星期日';
		}
	}

	/**
	 * calculate diffdays
	 */
	function diffDays($s_time, $e_time = '')
	{
		if (empty($e_time))
			$e_time = time();
		$time = $e_time - $e_time;
		$days = floor($time / 86400);

		return $days;
	}
