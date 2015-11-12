<?php

namespace sya\gallery\models;

use kartik\daterange\DateRangePicker;
use Yii;
use yii\bootstrap\Html;
use sya\gallery\helpers\FileHelper;
use yii\helpers\ArrayHelper;

class Gallery extends \yii\mongodb\ActiveRecord
{
    /**
     * Type column html
     */
    CONST SYA_TYPE_COLUMN_DROPDOWN = 'dropdown';

    CONST SYA_TYPE_COLUMN_TEXTAREA = 'textarea';

    CONST SYA_TYPE_COLUMN_RADIO = 'radio';

    CONST SYA_TYPE_COLUMN_RADIOLIST = 'radioList';

    CONST SYA_TYPE_COLUMN_CHECKBOX = 'checkbox';

    CONST SYA_TYPE_COLUMN_CHECKBOXLIST = 'checkboxList';

    CONST SYA_TYPE_COLUMN_HIDDEN = 'hidden';

    CONST SYA_TYPE_COLUMN_INPUT = 'text';

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
            $title = ArrayHelper::getValue($gallery, 'title');
            $caption = ArrayHelper::getValue($gallery, 'caption');
            $alt_text = ArrayHelper::getValue($gallery, 'alt_text');
            $options = ArrayHelper::getValue($gallery, 'options', []);

            $optionsDefault = ['class' => 'text-center', 'style' => 'vertical-align: middle;'];

            $options = ArrayHelper::merge($optionsDefault, $options);

            // Tao giao dien cho cac image
            $templateGallery .= Html::beginTag('tr', ['id' => 'imageItem', 'style' => 'width: 100%; background: white;']);
                $templateGallery .= Html::beginTag('td', ['class' => 'text-center', 'style' => 'vertical-align: middle;']);
                    $templateGallery .= self::generateImageByType($urlImg, $type);
                $templateGallery .= Html::endTag('td');

                // Type upload hidden
                $templateGallery .= Html::hiddenInput($module . '[gallery][' . $galleryId . '][type]', $type, ['class' => 'form-control']);

                // Infomation image
                $templateGallery .= Html::beginTag('td', ['class' => 'text-center', 'style' => 'vertical-align: middle;']);
                    // Url
                    $templateGallery .= Html::beginTag('div', ['class' => 'form-group field-gallery-url']);
                        $templateGallery .= Html::tag('label', 'Url', ['class' => 'control-label col-sm-3']);
                        $templateGallery .= Html::beginTag('div', ['class' => 'col-sm-9']);
                            $templateGallery .= Html::textInput($module . '[gallery][' . $galleryId . '][url]', $urlImg, ['class' => 'form-control', 'readonly' => true]);
                        $templateGallery .= Html::endTag('div');
                    $templateGallery .= Html::endTag('div');

                    // Title
                    $templateGallery .= Html::beginTag('div', ['class' => 'form-group field-gallery-url']);
                        $templateGallery .= Html::tag('label', 'Title', ['class' => 'control-label col-sm-3']);
                        $templateGallery .= Html::beginTag('div', ['class' => 'col-sm-9']);
                            $templateGallery .= Html::textInput($module . '[gallery][' . $galleryId . '][title]', $title, ['class' => 'form-control']);
                        $templateGallery .= Html::endTag('div');
                    $templateGallery .= Html::endTag('div');

                    // Caption
                    $templateGallery .= Html::beginTag('div', ['class' => 'form-group field-gallery-caption']);
                        $templateGallery .= Html::tag('label', 'Caption', ['class' => 'control-label col-sm-3']);
                        $templateGallery .= Html::beginTag('div', ['class' => 'col-sm-9']);
                            $templateGallery .= Html::textarea($module . '[gallery][' . $galleryId . '][caption]', $caption, ['class' => 'form-control']);
                        $templateGallery .= Html::endTag('div');
                    $templateGallery .= Html::endTag('div');

