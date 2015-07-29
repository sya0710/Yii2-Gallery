<?php

namespace sya\gallery\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use sya\gallery\helpers\FileHelper;

class AjaxController extends \yii\web\Controller{
    
    /**
     * Action upload anh
     */
    public function actionAdditemimage(){
        // Cac thong so mac dinh cua image
        // Kieu upload
        $type = Yii::$app->request->post('type');
        
        // Module upload
        $module = Yii::$app->request->post('module');
        
        // Cac truong cua image
        $columns = \yii\helpers\Json::decode(Yii::$app->request->post('columns'));
        
        // danh sach cac anh duoc upload
        $gallery = [];
        
        // Id cua gallery
        $id = uniqid('g_');
        
        // Begin upload image
        if ($type == 'upload'){// Upload anh khi chon type la upload
            $image = \yii\web\UploadedFile::getInstanceByName('image');
            if (!empty($image)) {
                $ext = FileHelper::getExtention($image);
                if (!empty($ext)) {
                    $fileDir = strtolower($module) . '/' . date('Y/m/d/');
                    $fileName = uniqid() . '.' . $ext;
                    $folder = Yii::getAlias(Yii::$app->getModule('gallery')->syaDirPath) . Yii::$app->getModule('gallery')->syaDirUpload . '/' . $fileDir;
                    FileHelper::createDirectory($folder);
                    $image->saveAs($folder . $fileName);
                    $image = $fileDir . $fileName;
                }
            }
            
            $gallery[$id] = [
                'url' => $image, 
                'type' => $type
            ];
        } elseif ($type == 'url') {// Lay ra duong dan anh khi type la url
            $image = Yii::$app->request->post('image');
            
            $gallery[$id] = [
                'url' => $image, 
                'type' => $type
            ];
        } elseif ($type == 'path') {
            $image = Yii::$app->request->post('image');
            $images = explode(',', $image);
            
            if (!empty($image) && is_array($images)) {
                foreach ($images as $img){
                    $id = uniqid('g_');
                    $gallery[$id] = [
                        'url' => $img,
                        'type' => $type
                    ];
                }
            }
        }
        // End upload image
        
        echo \sya\gallery\models\Gallery::generateGalleryTemplate($gallery, $module, $columns);
    }
    
    /**
     * Action tao giao dien upload
     */
    public function actionGetinputupload(){
        // Kieu upload
        $type = Yii::$app->request->post('type');
        
        echo \sya\gallery\models\Gallery::getInputImageByType($type);
    }
}