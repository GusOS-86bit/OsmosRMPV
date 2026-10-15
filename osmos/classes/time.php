<?php
class Time {
    static function get_time($pasttime, $today = 0, $differenceFormat = '%y') {
        if (empty($pasttime)) {
            return "";
        }

        $today = date("Y-m-d H:i:s");
        $datetime1 = date_create($pasttime);
        $datetime2 = date_create($today);

        if (!$datetime1 || !$datetime2) {
            return "";
        }

        $interval = date_diff($datetime1, $datetime2);

        // php and mysql clocks can be a bit off
        if ($interval->invert) {
            return "a few seconds ago";
        }

        $answerY = $interval->format('%y');
        $answerM = $interval->format('%m');
        $answer = $interval->format('%d');
        $answer2 = $interval->format('%h');

        if ($answerY >= 1) {
            return date("F jS, Y", strtotime($pasttime));
        } else if ($answerM >= 1) {
            return date("F jS, Y", strtotime($pasttime));
        } else if ($answer > 2) {
            return date("F jS, Y", strtotime($pasttime));
        } else if ($answer == 2) {
            return $answer . " d, " . $answer2 . " hr ago";
        } else if ($answer == 1) {
            return "1 d, " . date("h:i:s a", strtotime($pasttime));
        } else {
            $answer = $interval->format('%h');
            $answer2 = $interval->format('%i');

            if (($answer < 24) && ($answer > 1)) {
                return $answer . " hr, " . $answer2 . " min ago";
            } else if ($answer == 1){
                return "an hour ago";
            } else {
                $answer = $interval->format('%i');
                if (($answer < 60) && ($answer > 1)) {
                    return $answer . " minutes ago";
                } else if ($answer == 1) {
                    return "a minute ago";
                } else {
                    $answer = $interval->format('%s');
                    if ($answer >= 10) {
                        return $answer . " seconds ago";
                    } else {
                        return "a few seconds ago";
                    }
                }
            }
        }
    }
}
