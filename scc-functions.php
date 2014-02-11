<?php

//Excludes weekends and calculates the days between two dates
function DaysBetween($daylength, $weekbegins, $weekends, $start, $end) {

$oneday = new DateInterval("P1D");

$days = array();
$data = $daylength;
//$data = '12';

/* Iterate from $start up to $end+1 day, one day in each iteration.
   We add one day to the $end date, because the DatePeriod only iterates up to,
   not including, the end date. */
$modstart = $start->format('Y-m-d H:i:s');
$modstart = strtotime($modstart);
$modstart = strtotime("+1 day", $modstart);
$modstart = date("Y-m-d", $modstart);
if ($start->format('Y-m-d') == $end->format('Y-m-d') || $modstart == $end->format('Y-m-d')) {
    foreach(new DatePeriod($start, $oneday, $end) as $day) {
        $days = AddDay($days, $day, $data, $weekbegins, $weekends);
    }
} else {
    foreach(new DatePeriod($start, $oneday, $end->add($oneday)) as $day) {
        $days = AddDay($days, $day, $data, $weekbegins, $weekends);
    }
}
return $days;
}

//Used within DaysBetween() to add weekdays to $days
function AddDay($days, $day, $data, $weekbegins, $weekends) {
        $day_num = $day->format("N"); /* 'N' number days 1 (mon) to 7 (sun) */
        if ($weekends > $weekbegins && $day_num >= $weekbegins) {
            if($day_num < $weekends) { /* weekday */
                array_push($days,$data);
            }
        } else {
            if($day_num > $weekends && $day_num <= $weekbegins) { /* weekday */
                array_push($days,$data);
            }
        } 
        return $days;
}

//Determines the number of hours between two DateTimes
function DiffHours($daybegins , $dayends , $daylength , $start, $end, $days) {

$startstring = $start->format('Y-m-d H:i:s');
$startend1 = $start->format('Y-m-d') . ' ' . $dayends . ':00:00';
$ds = sizeof($days) - 1;
$endstring = $end->format('Y-m-d H:i:s');
$endend1 = $end->format('Y-m-d') . ' ' . $daybegins . ':00:00';
if ($start->format('Y-m-d') !== $end->format('Y-m-d')) {
    //for $start, determines hours between DateTime and end of day (Note: Initially, 6-18 workday is hardcoded)
    if ($start->format('H') >= $daybegins && $start->format('H') < $dayends) {
        $days[0] = round((strtotime($startend1) - strtotime($startstring))/60/60,2);
    } elseif ($start->format('H') < $daybegins) {
        $days[0] = $daylength;
    } elseif ($start->format('H') >= $dayends) {
        $days[0] = '00';
    }

    //for $end, determines hours between DateTime and end of day (Note: Initially, 6-18 workday is hardcoded)
    if ($end->format('H') >= $daybegins && $end->format('H') < $dayends) {
        $days[$ds] = round((strtotime($endstring) - strtotime($endend1))/60/60,2);
    } elseif ($end->format('H') < $daybegins) {
        $days[$ds] = $daylength;
    } elseif ($end->format('H') >= $dayends) {
        $days[$ds] = '00';
    }

} else {
    $days[0] = '00';
    $days[1] = round((strtotime($endstring)-strtotime($startstring))/60/60,2);
}

//computes hours between start and end
$dur1 = 0;
foreach ($days as $x) {
    $dur1 = $dur1 + $x;
}
return $dur1;
}

?>