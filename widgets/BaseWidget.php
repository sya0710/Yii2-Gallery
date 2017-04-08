<?php
namespace sya\gallery\widgets;

use Yii;
use yii\helpers\Url;
use yii\bootstrap\Html;
use yii\web\View;
use yii\bootstrap\Tabs;
use yii\bootstrap\Modal;
use yii\helpers\Json;
use sya\gallery\helpers\FileHelper;
use yii\helpers\StringHelper;
use yii\helpers\ArrayHelper;

class BaseWidget extends \yii\widgets\InputWidget {
    
    // Type upload
    CONST TYPE_UPLOAD = 'upload';
    CONST TYPE_URL = 'url';
    CONST TYPE_PATH = 'path';

    // Type column html
    CONST SYA_TYPE_COLUMN_DROPDOWN = 'dropdown';
    CONST SYA_TYPE_COLUMN_TEXTAREA = 'textarea';
    CONST SYA_TYPE_COLUMN_RADIO = 'radio';
    CONST SYA_TYPE_COLUMN_RADIOLIST = 'radioList';
    CONST SYA_TYPE_COLUMN_CHECKBOX = 'checkbox';
    CONST SYA_TYPE_COLUMN_CHECKBOXLIST = 'checkboxList';
    CONST SYA_TYPE_COLUMN_HIDDEN = 'hidden';
    CONST SYA_TYPE_COLUMN_INPUT = 'text';

    protected static $fileType = [
        'jpg',
        'gif',
        'png'
    ];

    protected static $countFileItem = 0;

    protected static $countFileItemLimit = 0;

    /**
     * @var string Tag container header column
     */
    public $tagHeaderColumns = 'th';

    /**
     * @var array Tag Html attribute header column
     */
    public $tagHeaderOptions = [];

    /**
     * @var array Tag Html attribute modal
     */
    public $optionModal = 'sya_gallery_modal';

    public $idPreview = 'sya_gallery_viewpath';

    /**
     * @var array Class Html attribute button
     */
    public $class_button;

    /**
     * @var String Container content gallery
     */
    public $syaContainer = 'tableImage';

    /**
     * @var String Name function preview image
     */
    public static $syaPreviewImage = 'syaPreviewImage';

    /**
     * @var String Option id for button add image
     */
    public $optionButton = 'insert_image';
    /**
     * @var string The template for rendering the gallery within a panel.
     * The following special variables are recognized and will be replaced:
     *  - {columns}: string, render column header titl for image
     *  - {infomationImage}: string, render infomation for image
     */
    public $layouts = <<< HTML
    <!-- Begin Display image gallery -->
    <table class="table table-bordered table-stripped" style="margin-top: 30px;">
        <!-- Begin header table -->
        <thead>
            <tr>
                <th>
                    {preview}
                </th>
                <th>
                    {infomation}
                </th>
                {columns}
                <th>
                    {action}
                </th>
            </tr>
        </thead>
        <!-- End header table -->
        <!-- Begin content table -->
        <tbody id="{syaContainer}">
            {infomationImage}
        </tbody>
        <!-- End content table -->
    </table>
    <!-- End Display image gallery -->
HTML;

    public $infomationImage = <<< HTML
    <tr id="imageItem" style="width: 100%; background: white;">
        <td class="text-center" style="vertical-align: middle;">{image}</td>
        {typeImage}
        <td class="text-center" style="vertical-align: middle;">{infomation}</td>
        {columns}
        <td class="text-center" style="vertical-align: middle;">{actions}</td>
    </tr>
HTML;

    public $infomationImageDetail = <<< HTML
    <div class="form-group field-gallery-url">
        <label class="control-label col-sm-3">{title}</label>
        <div class="col-sm-9">{fieldInput}</div>
    </div>
HTML;
    /**
     * Thong tin cua image
     * $columns = [
     *      'status' => [
     *          'title' => 'Status',
     *          'displayType' => 'dropdown',
     *          'items' => [
     *              'An',
     *              'Hien'
     *           ]
     *      ],
     *      'sort' => [
     *           'title' => 'Sort',
     *           'displayType' => 'text'
     *       ],
     * ]
     *
     * @var array
     */
    public $columns = [];

