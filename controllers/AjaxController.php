<?php

namespace sya\gallery\controllers;

use sya\gallery\Gallery;
use Yii;
use sya\gallery\helpers\FileHelper;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;
use yii\helpers\Json;
use sya\gallery\models\Gallery as GalleryModel;

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

        $attribute = Yii::$app->request->post('attribute');

        // Title
        $title = Yii::$app->request->post('title');

        // Caption
        $caption = Yii::$app->request->post('caption');

        // Alt text
        $alt_text = Yii::$app->request->post('alt_text');

        // Cac truong cua image
        $columns = Json::decode(Yii::$app->request->post('columns'));
        
        // danh sach cac anh duoc upload
        $gallery = [];

        // Column defalt image
        $columnsDefault = [
            'title' => $title,
            'caption' => $caption,
            'alt_text' => $alt_text
        ];

        // Id cua gallery
        $id = uniqid('g_');
        
        // Begin upload image
        if ($type == Gallery::TYPE_UPLOAD){// Upload anh khi chon type la upload
            $images = UploadedFile::getInstancesByName('image');
            if (empty($images))
                return;

            foreach ($images as $image) {
                // Tao lai id khi upload nhieu anh
                $id = uniqid('g_');

                $ext = FileHelper::getExtention($image);
                if (!empty($ext)) {
                    $fileDir = strtolower($module) . '/' . date('Y/m/d/');
                    $fileName = pathinfo($image, PATHINFO_BASENAME);
                    $folder = Yii::getAlias(Yii::$app->getModule('gallery')->syaDirPath) . Yii::$app->getModule('gallery')->syaDirUpload . '/' . $fileDir;
                    FileHelper::createDirectory($folder);
                    $image->saveAs($folder . $fileName);

                    $gallery[$id] = ArrayHelper::merge([
                        'url' => $fileDir . $fileName,
                        'type' => $type
                    ], $columnsDefault);
                }
            }
            
            $template = GalleryModel::generateGalleryTemplateByPath($gallery);
        } elseif ($type == Gallery::TYPE_URL) {// Lay ra duong dan anh khi type la url
            $image = Yii::$app->request->post('image');

            if (empty($image))
                return;

            $gallery[$id] = ArrayHelper::merge([
                'url' => $image,
                'type' => $type
            ], $columnsDefault);

            $template = GalleryModel::generateGalleryTemplate($gallery, $module, $columns);
        } elseif ($type == Gallery::TYPE_PATH) {
            $image = Yii::$app->request->post('image');

            if (empty($image))
                return;

            $images = explode(',', $image);

            if (!empty($image) && is_array($images)) {
                foreach ($images as $img){
                    $id = uniqid('g_');
                    $gallery[$id] = ArrayHelper::merge([
                        'url' => $img,
                        'type' => $type
                    ], $columnsDefault);
                }
            }

            $template = GalleryModel::generateGalleryTemplate($gallery, $module, $attribute, $columns);
        }
        // End upload image
        
        echo $template;
    }

    public function actionGetimagepreview() {
        // Image
        $image = Yii::$app->request->post('image');

        if (empty($image))
            return;

        echo GalleryModel::generateInsertFromUrl($image);
    }

    public function actionGetinfoimage() {
        // Image
        $image = Yii::$app->request->post('image');

        if (empty($image))
            return;

        echo GalleryModel::generatePreviewImage($image);
    }

    /**
     * Action tao giao dien upload
     */
    public function actionGetgallerypath(){
        $path = Yii::getAlias(Yii::$app->getModule('gallery')->syaDirPath) . Yii::$app->getModule('gallery')->syaDirUpload;

        $page = Yii::$app->request->post('page', 1);

        echo GalleryModel::getGalleryByPath($path, $page);
    }
}