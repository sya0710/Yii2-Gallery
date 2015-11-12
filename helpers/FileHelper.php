<?php
namespace sya\gallery\helpers;

use Yii;
use yii\helpers\ArrayHelper;

class FileHelper extends \yii\helpers\FileHelper {
    
    const URL = 'url';
    
    const PATH = 'path';
    
    const IMAGE = 'image';

    public static function getExtention($file) {
        if (($ext = pathinfo($file, PATHINFO_EXTENSION)) !== '')
            return strtolower($ext);
        return FALSE;
    }

    public static function getInfomation($file){
        $info = pathinfo($file);
        $info['filesize'] = self::customFileSize(filesize($file));
        $info['fileatime'] = filemtime($file);
        $info['width'] = ArrayHelper::getValue(getimagesize($file), 0);
        $info['height'] = ArrayHelper::getValue(getimagesize($file), 1);

        return $info;
    }

    public static function customFileSize($bytes, $decimals = 2) {
        $size = ['B','KB','MB','GB','TB','PB','EB','ZB','YB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . @$size[$factor];
    }
    
    /**
     * Get url or path file uploaded
     * @param type $file
     * @param type $type URL | PATH
     * @return type
     */
    public static function getFileUploaded ($file, $type = self::URL) {
        if ($type == self::PATH)
            return Yii::getAlias(Yii::$app->getModule('gallery')->syaDirPath) . Yii::$app->getModule('gallery')->syaDirUpload . DIRECTORY_SEPARATOR . $file;
        elseif ($type == self::URL) {
            return Yii::getAlias(Yii::$app->getModule('gallery')->syaDirUrl) . Yii::$app->getModule('gallery')->syaDirUpload . DIRECTORY_SEPARATOR . $file;
        }
        return FALSE;
    }
}