    /**
     * @var bool
     */
    public $multiple = true;

    // Ten model
    protected $moduleName;

    public function init() {
        parent::init();
        $this->moduleName = StringHelper::basename(get_class($this->model));
    }

    public function run() {
        parent::run();
        $this->registerAssets();
        $this->registerBaseAssets();
        $this->initLayout();
        $template = preg_replace_callback("/{\\w+}/", function ($matches) {
            $template = $this->renderSection($matches[0]);
            return $template === false ? $matches[0] : $template;
        }, $this->layouts);
        $this->renderLayout($template);
    }

    /**
     * Build layout ecommerce template
     * @return string
     */
    protected function initLayout(){
        // Array init element panel template
        $replace = [];
        if (strpos($this->layouts, '{columns}') !== false) {
            $columnsTemplate = $this->renderColumns();
            $replace['{columns}'] = $columnsTemplate;
        }
        
        if (strpos($this->layouts, '{infomationImage}') !== false) {
            $infomationTemplate = $this->renderInfomation();
            $replace['{infomationImage}'] = $infomationTemplate;
        }

        if (strpos($this->layouts, '{syaContainer}') !== false) {
            $replace['{syaContainer}'] = $this->syaContainer;
        }
        $this->layouts = strtr($this->layouts, $replace);
    }

    /**
     * Function generate preview text
     * @return null|string
     */
    protected function renderPreview(){
        $template = Yii::t('gallery', 'Image preview');
        return $template;
    }
    /**
     * Function generate infomation text
     * @return null|string
     */
    protected function renderInfomationText(){
        $template = Yii::t('gallery', 'Image Infomaton');
        return $template;
    }
    /**
     * Function generate action text
     * @return null|string
     */
    protected function renderActionText(){
        $template = Yii::t('gallery', 'Action');
        return $template;
    }

    protected function renderModal() {
        // Display button add image and modal add image
        Modal::begin([
            'header' => Yii::t('gallery', 'Add Image'),
            'footer' => '<button class="btn btn-success" id="' . $this->optionButton . '" data-type="' . self::TYPE_UPLOAD . '">' . Yii::t('gallery', 'Add Image'),
            'toggleButton' => [
                'label' => Yii::t('gallery', 'Add Image'),
                'class' => $this->class_button . 'btn btn-success'
            ],
            'size' => 'modal-lg custom_modal_gallery',
            'options' => [
                'id' => $this->optionModal
            ]
        ]);
            echo Html::beginTag('div', ['class' => 'tabs-container']);
                echo Html::beginTag('div', ['class' => 'tabs-left']);
                    echo Tabs::widget([
                        'navType' => 'nav-tabs',
                        'encodeLabels' => false,
                        'items' => [
                            [
                                'label' => Yii::t('gallery', 'Insert Media'),
                                'content' => '<div class="panel-body"><div id="my-awesome-dropzone" class="dropzone sya_custom_dropzone">
                                                    <div class="dropzone-previews"></div>
                                                </div></div>',
                                'linkOptions' => [
                                    'data-type' => self::TYPE_UPLOAD
                                ]
                            ],
                            [
                                'label' => Yii::t('gallery', 'Media Library'),
                                'content' => '<div class="panel-body" style="padding: 0;">
                                                    <input type="hidden" class="form-control sya_image_input"/>
                                                    <div class="row">
                                                        <div class="col-md-12" style="margin-top: -125px;">
                                                            <div class="col-md-8" id="sya_gallery_path">
                                                                <div class="row sya_media_library"></div>
                                                            </div>
                                                            <div class="col-md-4 ' . $this->idPreview . '"></div>
                                                        </div>
                                                    </div>
                                                </div>',
                                'linkOptions' => [
                                    'data-type' => self::TYPE_PATH
                                ]
                            ],
                            [
                                'label' => Yii::t('gallery', 'Insert from URL'),
                                'content' => '<div class="panel-body" id="sya_gallery_form_preview">
                                                    <input type="hidden" class="form-control sya_image_input"/>
                                                    <input type="url" class="form-control sya_input_info_image" name="sya_url"/>
                                                    <div id="embed_url_settings" class="row"></div>
                                                </div>',
                                'linkOptions' => [
                                    'data-type' => self::TYPE_URL
                                ]
                            ],
                        ]
                    ]);
                echo Html::endTag('div');
            echo Html::endTag('div');
        Modal::end(); // End Display button add image and modal add image
    }

