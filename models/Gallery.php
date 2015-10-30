<?php

namespace sya\gallery\models;

use Yii;
use yii\bootstrap\Html;
use sya\gallery\helpers\FileHelper;
use yii\helpers\ArrayHelper;

class Gallery extends \yii\mongodb\ActiveRecord
{
    public $moduleName = 'gallery';
    
    private static $fileType = [
        'jpg',
        'gif',
        'png'
    ];
    
    /**
     * Ham tao giao dien cho image
     * @param array $galleries mang cac gia tri cua image
     * @param string $module ten module dang su dung
     * @param array $columns danh sach truong cua image
     * @return string
     */
    public static function generateGalleryTemplate($galleries, $module, $columns = []){
        $templateGallery = '';
        foreach ($galleries as $galleryId => $gallery) {
            // Gia tri mac dinh cua image
            $urlImg = ArrayHelper::getValue($gallery, 'url');
            $type = ArrayHelper::getValue($gallery, 'type');

            // Tao giao dien cho cac image
            $templateGallery .= Html::beginTag('tr', ['id' => 'imageItem', 'style' => 'width: 100%; background: white;']);
                $templateGallery .= Html::beginTag('td');
                    $templateGallery .= self::generateImageByType($urlImg, $type);
                $templateGallery .= Html::endTag('td');
                $templateGallery .= Html::beginTag('td', ['style' => 'vertical-align: middle; text-align: center;']);
                    $templateGallery .= Html::textInput($module . '[gallery][' . $galleryId . '][url]', $urlImg, ['class' => 'form-control', 'readonly' => true]);
                $templateGallery .= Html::endTag('td');

                // Type upload hidden
                $templateGallery .= Html::hiddenInput($module . '[gallery][' . $galleryId . '][type]', $type, ['class' => 'form-control']);

                // Lay ra cac truong cua image
                foreach ($columns as $keyColumn => $column) {
                    $templateGallery .= Html::beginTag('td', ['style' => 'vertical-align: middle; text-align: center;']);
                        $templateGallery .= self::generateColumnByType($keyColumn, $column, $gallery, $module, $galleryId);
                    $templateGallery .= Html::endTag('td');
                }

                // Cac action xu ly cua image
                $templateGallery .= Html::beginTag('td', ['style' => 'vertical-align: middle; text-align: center;']);
                    $templateGallery .= Html::button('<i class="fa fa-trash"></i>', ['class' => 'btn btn-white', 'onclick' => 'removeImage(this);']);
                $templateGallery .= Html::endTag('td');
            $templateGallery .= Html::endTag('tr');
        }
        
        return $templateGallery;
    }
    
    /**
     * Ham lay ra anh theo kieu upload
     * @param string $urlImg duong dan anh
     * @param string $type kieu upload anh
     * @return string
     */
    private static function generateImageByType($urlImg, $type){
        $options = ['style' => 'width: 100px; height: 100px;'];
        
        $template = null;
        switch ($type) {
            case \sya\gallery\Gallery::TYPE_URL:
                $template = Html::img($urlImg, $options);
                break;
            default:
                $template = Html::img(FileHelper::getFileUploaded($urlImg), $options);
                break;
        }
        
        return $template;
    }
    
    /**
     * Ham tao giao dien cho column
     * @param string $keyColumn ten truong cua column
     * @param array $column mang setting cua column
     * @param array $gallery mang gia tri cua image
     * @param string $module ten module dang su dung
     * @param string $id id cua 1 anh
     * @return string
     */
    private static function generateColumnByType($keyColumn, $column, $gallery, $module, $id){
        $typeImage = ArrayHelper::getValue($column, 'displayType', 'text');
        $items = ArrayHelper::getValue($column, 'items', []);
        $options = ArrayHelper::getValue($column, 'options', ['class' => 'form-control']);
        
        $template = null;
        switch ($typeImage) {
            case 'dropdown':
                $template = Html::dropDownList($module . '[gallery][' . $id . '][' . $keyColumn . ']', ArrayHelper::getValue($gallery, $keyColumn), $items, $options);
                break;
            default:
                $template = Html::textInput($module . '[gallery][' . $id . '][' . $keyColumn . ']', ArrayHelper::getValue($gallery, $keyColumn), $options);
                break;
        }
        
        return $template;
    }
    
    /**
     * Ham tao giao dien upload anh
     * @return string
     */
    public static function generateInsertFromUrl($image){
        // Image preview
        $template = Html::beginTag('div', ['class' => 'col-sm-12']);
            $template .= Html::img($image, ['id' => 'embed_image_url']);
        $template .= Html::endTag('div');

        // Caption image
        $template .= Html::beginTag('div', ['class' => 'col-sm-12 embed_field']);
            $template .= Html::beginTag('label', ['class' => 'row']);
                $template .= Html::tag('span', 'Caption', ['class' => 'col-sm-12']);
                $template .= Html::textarea('', '', ['class' => 'form-control col-sm-12']);
            $template .= Html::endTag('label');
        $template .= Html::endTag('div');

        // Alt text image
        $template .= Html::beginTag('div', ['class' => 'col-sm-12 embed_field']);
            $template .= Html::beginTag('label', ['class' => 'row']);
                $template .= Html::tag('span', 'Alt text', ['class' => 'col-sm-12']);
                $template .= Html::input('text', '', '', ['class' => 'form-control col-sm-12']);
            $template .= Html::endTag('label');
        $template .= Html::endTag('div');
            
        return $template;
    }
    
    /**
     * Ham lay ra hinh anh trong website theo duong dan
     * @param string $path duong dan chua anh
     * @param string $template hinh anh duoc lay ra tu thu muc upload
     * @return string
     */
    private function getGalleryByPath($path, $template = ''){
        // Duong dan chua anh
        $rootPath = Yii::getAlias(Yii::$app->getModule('gallery')->syaDirPath);
        
        // Thu muc upload
        $dirPath = Yii::$app->getModule('gallery')->syaDirUpload;
        
        if (!file_exists($path))
            return null;
        
        $entrys = scandir($path);
        foreach ($entrys as $entry) {
            if (in_array($entry, ['.', '..', 'cache']))
                continue;
            
            $entryPath = $path . DIRECTORY_SEPARATOR . $entry;
            if (is_dir($entryPath)) {
                $template .= self::getGalleryByPath($entryPath, $template);
            } else {
                if (in_array(end(explode('.', $entry)), self::$fileType)) {
                    $imgPath = str_replace($rootPath . $dirPath . DIRECTORY_SEPARATOR, '', $entryPath);
                    $template .= Html::beginTag('div', ['class' => 'col-md-3 text-center']);
                        // input chua cac image duoc chon
                        $template .= Html::hiddenInput('let_galleries', '', ['id' => 'let_galleries','class' => 'col-md-3 text-center input_image', 'data-type' => 'path']);
                        
                        // View image
                        $template .= Html::beginTag('div', ['class' => 'letImgPreview', 'id' => $imgPath, 'onclick' => 'insertImagePath(this);']);
                            $template .= Html::img('@web/' . $dirPath . DIRECTORY_SEPARATOR . $imgPath, ['style' => 'max-width: 100%; height: 200px;']);
                        $template .= Html::endTag('div');
                    $template .= Html::endTag('div');
                }
            }
        }
        
        return $template;
    }
}
