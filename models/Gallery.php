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

    private static $countFileItem = 0;

    private static $countFileItemLimit = 0;

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
                    $templateGallery .= Html::button('<i class="fa fa-trash"></i>', ['class' => 'btn btn-white', 'onclick' => 'syaremoveImage(this);']);
                $templateGallery .= Html::endTag('td');
            $templateGallery .= Html::endTag('tr');
        }
        
        return $templateGallery;
    }

    /**
     * Function Gen template gallery by path upload
     * @param array $galleries Array item image
     * @return string
     */
    public static function generateGalleryTemplateByPath($galleries){
        $templateGallery = '';
        foreach ($galleries as $galleryId => $gallery) {
            // Gia tri mac dinh cua image
            $urlImg = ArrayHelper::getValue($gallery, 'url');

            $templateGallery .= self::_generateGalleryTemplateByPath($urlImg, Yii::$app->getModule('gallery')->syaDirUpload . DIRECTORY_SEPARATOR . $urlImg);
        }

        return $templateGallery;
    }

    /**
     * Function gen template one image by path
     * @param $imgPath Path of image
     * @param $imgPathFull Path full of image
     * @return string
     */
    private static function _generateGalleryTemplateByPath($imgPath, $imgPathFull){
        $template = Html::beginTag('div', ['class' => 'col-md-3 col-lg-3 text-center']);

            // View image
            $template .= Html::beginTag('div', ['class' => 'letImgPreview', 'id' => $imgPath, 'onclick' => 'syainsertImagePath(this);']);
            $template .= Html::img('@web/' . $imgPathFull, ['style' => 'max-width: 100%;']);
            $template .= Html::endTag('div');
        $template .= Html::endTag('div');

        return $template;
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
     * Ham tao giao dien upload anh truc tiep = url
     * @return string
     */
    public static function generateInsertFromUrl($image){
        // Image preview
        $template = Html::beginTag('div', ['class' => 'col-sm-12', 'style' => 'margin-top: -55px;']);
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
     * @param int $page so trang
     * @param int $limit gioi han lay bao nhieu anh 1 lan
     * @param string $template hinh anh duoc lay ra tu thu muc upload
     * @return null|string
     */
    public static function getGalleryByPath($path = '', $page = 1, $limit = 12, $template = ''){
        // So file can lay
        $offset = $page * $limit;

        // Duong dan chua anh
        $rootPath = Yii::getAlias(Yii::$app->getModule('gallery')->syaDirPath);
        
        // Thu muc upload
        $dirPath = Yii::$app->getModule('gallery')->syaDirUpload;

        if (!file_exists($path))
            return null;

        // Get all file and Sort file DESC time
        $entrys = scandir($path, 1);

        foreach ($entrys as $k => $entry) {
            if (self::$countFileItem == $offset) {
                self::$countFileItemLimit = 0;
                break;
            }

            if (in_array($entry, ['.', '..', 'cache']))
                continue;

            $entryPath = $path . DIRECTORY_SEPARATOR . $entry;
            if (is_dir($entryPath)) {
                $template = self::getGalleryByPath($entryPath, $page, $limit, $template);
            } else {
                self::$countFileItem++;
                self::$countFileItemLimit++;

                if ($page !== 1 AND self::$countFileItemLimit + $limit <= $offset){
                    continue;
                }

                if (in_array(end(explode('.', $entry)), self::$fileType)) {
                    $imgPath = str_replace($rootPath . $dirPath . DIRECTORY_SEPARATOR, '', $entryPath);

                    // Generate img by path gallery library
                    $template .= self::_generateGalleryTemplateByPath($imgPath, $dirPath . DIRECTORY_SEPARATOR . $imgPath);
                }
            }
        }
        
        return $template;
    }
}