    /**
     * @param $template String template gallery
     * @throws \Exception
     */
    protected function renderLayout($template){
        $this->renderModal();
        echo $template;
    }

    /**
     * Renders a section of the specified name.
     * If the named section is not supported, false will be returned.
     * @param string $name the section name, e.g., `{items}`, `{itemsStatistic}`.
     * @return string|boolean the rendering result of the section, or false if the named section is not supported.
     */
    protected function renderSection($name)
    {
        switch ($name) {
            case '{columns}':
                return $this->renderColumns();
            case '{preview}':
                return $this->renderPreview();
            case '{infomation}':
                return $this->renderInfomationText();
            case '{action}':
                return $this->renderActionText();
            case '{infomationImage}':
                return $this->renderInfomation();
            default:
                return false;
        }
    }

    /**
     * Function generate column for image
     * @return null|string
     */
    protected function renderColumns(){
        $template = null;
        foreach ($this->columns as $column){
            $typeImage = ArrayHelper::getValue($column, 'displayType', self::SYA_TYPE_COLUMN_INPUT);
            $titleColumn = ArrayHelper::getValue($column, 'title');
            if ($typeImage != self::SYA_TYPE_COLUMN_HIDDEN) {
                $template .= Html::tag($this->tagHeaderColumns, $titleColumn, $this->tagHeaderOptions);
            }
        }
        return $template;
    }


