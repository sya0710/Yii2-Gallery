<?php

namespace sya\gallery\controllers;

use Yii;
use sya\gallery\helpers\FileHelper;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;
use yii\helpers\Json;

class AjaxController extends \yii\web\Controller{
    
    /**
     * Action upload anh
     */
    public function actionAdditemimage(){
        // Cac thong so mac dinh cua image
        // Kieu upload
        $type = Yii::$app->request->post('type');
        
        // Widget call function build image
        $widget_class = Yii::$app->request->post('widget_class', '/sya/gallery/Gallery');
        $widget_class = $this->rewriteWidgetClass($widget_class);

        // Module upload
        $module = Yii::$app->request->post('module');

        // Attribute name
        $attribute = Yii::$app->request->post('attribute');

        // Cac truong cua image
        $columns = Json::decode(Yii::$app->request->post('columns'));

        $templateInfomationImage = Yii::$app->request->post('templateInfomationImage');

        $templateInfomationImageDetail = Yii::$app->request->post('templateInfomationImageDetail');

        // danh sach cac anh duoc upload
        $gallery = [];

        // Column defalt image
        $columnsDefault = [
            'title' => '',
            'caption' => '',
            'alt_text' => ''
        ];

        // Id cua gallery
        $id = uniqid('g_');

        // Begin upload image
        if ($type == $widget_class::TYPE_UPLOAD){// Upload anh khi chon type la upload
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

                    $columnsDefault['title'] = reset(explode('.', $fileName));

                    $gallery[$id] = ArrayHelper::merge([
                        'url' => $fileDir . $fileName,
                        'type' => $type
                    ], $columnsDefault);
                }
            }
            
            $template = $widget_class::generateGalleryTemplateByPath($gallery);
        } elseif ($type == $widget_class::TYPE_URL) {// Lay ra duong dan anh khi type la url
            $image = Yii::$app->request->post('image');

            $columnsDefault = Json::decode($image);

            if (empty($image))
                return;

            $gallery[$id] = ArrayHelper::merge([
                'type' => $type
            ], $columnsDefault);

            $template = $widget_class::generateGalleryTemplate($gallery, $columns, $module, $attribute, $templateInfomationImage, $templateInfomationImageDetail);
        } elseif ($type == $widget_class::TYPE_PATH) {
            $image = Yii::$app->request->post('image');

            if (empty($image))
                return;

            $images = explode(';', $image);

            if (!empty($image) && is_array($images)) {
                foreach ($images as $img){
                    $columnsDefault = Json::decode($img);

                    $id = uniqid('g_');
                    $gallery[$id] = ArrayHelper::merge([
                        'type' => $type
                    ], $columnsDefault);
                }
            }

            $template = $widget_class::generateGalleryTemplate($gallery, $columns, $module, $attribute, $templateInfomationImage, $templateInfomationImageDetail);
        }
        // End upload image
        echo $template;
    }

    public function actionGetimagepreview() {
        // Image
        $image = Yii::$app->request->post('image');

        // Widget call function build image
        $widget_class = Yii::$app->request->post('widget_class', '/sya/gallery/Gallery');
        $widget_class = $this->rewriteWidgetClass($widget_class);

        if (empty($image))
            return;

        echo $widget_class::generateInsertFromUrl($image);
    }

    public function actionGetinfoimage() {
        // Image
        $image = Yii::$app->request->post('image');

        // Widget call function build image
        $widget_class = Yii::$app->request->post('widget_class', '/sya/gallery/Gallery');
        $widget_class = $this->rewriteWidgetClass($widget_class);

        if (empty($image))
            return;

        echo $widget_class::generatePreviewImage($image);
    }

    /**
     * Action tao giao dien upload
     */
    public function actionGetgallerypath(){
        $path = Yii::getAlias(Yii::$app->getModule('gallery')->syaDirPath) . Yii::$app->getModule('gallery')->syaDirUpload;

        $page = Yii::$app->request->post('page', 1);

        // Widget call function build image
        $widget_class = Yii::$app->request->post('widget_class', '/sya/gallery/Gallery');
        $widget_class = $this->rewriteWidgetClass($widget_class);

        echo $widget_class::getGalleryByPath($path, $page);
    }

    function rewriteWidgetClass($widget_class) {
        return str_replace('/', '\\', $widget_class);
    }
}