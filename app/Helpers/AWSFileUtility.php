<?php
namespace App\Helpers;

class AWSFileUtility
{
    public static $s3, $config;
    public $filename, $extension, $file, $path;

    public static function init($settingModel)
    {
        self::$config = array();
        self::$config["key"] = $settingModel->getValueFromName("aws_s3_key");
        self::$config["secret"] = $settingModel->getValueFromName("aws_s3_secret");
        self::$config["region"] = $settingModel->getValueFromName("aws_s3_region");
        self::$config["bucket"] = $settingModel->getValueFromName("aws_s3_bucket");
        self::$config["version"] = $settingModel->getValueFromName("aws_s3_version");
    }
    
    public static function validateConfig()
    {
        if (!FileUtility::use_s3)
        {
            return;
        }
        
        if (!isset(self::$config["key"]) || !self::$config["key"])
        {
            throw new Exception("Setting : AWS S3 key not set yet");
        }

        if (!isset(self::$config["secret"]) || !self::$config["secret"])
        {
            throw new Exception("Setting : AWS S3 secret not set yet");
        }

        if (!isset(self::$config["bucket"]) || !self::$config["bucket"])
        {
            throw new Exception("Setting : AWS S3 bucket not set yet");
        }

        if (!isset(self::$config["region"]) || !self::$config["region"])
        {
            throw new Exception("Setting : AWS S3 region not set yet");
        }

        if (!isset(self::$config["version"]) || !self::$config["version"])
        {
            throw new Exception("Setting : AWS S3 version not set yet");
        }
    }

    public function __construct()
    {
        if (!self::$s3)
        {
            self::validateConfig();
            
            $credentials = new Aws\Credentials\Credentials(self::$config["key"], self::$config["secret"]);

            $options = [
                'region' => self::$config["region"],
                'version' => self::$config["version"],
                'credentials' => $credentials,
                'http' => [
                    'verify' => false
                ],
            ];

            self::$s3 = new Aws\S3\S3Client($options);
        }
    }

    public static function getBaseURL()
    {
        if ( !isset(self::$config["bucket"]) || empty(self::$config["bucket"]) )
        {
            throw new Exception("AWS config bucket not found");
        }
        
        if ( !isset(self::$config["region"]) || empty(self::$config["region"]) )
        {
            throw new Exception("AWS config region not found");
        }
        
        return "https://" . self::$config["bucket"] . ".s3." . self::$config["region"] . ".amazonaws.com/";
    }

    public function move($src, $dest_path, $filename = "")
    {
        try
        {
            $dest_path = static::removePathSlashs($dest_path);
            $dest_path .= "/";

            $this->path = $dest_path;
            $temp = pathinfo($src);

            $this->filename = static::cleanFileName($temp['filename']);
            $this->extension = $temp['extension'];
            
            if (strlen($this->filename) > 100)
            {
                $this->filename = substr($this->filename, 0, 100);
            }

            if ($filename)
            {
                $this->filename = static::cleanFileName($filename);
                if (strlen($this->filename) > 100)
                {
                    $this->filename = substr($this->filename, 0, 100);
                }

                $this->file = $this->filename . "." . $this->extension;
            }
            else
            {
                if (strlen($this->filename) > 100)
                {
                    $this->filename = substr($this->filename, 0, 100);
                }

                $this->file = $this->getAutoincreamentFileName($this->filename, $this->extension, $this->path);
                $this->filename = pathinfo($this->file, PATHINFO_FILENAME);
            }
            
            if ( !file_exists($src) )
            {
                throw new Exception("File Not Found : $src");
            }

            self::$s3->putObject(array(
                'Bucket' => self::$config["bucket"],
                'Key' => $this->path . $this->file,
                'SourceFile' => $src,
                'ACL' => 'public-read'
            ));
            
            gc_collect_cycles();
        }
        catch (Aws\S3\Exception\S3Exception $e)
        {
            //die($e->getMessage());
            return false;
        }

        return true;
    }

    public static function get($file)
    {
        return self::getBaseURL() . $file;
    }

    public function read($file)
    {
        try
        {
            if ($this->isExist($file))
            {
                $result = self::$s3->getObject(array(
                    'Bucket' => self::$config["bucket"],
                    'Key' => $file
                ));
                
                return $result;
            }
        }
        catch (Aws\S3\Exception\S3Exception $e)
        {
            die($e->getMessage());
        }
        
        return false;
    }

    public function delete($file)
    {
        try
        {
            if ($this->isExist($file))
            {
                self::$s3->deleteObject([
                    'Bucket' => self::$config["bucket"],
                    'Key' => $file
                ]);
                
                CakeLog::write('file', "S3 Delete File : " . $file);
            }
        }
        catch (Aws\S3\Exception\S3Exception $e)
        {
            //die($e->getMessage());
            return false;
        }

        return true;
    }

    public function isExist($file)
    {
        return self::$s3->doesObjectExist(self::$config["bucket"], $file);
    }

    /**
     * return filename which which will be save 
     * @param string $filename
     * @param string $ext
     * @param string $dest_path
     * @return string
     */
    public function getAutoincreamentFileName($filename, $ext, $dest_path, $sep = "_", $i = 0)
    {
        $temp_name = $i > 0 ? $filename . $sep . $i : $filename;

        if ($this->isExist($dest_path . $temp_name . "." . $ext))
        {
            return $this->getAutoincreamentFileName($filename, $ext, $dest_path, $sep, $i + 1);
        }
        else
        {
            return $temp_name . "." . $ext;
        }
    }

    

    public static function removePathSlashs($path, $side = '')
    {
        $side = strtoupper($side);
        $path = trim(str_replace('\\', '/', $path));

        if ($side == 'FIRST' || $side == 'START' || empty($side))
        {
            if (substr($path, 0, 1) == "/")
            {
                $path = substr($path, 1, strlen($path));
            }
        }

        if ($side == 'LAST' || $side == 'END' || empty($side))
        {
            if (substr($path, -1) == "/")
            {
                $path = substr($path, 0, strrpos($path, "/"));
            }
        }
        return $path;
    }

    public static function cleanFileName($string)
    {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

        return preg_replace('/[^A-Za-z0-9\-_]/', '', $string); // Removes special chars.
    }

}