    /**
     * Function render infomation basic for image
     * @return null|string
     */
    protected function renderInfomation(){
        $template = null;
        $galleries = Html::getAttributeValue($this->model, $this->attribute);
        if (!empty($galleries)){
            $template .= $this->generateGalleryTemplate($galleries, $this->columns, $this->moduleName, $this->attribute, $this->infomationImage, $this->infomationImageDetail);
        }
        return $template;
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

    /**
     * Function build template one row image
     * @param $gallery Infomation for image
     * @param $galleryId Id gallery
     * @param $columns Column extend image
     * @param string $template Template infomation image
     * @param string $templateInfomationDetail Template infomation detail image
     * @return string
     */
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

    /**
     * Function generate input form infomation image
     * @param $galleryId Id gallery
     * @param $urlImg Url image
     * @param $title Title image
     * @param $caption Caption image
     * @param $alt_text Alt text image
     * @param string $templateInfomationDetail Template infomation detail image
     * @return null|string
     */
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
            ],
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
                $fileTemplate = Html::textInput($module . '[' . $attribute . '][' . $galleryId . '][' . $nameColumns . ']', $value, $options);
                $replace['{fieldInput}'] = $fileTemplate;
            }
            $template .= strtr($layouts, $replace);
        }
        return $template;
    }

    /**
     * Function generate column infomation for image
     * @param $columns Infomation extend for image
     * @param $gallery Infomation default for image
     * @param $galleryId Id gallery image
     * @param $options Html attribute column for image
     * @return null|string
     */
    protected function renderColumnsImage($columns, $gallery, $galleryId, $options, $module, $attribute){
        $template = null;
        foreach ($columns as $keyColumn => $column) {
            $template .= self::generateColumnByType($keyColumn, $column, $gallery, $galleryId, $options, $module, $attribute);
        }
        return $template;
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
    protected static function _generateGalleryTemplateByPath($imgPath, $imgPathFull){
        $template = Html::beginTag('div', ['class' => 'col-md-3 col-lg-3 text-center']);
            $template .= Html::icon('ok', ['class' => 'icon-active sya_remove_img', 'onclick' => 'removeImageByGallery($(this).next());']);
            $infomation_images = Json::encode([
                'url' => $imgPath,
                'title' => reset(explode('.', end(explode('/', $imgPath)))),
                'caption' => '',
                'alt_text' => '',
            ]);
            // View image
            $template .= Html::beginTag('div', ['class' => 'letImgPreview', 'id' => $imgPath, 'data-info' => $infomation_images, 'onclick' => self::$syaPreviewImage . '(this);']);
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
    protected static function generateImageByType($urlImg, $type){
        $options = ['style' => 'width: 100px; height: 100px;'];
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

    /**
     * Ham tao giao dien cho column
     * @param string $keyColumn ten truong cua column
     * @param array $column mang setting cua column
     * @param array $gallery mang gia tri cua image
     * @param string $id id cua 1 anh
     * @param string $tdOptions Html Attribute of td column
     * @return string
     */
    private function generateColumnByType($keyColumn, $column, $gallery, $id, $tdOptions = [], $module, $attribute){
        $typeImage = ArrayHelper::getValue($column, 'displayType', 'text');
        $items = ArrayHelper::getValue($column, 'items', []);
        $options = ArrayHelper::getValue($column, 'options', ['class' => 'form-control']);
        $column_name = $module . '[' . $attribute . '][' . $id . '][' . $keyColumn . ']';
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
        // Title image
        $template .= Html::beginTag('div', ['class' => 'col-sm-12 embed_field']);
            $template .= Html::beginTag('label', ['class' => 'row']);
                $template .= Html::tag('span', 'Title', ['class' => 'col-sm-12']);
                $template .= Html::input('text', 'sya_title', '', ['class' => 'form-control col-sm-12', 'id' => 'sya_preview_title']);
            $template .= Html::endTag('label');
        $template .= Html::endTag('div');
        // Caption image
        $template .= Html::beginTag('div', ['class' => 'col-sm-12 embed_field']);
            $template .= Html::beginTag('label', ['class' => 'row']);
                $template .= Html::tag('span', 'Caption', ['class' => 'col-sm-12']);
                $template .= Html::textarea('sya_caption', '', ['class' => 'form-control col-sm-12', 'id' => 'sya_preview_caption']);
            $template .= Html::endTag('label');
        $template .= Html::endTag('div');
        // Alt text image
        $template .= Html::beginTag('div', ['class' => 'col-sm-12 embed_field']);
            $template .= Html::beginTag('label', ['class' => 'row']);
                $template .= Html::tag('span', 'Alt text', ['class' => 'col-sm-12']);
                $template .= Html::input('text', 'sya_alt_text', '', ['class' => 'form-control col-sm-12', 'id' => 'sya_preview_alt_text']);
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
     * @param $image Infomation image
     * @return string
     */
    public static function generatePreviewImage($image){
        $image =  Json::decode($image);
        $url = ArrayHelper::getValue($image, 'url');
        $title = ArrayHelper::getValue($image, 'title');
        $caption = ArrayHelper::getValue($image, 'caption');
        $alt_text = ArrayHelper::getValue($image, 'alt_text');
        $imageUrl = Yii::getAlias('@web') . DIRECTORY_SEPARATOR . Yii::$app->getModule('gallery')->syaDirUpload . DIRECTORY_SEPARATOR . $url;
        $imagePath = Yii::getAlias(Yii::$app->getModule('gallery')->syaDirPath) . Yii::$app->getModule('gallery')->syaDirUpload . DIRECTORY_SEPARATOR . $url;
        $info = FileHelper::getInfomation($imagePath);
        $basename = ArrayHelper::getValue($info, 'basename');
        $filesize = ArrayHelper::getValue($info, 'filesize');
        $fileatime = ArrayHelper::getValue($info, 'fileatime');
        $width = ArrayHelper::getValue($info, 'width');
        $height = ArrayHelper::getValue($info, 'height');
        $template = Html::tag('h3', Yii::t('gallery', 'ATTACHMENT DETAILS'), []);
        // Info img
        $template .= Html::beginTag('div', ['class' => 'sya_info_galllery row']);
            $template .= Html::beginTag('div', ['class' => 'col-md-6', 'style' => 'padding: 0;']);
                $template .= Html::img($imageUrl, []);
            $template .= Html::endTag('div');
            $template .= Html::beginTag('div', ['class' => 'col-md-6', 'style' => 'padding: 0; word-break: break-word;']);
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
            $template .= Html::beginForm(' ', ' ', ['id' => 'sya_gallery_form_preview']);
                // Url
                $template .= Html::beginTag('div', ['class' => 'form-group field-gallery-url']);
                    $template .= Html::tag('label', 'Url', ['class' => 'control-label col-sm-3']);
                    $template .= Html::beginTag('div', ['class' => 'col-sm-9']);
                        $template .= Html::input('text', 'sya_url', $url, ['class' => 'col-sm-10 form-control', 'readonly' => true]);
                    $template .= Html::endTag('div');
                $template .= Html::endTag('div');
                // Title
                $template .= Html::beginTag('div', ['class' => 'form-group field-gallery-title']);
                    $template .= Html::tag('label', 'Title', ['class' => 'control-label col-sm-3']);
                    $template .= Html::beginTag('div', ['class' => 'col-sm-9']);
                        $template .= Html::input('text', 'sya_title', $title, ['class' => 'col-sm-10 form-control', 'id' => 'sya_preview_title']);
                    $template .= Html::endTag('div');
                $template .= Html::endTag('div');
                // Caption
                $template .= Html::beginTag('div', ['class' => 'form-group field-gallery-caption']);
                    $template .= Html::tag('label', 'Caption', ['class' => 'control-label col-sm-3']);
                    $template .= Html::beginTag('div', ['class' => 'col-sm-9']);
                        $template .= Html::textarea('sya_caption', $caption, ['class' => 'col-sm-10 form-control', 'id' => 'sya_preview_caption']);
                    $template .= Html::endTag('div');
                $template .= Html::endTag('div');
                // Alt text
                $template .= Html::beginTag('div', ['class' => 'form-group field-gallery-alt-text']);
                    $template .= Html::tag('label', 'Alt text', ['class' => 'control-label col-sm-3']);
                    $template .= Html::beginTag('div', ['class' => 'col-sm-9']);
                        $template .= Html::input('text', 'sya_alt_text', $alt_text, ['class' => 'col-sm-10 form-control', 'id' => 'sya_preview_alt_text']);
                    $template .= Html::endTag('div');
                $template .= Html::endTag('div');
            $template .= Html::endForm();
        $template .= Html::endTag('div');
        return $template;
    }

    protected function registerAssets(){
        \sya\gallery\GalleryAssets::register($this->getView());
        $this->getView()->registerJs('
            function removeImageByGallery(element){
                var listImage = $("#image").val().split(";"),
                    image = $(element).attr("data-info");
                if ($("#image").val().length && listImage.length){
                    for(i = 0; i < listImage.length; i++){
                        if (listImage[i] == image){
                            listImage.splice(i, 1);
                        }
                    }
                }
                $("#image").val(listImage.length ? listImage.join(";") : "");
                $(element).parent().removeClass("active");
                $(".' . $this->idPreview . '").html("");
            }

            function addImageByGallery(type){
                var module = "' . $this->moduleName . '",
                    attribute = "' . $this->attribute . '",
                    templateInfomationImage = \'' . str_replace(array("\r\n", "\n", "\r"), '', $this->infomationImage) . '\',
                    templateInfomationImageDetail = \'' . str_replace(array("\r\n", "\n", "\r"), '', $this->infomationImageDetail) . '\',
                    columns = \'' . Json::encode($this->columns) . '\',
                    image = $("#image").val();
                if (image.length > 0) {
                    $.ajax({
                        url: "' . Url::to(['/gallery/ajax/additemimage']) . '",
                        type: "post",
                        data: {type: type, module: module, attribute: attribute, columns: columns, image: image, templateInfomationImage: templateInfomationImage, templateInfomationImageDetail: templateInfomationImageDetail},
                    }).done(function (data) {
                        if (data.length > 0) {
                            $("#' . $this->syaContainer . '").append(data);
                            $("#' . $this->optionModal . '").modal("hide");
                            // Reset value
                            $("#sya_gallery_path").find(".active").removeClass("active");
                            $(".' . $this->idPreview . '").html(" ");
                            $(".sya_image_input").val("");
                        }
                    });
                }
            }

            // Ham xoa anh
            function syaremoveImage(element){
                var confirmAlert = confirm("' . Yii::t('yii', 'Are you sure you want to delete this item?') . '");
                if (confirmAlert == true){
                    $(element).parents("#imageItem").remove();
                    // Kiem tra co anh trong gallery hay khong
                    if ($("#' . $this->syaContainer . '").children().length == 0) {
                        $("#' . $this->syaContainer . '").append(\'' . Html::hiddenInput($this->moduleName . '[' . $this->attribute . ']', '', ['class' => 'form-control']) . '\');
                    }
                }
            }
        ', View::POS_END);
        $this->getView()->registerJs('
            $("#' . $this->optionButton . '").click(function() {
                addImageByGallery($(this).attr("data-type"));
            });
        ', View::POS_READY);
    }

    protected function registerBaseAssets() {
        \sya\gallery\GalleryAssets::register($this->getView());
        $singleGallery = [
            'active' => null,
            'noactive' => ''
        ];
        if (!$this->multiple)
            $singleGallery = [
                'active' => '$($(element).parents(".sya_media_library").find(".active")).removeClass("active"); $("#image").val("");',
                'noactive' => ' else {
                    removeImageByGallery(element);
                }'
            ];
        $this->getView()->registerJs('
            function formChangeValue(){
                $("#sya_gallery_form_preview .form-control").keyup(function(event) {
                    var element = $(this),
                        listImage = $("#image").val().split(";"),
                        url = element.parents("#sya_gallery_form_preview").find("input[name=\'sya_url\']").val(),
                        imageChange = [];
                    for(i = 0; i < listImage.length; i++){
                        var image = jQuery.parseJSON(listImage[i]);
                        if (image.url == url){
                            element.each(function(){
                                image[element.attr("name").replace("sya_", "")] = element.val();
                            });
                            $("div[id=\'" + url + "\']").attr("data-info", JSON.stringify(image));
                        }
                        imageChange[i] = JSON.stringify(image);
                    }
                    $("#image").val(imageChange.length ? imageChange.join(";") : listImage);
                });
            }

            function ' . self::$syaPreviewImage . '(element){
                var listImage = $("#image").val().split(";"),
                    imagesSelected = [],
                    image = $(element).attr("data-info");
                $.ajax({
                    url: "' . Url::to(['/gallery/ajax/getinfoimage']) . '",
                    type: "post",
                    data: {image: image},
                }).done(function (data) {
                    $(".' . $this->idPreview . '").html(data);
                    formChangeValue();
                });
                if (!$(element).parent().hasClass("active")) {
                    ' . ArrayHelper::getValue($singleGallery, "active") . '
                    // Add image for input
                    if ($("#image").val().length && listImage.length){
                        var updateImage = false;
                        for(i = 0; i < listImage.length; i++){
                            if (listImage[i] == image){
                                updateImage = true;
                            }
                            imagesSelected[i] = listImage[i];
                        }
                        if (!updateImage){
                            imagesSelected[imagesSelected.length] = image;
                        }
                    }
                    $("#image").val(imagesSelected.length ? imagesSelected.join(";") : image);
                    $(element).parent().addClass("active");
                }' . ArrayHelper::getValue($singleGallery, "noactive") . '
            }

            var page = 2;
            function syaloadMoreImage(){
                $.ajax({
                    url: "' . Url::to(['/gallery/ajax/getgallerypath']) . '",
                    type: "post",
                    data: {type: $("#' . $this->optionButton . '").attr("data-type"), page: page},
                }).done(function (data) {
                    if (data != "") {
                        $(".sya_media_library").append(data);
                        page += 1;
                    }
                });
            }

            function syaGetGalleryByPath(){
                $.ajax({
                    url: "' . Url::to(['/gallery/ajax/getgallerypath']) . '",
                    type: "post",
                    data: {type: "' . self::TYPE_PATH . '"},
                }).done(function (data) {
                    $(".sya_media_library").html(data);
                    $(".sya_remove_img").hover(function(){
                        $(this).removeClass("glyphicon-ok");
                        $(this).addClass("glyphicon-remove");
                    }, function(){
                        $(this).addClass("glyphicon-ok");
                        $(this).removeClass("glyphicon-remove");
                    });
                });
            }
        ', View::POS_END);

        $this->getView()->registerJs('
            // Sap xep anh
            $("#' . $this->syaContainer . '").sortable({});
            // Dropzone drop and drag file
            Dropzone.options.myAwesomeDropzone = {
                uploadMultiple: true,
                parallelUploads: 100,
                maxFiles: 100,
                paramName: "image",
                params: {
                    type: "' . self::TYPE_UPLOAD . '",
                    module: "' . $this->moduleName . '",
                    columns: \'' . Json::encode($this->columns) . '\'
                },
                autoDiscover: false,
                url: "' . Url::to(['/gallery/ajax/additemimage']) . '",
                headers: {
                    "Accept": "*/*",
                    "' . \yii\web\Request::CSRF_HEADER . '": "' . Yii::$app->getRequest()->csrfToken . '",
                    "Access-Control-Allow-Origin": "*"
                },
                // Dropzone settings
                init: function() {
                    this.on("sendingmultiple", function(image, xhr, formData) {
                        formData.processData = false;
                        formData.contentType = false;
                        formData.enctype = "multipart/form-data";
                    });
                    this.on("complete", function(files, responseText, e) {
                        syaGetGalleryByPath();
                        $(".custom_modal_gallery a[data-type=\'' . self::TYPE_PATH . '\']").tab("show");
                        $(".sya_media_library").prepend(responseText);
                    });
                }
            }

            $(".custom_modal_gallery a[data-toggle=\'tab\']").on("shown.bs.tab", function (e) {
                var tabs = $(e.target).parents(".tabs-left"),
                    image = tabs.find(".active").find(".sya_image_input");
                if ($(".sya_media_library").children().length <= 0 && $(e.target).attr("data-type") == "' . self::TYPE_PATH . '"){
                    syaGetGalleryByPath();
                }
                // Forcus input
                image.focus();
                tabs.find(".sya_image_input").removeAttr("id");
                image.attr("id", "image");
                $("#' . $this->optionButton . '").attr("data-type", $(e.target).attr("data-type"));
            })

            $(".sya_input_info_image").on("input",function(e){
                var image = $(this).val();
                $.ajax({
                    url: "' . Url::to(['/gallery/ajax/getimagepreview']) . '",
                    type: "post",
                    data: {image: image},
                }).done(function (data) {
                    $("#embed_url_settings").html(data);
                    $("#image").val("{\"url\": \"" + image + "\", \"title\":\"\", \"caption\":\"\", \"alt_text\":\"\"}");
                    formChangeValue();
                });
            });

            $( ".sya_media_library" ).scroll(function() {
                var scrollPosition = $(this).scrollTop() + $(this).outerHeight(),
                    divTotalHeight = $(this)[0].scrollHeight
                          + parseInt($(this).css("padding-top"), 10)
                          + parseInt($(this).css("padding-bottom"), 10)
                          + parseInt($(this).css("border-top-width"), 10)
                          + parseInt($(this).css("border-bottom-width"), 10);
                if(scrollPosition + 20 == divTotalHeight)
                {
                    syaloadMoreImage();
                }
            });
        ', View::POS_READY);
    }
}