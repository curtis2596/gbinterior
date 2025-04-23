<?php
namespace App\Helpers;

class ImageUtility
{

    public $src, $dest;

    public function __construct($src, $dest)
    {
        $this->src = $src;
        $this->dest = $dest;
    }

    function resize($w, $h, $crop = FALSE)
    {
        list($width, $height) = getimagesize($this->src);
        if (!$width || !$height)
        {
            return false;
        }

        $r = $width / $height;
        if ($crop)
        {
            if ($width > $height)
            {
                $width = ceil($width - ($width * abs($r - $w / $h)));
            }
            else
            {
                $height = ceil($height - ($height * abs($r - $w / $h)));
            }
            $newwidth = $w;
            $newheight = $h;
        }
        else
        {
            if (($w / $h) > $r)
            {
                $newwidth = $h * $r;
                $newheight = $h;
            }
            else
            {
                $newheight = $w / $r;
                $newwidth = $w;
            }
        }

        $type = $this->getType();

        switch ($type)
        {
            case IMAGETYPE_JPEG:
                $src_img = imagecreatefromjpeg($this->src);
                break;

            case IMAGETYPE_PNG:
                $src_img = imagecreatefrompng($this->src);
                break;

            case IMAGETYPE_GIF:
                $src_img = imagecreatefromgif($this->src);
                break;

            default :
                return false;
        }


        $dst_img = imagecreatetruecolor($newwidth, $newheight);

        imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

        switch ($type)
        {
            case IMAGETYPE_JPEG:
                imagejpeg($dst_img, $this->dest, 100);
                break;

            case IMAGETYPE_PNG:
                imagepng($dst_img, $this->dest, 0);
                break;

            case IMAGETYPE_GIF:
                imagegif($dst_img, $this->dest);
                break;
        }

        return true;
    }

    public function getType($img = "")
    {
        $img = $img ? $img : $this->src;

        return exif_imagetype($img);
    }

    public function correctOrientation()
    {
        if ($this->getType() != IMAGETYPE_JPEG)
        {
            return;
        }

        if (function_exists('exif_read_data'))
        {
            $exif = exif_read_data($this->src);
            if ($exif && isset($exif['Orientation']))
            {
                $orientation = $exif['Orientation'];
                if ($orientation != 1) // if there is some rotation necessary
                {
                    $img = imagecreatefromjpeg($this->src);
                    $deg = 0;
                    switch ($orientation)
                    {
                        case 3:
                            $deg = 180;
                            break;
                        case 6:
                            $deg = 270;
                            break;
                        case 8:
                            $deg = 90;
                            break;
                    }
                    
                    if ($deg)
                    {
                        $img = imagerotate($img, $deg, 0);
                    }
                    
                    // then rewrite the rotated image back to the disk as $filename 
                    imagejpeg($img, $this->dest, 95);
                }
            }
        }
    }

}