                    // Alt text
                    $templateGallery .= Html::beginTag('div', ['class' => 'form-group field-gallery-alt-text']);
                        $templateGallery .= Html::tag('label', 'Alt text', ['class' => 'control-label col-sm-3']);
                        $templateGallery .= Html::beginTag('div', ['class' => 'col-sm-9']);
                            $templateGallery .= Html::textInput($module . '[gallery][' . $galleryId . '][alt_text]', $alt_text, ['class' => 'form-control']);
                        $templateGallery .= Html::endTag('div');
                    $templateGallery .= Html::endTag('div');
                $templateGallery .= Html::endTag('td');

                // Lay ra cac truong cua image
                foreach ($columns as $keyColumn => $column) {
                    $templateGallery .= self::generateColumnByType($keyColumn, $column, $gallery, $module, $galleryId, $options);
                }

                // Cac action xu ly cua image
                $templateGallery .= Html::beginTag('td', ['class' => 'text-center', 'style' => 'vertical-align: middle;']);
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
            $template .= Html::icon('ok', ['class' => 'icon-active']);

            // View image
            $template .= Html::beginTag('div', ['class' => 'letImgPreview', 'id' => $imgPath, 'onclick' => 'syaPreviewImage(this);']);
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
     * @param string $tdOptions Html Attribute of td column
     * @return string
     */
    private static function generateColumnByType($keyColumn, $column, $gallery, $module, $id, $tdOptions = []){
        $typeImage = ArrayHelper::getValue($column, 'displayType', 'text');
        $items = ArrayHelper::getValue($column, 'items', []);
        $options = ArrayHelper::getValue($column, 'options', ['class' => 'form-control']);
        $column_name = $module . '[gallery][' . $id . '][' . $keyColumn . ']';

        switch ($typeImage) {
            case self::SYA_TYPE_COLUMN_DROPDOWN:
                $template = Html::dropDownList($column_name, ArrayHelper::getValue($gallery, $keyColumn), $items, $options);
                break;
            case self::SYA_TYPE_COLUMN_TEXTAREA:
                $template = Html::textarea($column_name, ArrayHelper::getValue($gallery, $keyColumn), $options);
                break;
            case self::SYA_TYPE_COLUMN_RADIO:
                $template = Html::radio($column_name, ArrayHelper::getValue($gallery, $keyColumn), $options);
                break;
            case self::SYA_TYPE_COLUMN_RADIOLIST:
                $template = Html::radioList($column_name, ArrayHelper::getValue($gallery, $keyColumn), $items, $options);
                break;
            case self::SYA_TYPE_COLUMN_CHECKBOX:
                $template = Html::checkbox($column_name, ArrayHelper::getValue($gallery, $keyColumn), $options);
                break;
            case self::SYA_TYPE_COLUMN_CHECKBOXLIST:
                $template = Html::checkboxList($column_name, ArrayHelper::getValue($gallery, $keyColumn), $items, $options);
                break;
            case self::SYA_TYPE_COLUMN_HIDDEN:
                $tdOptions = ArrayHelper::merge($tdOptions, ['style' => 'display: none;']);
                $template = Html::hiddenInput($column_name, ArrayHelper::getValue($gallery, $keyColumn), $options);
                break;
            default:
                $template = Html::textInput($column_name, ArrayHelper::getValue($gallery, $keyColumn), $options);
                break;
        }

        $templateGallery = Html::beginTag('td', $tdOptions);
            $templateGallery .= $template;
        $templateGallery .= Html::endTag('td');

        return $templateGallery;
    }

