<?php

Yii::import('application.components.images.*');

/**
 * Description of ImageUpload
 *
 * @author Dedi Suhanda
 * @email dedi.suhanda@gmail.com
 */
class ImageUpload extends CComponent {

    protected $_image;
    public $pathOfMark = '/images/';
    public $path;
    public $threshold = 100;

    /**
     *
     * @var String folder  
     */
    public $folder = "Y-m-d";

    /**
     * array(
     *   array('label' => '_L', 'width' => 610, "height" => 400),
     *   array('label' => '_M', 'width' => 300, "height" => 250),
     *   array('label' => '_T', 'width' => 120, "height" => 90),
     * );
     */
    public $labels;
    public $oLabel = '_O';
    public $width = 900;
    public $height = 900;

    public function init() {

        //   $this->image = new ImageClass();
    }

    /**
     * change destination directori to date path
     * 
     * @param String dest 
     * @param bool mkdir
     */
    public function initDestination($dest, $mkdir = true) {
//        $dest = $dest . '/' . date($this->folder);
//        if (!file_exists($dest) && $mkdir) {
//            mkdir($dest);
//        }
        return $dest . '/';
    }

    /**
     * start to resize and crop image
     *
     * @param type $filename
     * @param type $destination
     * @return boolean 
     */
    public function save($filename, $destination) {
        if (!file_exists($filename))
            return false;
        list($this->width, $this->height) = getimagesize($filename);
        $destination = $this->saveOriginal($filename, $destination);
        foreach ($this->labels as $label) {
            $dest = str_replace($this->oLabel, $label['label'], $destination);
            $this->image = new ImageClass();
            $this->image->source($filename);
            if (!isset($label['zoom'])) {
                $label['zoom'] = true;
            }
            if ($label['zoom']) {
                $this->image->crop($label['width'], $label['height'], $this->width, $this->height);
            } else{
                $this->image->resize($label['width'], $label['height']);
            }
            if (isset($label['watermark'])) {
                $this->image->source($dest);
                $mark = Yii::getPathOfAlias('webroot') . $this->pathOfMark . $label['watermark'];
                $this->image->watermark($mark, 60, $label['mark_x'], $label['mark_y']);
            }
            
            if (isset($label['quality']))
                $this->image->create($dest, $label['quality']);
            else
                $this->image->create($dest);
        }
        @unlink($filename);
        return $destination;
    }

    public function saveOriginal($filename, $destination) {
        $destination = $this->rename($destination, $this->oLabel);
        $this->image = new ImageClass();
        $this->image->source($filename);
        $this->image->resize($this->width, $this->height);
        $this->image->create($destination);

        return $destination;
    }

    public function calculateWHXY($label) {

        $width = $label['width'];
        $height = $label['height'];
        if ($width > $height) {
            while ($this->width >= $width + $label['width'] / $this->threshold) {
                $width = $width + $label['width'] / $this->threshold;
            }
            $height = ($label['height'] * $width) / $label['width'];
//            if ($this->height - $height < 0) {
//                $oldh = $height;
//                $height = $this->height;
//                $width = ($width * $oldh) / $height;
//            }
        } else {
            while ($this->height >= $height + $label['height'] / $this->threshold) {
                $height = $height + $label['height'] / $this->threshold;
            }
            $width = ($label['width'] * $height) / $label['height'];
//            if ($this->width - $width < 0) {
//                $oldw = $width;
//                $width = $this->width;
//                $height = ($height * $oldw) / $width;
//            }
        }
        $x = ($this->width - $width) / 2;
        $y = ($this->height - $height) / 2;
        return array($width, $height, $x, $y);
    }

    public function rename($filename, $label) {
        $ext = substr($filename,-3);
        $filename = substr($filename,0,-4);
        return ($filename . $label. ".". $ext);
    }

    public function getImage() {
        return $this->_image;
    }

    public function setImage($val) {
        $this->_image = $val;
    }

}

?>
