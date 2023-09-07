<?php


class test
{

    function plusMinus($arr)
    {
        



       // Write your code here
       $positive = 0;
       $negative = 0;
       $zero = 0;

       $length = count($arr);
       $start = 0;
       $end = $length - 1;

      echo sprintf("%d\n", $start);
      echo sprintf("---------%d\n", $length%2);

      do {
        $arr[$start] > 0 ? $positive += 1 : ($arr[$start] < 0 ? $negative += 1 : $zero += 1);
        if($length%2 == 1) {
            $arr[$end] > 0 ? $positive += 1 : ($arr[$end] < 0 ? $negative += 1 : $zero += 1);
        }
        
        $start++;
        $end--;

        echo sprintf("start: %d\n", $start);
        echo sprintf("end: %d\n", $end);
      } while (($start < $end && $end > $start));
      
echo sprintf("positive: %b\n", $positive);
echo sprintf("negative: %b\n", $negative);
echo sprintf("zero: %b\n", $zero);


       echo sprintf("%.6f\n", $positive / $length);
       echo sprintf("%.6f\n",$negative / $length);
       echo sprintf("%.6f",$zero / $length);
   }
}

$arr = [0,0,-1,1,1];
        // $arr = [];

        // $arr = [1,-2,-7,9,1,-8,-5];
(new test())->plusMinus($arr);