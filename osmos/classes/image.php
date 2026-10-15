<?php

class Image
{

    public function generate_filename($length)
    {
        $array = array(0,1,2,3,4,5,6,7,8,9,'a', 'A',
                                            'b', 'B',
                                            'c', 'C',
                                            'd', 'D',
                                            'e', 'E',
                                            'f', 'F',
                                            'g', 'G',
                                            'h', 'H',
                                            'i', 'I',
                                            'j', 'J',
                                            'k', 'K',
                                            'l', 'L',
                                            'm', 'M',
                                            'n', 'N',
                                            'o', 'O',
                                            'p', 'P',
                                            'q', 'Q',
                                            'r', 'R',
                                            's', 'S',
                                            't', 'T',
                                            'u', 'U',
                                            'v', 'V',
                                            'w', 'W',
                                            'x', 'X',
                                            'y', 'Y',
                                            'z', 'Z');
        $text = "";

        for($x= 0; $x < $length ; $x++)
            {
                $random = random_int(0, 61);
                $text .= $array[$random];
            }
        return $text;
    }

    // jpeg/png/gif only, returns false for videos or broken files instead of crashing
    private function open_image($file_name)
    {
        if (!is_string($file_name) || $file_name === "" || !is_file($file_name)) {
            return false;
        }

        $info = @getimagesize($file_name);
        if (!$info) {
            return false;
        }

        switch ($info[2]) {
            case IMAGETYPE_JPEG: $image = @imagecreatefromjpeg($file_name); break;
            case IMAGETYPE_PNG:  $image = @imagecreatefrompng($file_name); break;
            case IMAGETYPE_GIF:  $image = @imagecreatefromgif($file_name); break;
            default: $image = false;
        }

        return $image ? $image : false;
    }

    // white background, otherwise transparent pngs turn black
    private function new_canvas($width, $height)
    {
        $canvas = imagecreatetruecolor(max(1, (int)$width), max(1, (int)$height));
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        return $canvas;
    }

    public function crop_image($original_file_name, $cropped_file_name, $max_width, $max_height)
    {
        $original_image = $this->open_image($original_file_name);
        if (!$original_image) {
            return false;
        }

        $original_width = imagesx($original_image);
        $original_height = imagesy($original_image);

        $original_aspect = $original_width / $original_height;
        $max_aspect = $max_width / $max_height;

        if ($original_aspect >= $max_aspect) {
            $new_height = $max_height;
            $new_width = $original_width * ($max_height / $original_height);
        } else {
            $new_width = $max_width;
            $new_height = $original_height * ($max_width / $original_width);
        }

        $new_image = $this->new_canvas($new_width, $new_height);

        imagecopyresampled($new_image, $original_image, 0, 0, 0, 0, (int)$new_width, (int)$new_height, $original_width, $original_height);

        imagedestroy($original_image);

        $x = (int)round(($new_width - $max_width) / 2);
        $y = (int)round(($new_height - $max_height) / 2);

        $new_cropped_image = $this->new_canvas($max_width, $max_height);

        imagecopyresampled($new_cropped_image, $new_image, 0, 0, $x, $y, $max_width, $max_height, $max_width, $max_height);

        imagedestroy($new_image);

        imagejpeg($new_cropped_image, $cropped_file_name, 90);

        imagedestroy($new_cropped_image);

        return true;
    }

    // fit inside max_width x max_height, never makes it bigger
    public function resize_image($original_file_name, $resized_file_name, $max_width, $max_height)
    {
        $original_image = $this->open_image($original_file_name);
        if (!$original_image) {
            return false;
        }

        $original_width = imagesx($original_image);
        $original_height = imagesy($original_image);

        if ($original_width <= $max_width && $original_height <= $max_height) {
            imagedestroy($original_image);
            if ($resized_file_name != $original_file_name) {
                copy($original_file_name, $resized_file_name);
            }
            return true;
        }

        $scale = min($max_width / $original_width, $max_height / $original_height);
        $new_width = (int)round($original_width * $scale);
        $new_height = (int)round($original_height * $scale);

        $new_image = $this->new_canvas($new_width, $new_height);

        imagecopyresampled($new_image, $original_image, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);

        imagedestroy($original_image);

        imagejpeg($new_image, $resized_file_name, 90);

        imagedestroy($new_image);

        return true;
    }

    public function get_thumb_cover($filename)
    {
        $thumbnail = $filename . "_cover_thumb_.jpg";
        if(file_exists($thumbnail))
            {
                return $thumbnail;
            }
        $this->crop_image($filename, $thumbnail, 1366, 488);

        if(file_exists($thumbnail))
            {
                return $thumbnail;
            }else
            {
                return $filename;
            }
    }

    public function get_thumb_profile($filename)
    {
        $thumbnail = $filename . "_profile_thumb.jpg";
        if(file_exists($thumbnail))
            {
                return $thumbnail;
            }
        $this->crop_image($filename, $thumbnail, 600, 600);

        if(file_exists($thumbnail))
            {
                return $thumbnail;
            }else
            {
                return $filename;
            }
    }

    public function get_thumb_post($filename)
    {
        $thumbnail = $filename . "_post_thumb_.jpg";
        if(file_exists($thumbnail))
            {
                return $thumbnail;
            }
        $this->crop_image($filename, $thumbnail, 600, 600);

        if(file_exists($thumbnail))
            {
                return $thumbnail;
            }else
            {
                return $filename;
            }
    }

}
