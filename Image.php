<?php
namespace sya\gallery;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use sya\gallery\helpers\FileHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\View;

class Image extends \sya\gallery\widgets\BaseWidget {
    
    public $layouts = <<< HTML
        <div id="{syaContainer}" style="margin-bottom: 10px;" class="sya_file_preview">
            {infomationImage}
        </div>
HTML;

    public $infomationImage = <<< HTML
        <div class="sya_preview_image">
        {image}
        {typeImage}
        {infomation}
        {columns}
        </div>
HTML;

    public $infomationImageDetail = <<< HTML
        {fieldInput}
HTML;

    /**
     * @var String Name function preview image
     */
    public static $syaPreviewImage = 'syaPreviewSingleImage';

    /**
     * @var bool
     */
    public $multiple = false;

    /**
     * @param $template String template gallery
     * @throws \Exception
     */
    protected function renderLayout($template){
        echo $template;
        $this->renderModal();
    }

    /**
     * Ham tao giao dien cho image
     * @param array $galleries mang cac gia tri cua image
     * @param array $columns danh sach truong cua image
     * @param string $module Module name
     * @param string $attribute Attribute name
     * @param string $template Template infomation image
     * @param string $templateInfomationDetail Template infomation detail image
     * @return string
     */
    public static function generateGalleryTemplate($galleries, $columns = [], $module, $attribute, $template = null, $templateInfomationDetail = null){
        $templateGallery = '';

        if (empty($galleries) OR !is_array($galleries))
            return null;

        foreach ($galleries as $galleryId => $gallery) {
            // Tao giao dien cho cac image
            $templateGallery .= self::buildTemplateRow($gallery, $galleryId, $module, $attribute, $columns, $template, $templateInfomationDetail);
        }
        return $templateGallery;
    }

    protected function buildTemplateRow($gallery, $galleryId, $module, $attribute, $columns, $template, $templateInfomationDetail){
        // Gia tri mac dinh cua image
        $urlImg = ArrayHelper::getValue($gallery, 'url');
        $type = ArrayHelper::getValue($gallery, 'type');
        $title = ArrayHelper::getValue($gallery, 'title');
        $caption = ArrayHelper::getValue($gallery, 'caption');
        $alt_text = ArrayHelper::getValue($gallery, 'alt_text');
        $options = ArrayHelper::getValue($gallery, 'options', []);
        $optionsDefault = ['class' => 'text-center', 'style' => 'vertical-align: middle;'];
        $options = ArrayHelper::merge($optionsDefault, $options);
        // Build layout image
        $layouts = $template;
        $replace = [];
        if (strpos($layouts, '{image}') !== false) {
            $imageTemplate = self::generateImageByType($urlImg, $type);
            $replace['{image}'] = $imageTemplate;
        }
        if (strpos($layouts, '{typeImage}') !== false) {
            $typeImageTemplate = Html::hiddenInput($module . '[' . $attribute . '][' . $galleryId . '][type]', $type, ['class' => 'form-control']);
            $replace['{typeImage}'] = $typeImageTemplate;
        }
        if (strpos($layouts, '{infomation}') !== false) {
            $infomationTemplate = self::renderInfomationImage($galleryId, $urlImg, $title, $caption, $alt_text, $module, $attribute, $templateInfomationDetail);
            $replace['{infomation}'] = $infomationTemplate;
        }
        if (strpos($layouts, '{columns}') !== false) {
            $columnsTemplate = self::renderColumnsImage($columns, $gallery, $galleryId, $options, $module, $attribute);
            $replace['{columns}'] = $columnsTemplate;
        }
        if (strpos($layouts, '{actions}') !== false) {
            $actionsTemplate = Html::button('<i class="fa fa-trash"></i>', ['class' => 'btn btn-white', 'onclick' => 'syaremoveImage(this);']);
            $replace['{actions}'] = $actionsTemplate;
        }
        return strtr($layouts, $replace);
    }

    protected static function generateImageByType($urlImg, $type){
        $options = ['style' => 'max-height: 300px;', 'class' => 'img-responsive'];
        $template = null;
        switch ($type) {
            case self::TYPE_URL:
                $template = Html::img($urlImg, $options);
                break;
            default:
                $template = Html::img(FileHelper::getFileUploaded($urlImg), $options);
                break;
        }
        return $template;
    }

    protected function renderInfomationImage($galleryId, $urlImg, $title, $caption, $alt_text, $module, $attribute, $templateInfomationDetail){
        $template = null;
        // config layout infomation image
        $layouts = $templateInfomationDetail;
        $replace = [];
        // Html attribute default input
        $options = [
            'class' => 'form-control'
        ];
        // Array column infomation for
        $infomation = [
            'url' => [
                'label' => 'Url',
                'value' => $urlImg,
                'options' => ArrayHelper::merge([
                    'readonly' => true
                ], $options)
            ],
            'title' => [
                'label' => 'Title',
                'value' => $title,
                'options' => $options
            ],
            'caption' => [
                'label' => 'Caption',
                'value' => $caption,
                'options' => $options
            ],
            'alt_text' => [
                'label' => 'Alt text',
                'value' => $alt_text,
                'options' => $options
            ]
        ];
        foreach ($infomation as $nameColumns => $info) {
            $label = ArrayHelper::getValue($info, 'label');
            $value = ArrayHelper::getValue($info, 'value');
            $options = ArrayHelper::getValue($info, 'options');
            if (strpos($layouts, '{title}') !== false) {
                $titleTemplate = $label;
                $replace['{title}'] = $titleTemplate;
            }
            if (strpos($layouts, '{fieldInput}') !== false) {
                $fileTemplate = Html::hiddenInput($module . '[' . $attribute . '][' . $galleryId . '][' . $nameColumns . ']', $value, $options);
                $replace['{fieldInput}'] = $fileTemplate;
            }
            $template .= strtr($layouts, $replace);
        }
        return $template;
    }

    protected function registerAssets(){
        \sya\gallery\GalleryAssets::register($this->getView());

        $this->getView()->registerJs('
            function addImageBySingleImage(type){
                var module = "' . $this->moduleName . '",
                    attribute = "' . $this->attribute . '",
                    templateInfomationImage = \'' . str_replace(array("\r\n", "\n", "\r"), '', $this->infomationImage) . '\',
                    templateInfomationImageDetail = \'' . str_replace(array("\r\n", "\n", "\r"), '', $this->infomationImageDetail) . '\',
                    columns = \'' . Json::encode($this->columns) . '\',
                    image = $("#image").val(),
                    widget_class = "/sya/gallery/Image";
                if (image.length > 0) {
                    $.ajax({
                        url: "' . Url::to(['/gallery/ajax/additemimage']) . '",
                        type: "post",
                        data: {type: type, widget_class: widget_class, module: module, attribute: attribute, columns: columns, image: image, templateInfomationImage: templateInfomationImage, templateInfomationImageDetail: templateInfomationImageDetail},
                    }).done(function (data) {
                        if (data.length > 0) {
                            $("#' . $this->syaContainer . '").html("");
                            $("#' . $this->syaContainer . '").append(data);
                            $("#' . $this->optionModal . '").modal("hide");
                            // Reset value
                            $("#sya_gallery_path").find(".active").removeClass("active");
                            $(".' . $this->idPreview . '").html("");
                            $(".sya_image_input").val("");
                        }
                    });
                }
            }
        ', View::POS_END);

        $this->getView()->registerJs('
            $("#' . $this->optionButton . '").click(function() {
                addImageBySingleImage($(this).attr("data-type"));
            });
        ', View::POS_READY);
    }
}