    /**
     * Ham tao giao dien upload anh truc tiep = url
     * @param $image Url image
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
        if (!is_dir($path)) {
            throw new InvalidParamException("The dir argument must be a directory: $path");
        }

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

    /**
     * Function generate preview infomation image
     * @param $image Url image
     * @return string
     */
    public static function generatePreviewImage($image){
        $imageUrl = Yii::getAlias('@web') . DIRECTORY_SEPARATOR . Yii::$app->getModule('gallery')->syaDirUpload . DIRECTORY_SEPARATOR . $image;
        $imagePath = Yii::getAlias(Yii::$app->getModule('gallery')->syaDirPath) . Yii::$app->getModule('gallery')->syaDirUpload . DIRECTORY_SEPARATOR . $image;

        $info = FileHelper::getInfomation($imagePath);
        $basename = ArrayHelper::getValue($info, 'basename');
        $filesize = ArrayHelper::getValue($info, 'filesize');
        $fileatime = ArrayHelper::getValue($info, 'fileatime');
        $filename = ArrayHelper::getValue($info, 'filename');
        $width = ArrayHelper::getValue($info, 'width');
        $height = ArrayHelper::getValue($info, 'height');

        $template = Html::tag('h3', Yii::t('gallery', 'ATTACHMENT DETAILS'), []);

        // Info img
        $template .= Html::beginTag('div', ['class' => 'sya_info_galllery row']);
            $template .= Html::beginTag('div', ['class' => 'col-md-6', 'style' => 'padding: 0;']);
                $template .= Html::img($imageUrl, []);
            $template .= Html::endTag('div');

            $template .= Html::beginTag('div', ['class' => 'col-md-6', 'style' => 'padding: 0;']);
                // File Name
                $template .= Html::beginTag('div', ['style' => 'font-weight: bold;']);
                    $template .= $basename;
                $template .= Html::endTag('div');

                $template .= Html::beginTag('div', []);
                    $template .= date('d/m/Y', $fileatime);
                $template .= Html::endTag('div');

                // File size
                $template .= Html::beginTag('div', []);
                    $template .= $filesize;
                $template .= Html::endTag('div');

                $template .= Html::beginTag('div', []);
                    $template .= $width . ' x ' . $height;
                $template .= Html::endTag('div');
            $template .= Html::endTag('div');
        $template .= Html::endTag('div');

        // Attribute img
        $template .= Html::beginTag('div', ['class' => 'col-md-12 form-horizontal', 'style' => 'margin-top: 20px;']);
            // Url
            $template .= Html::beginTag('div', ['class' => 'form-group field-gallery-url']);
                $template .= Html::tag('label', 'Url', ['class' => 'control-label col-sm-3']);
                $template .= Html::beginTag('div', ['class' => 'col-sm-9']);
                    $template .= Html::input('text', '', Yii::$app->request->getHostInfo() . $imageUrl, ['class' => 'col-sm-10 form-control', 'readonly' => true]);
                $template .= Html::endTag('div');
            $template .= Html::endTag('div');

            // Title
            $template .= Html::beginTag('div', ['class' => 'form-group field-gallery-title']);
                $template .= Html::tag('label', 'Title', ['class' => 'control-label col-sm-3']);
                $template .= Html::beginTag('div', ['class' => 'col-sm-9']);
                    $template .= Html::input('text', '', $filename, ['class' => 'col-sm-10 form-control', 'id' => 'sya_preview_title']);
                $template .= Html::endTag('div');
            $template .= Html::endTag('div');

            // Caption
            $template .= Html::beginTag('div', ['class' => 'form-group field-gallery-caption']);
                $template .= Html::tag('label', 'Caption', ['class' => 'control-label col-sm-3']);
                $template .= Html::beginTag('div', ['class' => 'col-sm-9']);
                    $template .= Html::textarea('', '', ['class' => 'col-sm-10 form-control', 'id' => 'sya_preview_caption']);
                $template .= Html::endTag('div');
            $template .= Html::endTag('div');

            // Alt text
            $template .= Html::beginTag('div', ['class' => 'form-group field-gallery-alt-text']);
                $template .= Html::tag('label', 'Alt text', ['class' => 'control-label col-sm-3']);
                $template .= Html::beginTag('div', ['class' => 'col-sm-9']);
                    $template .= Html::input('text', '', '', ['class' => 'col-sm-10 form-control', 'id' => 'sya_preview_alt_text']);
                $template .= Html::endTag('div');
            $template .= Html::endTag('div');

        $template .= Html::endTag('div');
        return $template;
    }
}
