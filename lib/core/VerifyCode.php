<?php
namespace lib\core;

class VerifyCode {
    private $width;
    private $height;
    private $length;
    private $snow;
    private $line;
    private $font=[];
    private $chars = 'abcdefghkmnprstuvwxyzABCDEFGHKMNPRSTUVWXYZ23456789';
    private $img;
    private $code;

    /**
     * VerifyCode constructor.
     * @param int $width
     * @param int $height
     * @param int $length
     * @param int $snow
     * @param int $line
     */
    public function __construct($width, $height, $length=4, $snow=10, $line=10){
        $this->width = $width;
        $this->height = $height;
        $this->length = $length;
        $this->snow = $snow;
        $this->line = $line;
        $this->font = $this->getFont();
        $this->code = $this->setCode();
    }

    private function getFont(){
        $fold = APP_ROOT.'/lib/font/';
        $font = [];
        if($handle = opendir($fold)){
            while(false !== ($file = readdir($handle))){
                if( $file  !=  "."  and  $file  !=  ".." ){
                    $font[] = $fold.$file;
                }
            }
            closedir($handle);
        }
        return $font;
    }

    private function setCode(){
        $res = '';
        $len = strlen($this->chars) - 1;
        for($i = 0; $i < $this->length; $i++){
            $res .= $this->chars[mt_rand(0, $len)];
        }
        return $res;
    }

    public function getCode(){
        return strtolower($this->code);
    }

    private function createBg(){
        $this->img = imagecreatetruecolor($this->width, $this->height);
        $bgColor = imagecolorallocate($this->img, mt_rand(160, 255), mt_rand(160, 255), mt_rand(160, 255));
        imagefill($this->img, 0, 0, $bgColor);
    }

    private function drawDistrub(){
        if($this->line != 0){
            for($i = 0; $i < $this->line; $i++) {
                $color = imagecolorallocate($this->img, mt_rand(0, 200), mt_rand(0, 200), mt_rand(0, 200));
                imageline($this->img, mt_rand(1, $this->width), mt_rand(1, $this->height), mt_rand(1, $this->width), mt_rand(1, $this->height), $color);
            }
        }
        if($this->snow != 0){
            for($i = 0; $i < $this->snow; $i++) {
                $color = imagecolorallocate($this->img, mt_rand(0, 200), mt_rand(0, 200), mt_rand(0, 200));
                imagestring($this->img, mt_rand(1, 5), mt_rand(1, $this->width), mt_rand(1, $this->height), '*', $color);
            }
        }
    }

    private function writeCode(){
        for($i = 0; $i < $this->length; $i++){
            $fontSize = mt_rand(($this->width*0.8)/$this->length,($this->width*0.85)/$this->length);
            $angle = mt_rand(-30, 30);
            $x = mt_rand(1, 4) + $i * ($this->width / $this->length);
            $y = mt_rand($this->height*0.7 ,$this->height*0.8);

            $color = imagecolorallocate($this->img, mt_rand(20, 120), mt_rand(20, 120), mt_rand(20, 120));
            $font = array_rand($this->font);
            $str = substr($this->code, $i, 1);
            imagettftext($this->img, $fontSize, $angle, $x, $y, $color, $this->font[$font], $str);
        }
    }

    private function outPut(){
        header("content-type:image/gif");
        imagegif($this->img);
        imagedestroy($this->img);
    }

    public function doIt(){
        $this->createBg();
        $this->drawDistrub();
        $this->writeCode();
        $this->outPut();
    }
}