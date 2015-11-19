<?php

namespace sya\gallery;

use Yii;
use yii\bootstrap\Html;
use yii\helpers\Url;
use yii\helpers\StringHelper;
use yii\web\View;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use sya\gallery\models\Gallery as GalleryModel;
use yii\bootstrap\Modal;
use yii\bootstrap\Tabs;

class Gallery extends \yii\widgets\InputWidget {

    // Type upload
    CONST TYPE_UPLOAD = 'upload';
    CONST TYPE_URL = 'url';
    CONST TYPE_PATH = 'path';

    /**
     * @var string Tag container header column
     */
    public $tagHeaderColumns = 'th';

    /**
     * @var array Tag Html attribute header column
     */
    public $tagHeaderOptions = [];

    public $syaContainer = '#tableImage';

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
                    Image preview
                </th>
                <th>
                    Image Infomaton
                </th>
                {columns}
                <th>
                    Action
                </th>
            </tr>
        </thead>
        <!-- End header table -->
        <!-- Begin content table -->
        <tbody id="tableImage">
            {infomationImage}
        </tbody>
        <!-- End content table -->
    </table>
    <!-- End Display image gallery -->
HTML;

    public static $infomationImage = <<< HTML
    <tr id="imageItem" style="width: 100%; background: white;">
        <td class="text-center" style="vertical-align: middle;">{image}</td>
        {typeImage}
        <td class="text-center" style="vertical-align: middle;">{infomation}</td>
        {columns}
        <td class="text-center" style="vertical-align: middle;">{actions}</td>
    </tr>
HTML;

    public static $infomationImageDetail = <<< HTML
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
    private $moduleName;

    public function init() {
        parent::init();

        $this->moduleName = StringHelper::basename(get_class($this->model));
    }

    public function run() {
        parent::run();

        $this->registerAssets();
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

        $this->layouts = strtr($this->layouts, $replace);
    }

    protected function renderLayout($template){
        // Display button add image and modal add image
        Modal::begin([
            'header' => Yii::t('yii', 'Add Image'),
            'footer' => '<button class="btn btn-success" id="insert_image" data-type="' . self::TYPE_UPLOAD . '">' . Yii::t('yii', 'Insert Image'),
            'toggleButton' => [
                'label' => Yii::t('yii', 'Add Image'),
                'class' => 'btn btn-success'
            ],
            'size' => 'modal-lg custom_modal_gallery',
            'options' => [
                'id' => 'sya_gallery_modal'
            ]
        ]);
            echo Html::beginTag('div', ['class' => 'tabs-container']);
                echo Html::beginTag('div', ['class' => 'tabs-left']);
                    echo Tabs::widget([
                        'navType' => 'nav-tabs',
                        'encodeLabels' => false,
                        'items' => [
                            [
                                'label' => Yii::t('yii', 'Insert Media'),
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
                                                            <div class="col-md-4" id="sya_gallery_viewpath"></div>
                                                        </div>
                                                    </div>
                                                </div>',
                                'linkOptions' => [
                                    'data-type' => self::TYPE_PATH
                                ]
                            ],
                            [
                                'label' => Yii::t('gallery', 'Insert from URL'),
                                'content' => '<div class="panel-body">
                                                    <input type="url" class="form-control sya_image_input sya_input_info_image"/>
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
            case '{infomationImage}':
                return $this->renderInfomation();
            default:
                return false;
        }
    }

    protected function renderColumns(){
        $template = null;
        foreach ($this->columns as $column){
            $typeImage = ArrayHelper::getValue($column, 'displayType', GalleryModel::SYA_TYPE_COLUMN_INPUT);
            $titleColumn = ArrayHelper::getValue($column, 'title');

            if ($typeImage != GalleryModel::SYA_TYPE_COLUMN_HIDDEN) {
                $template .= Html::tag($this->tagHeaderColumns, $titleColumn, $this->tagHeaderOptions);
            }
        }

        return $template;
    }

    protected function renderInfomation(){
        $template = null;
        $galleries = Html::getAttributeValue($this->model, $this->attribute);

        if (!empty($galleries)){
            $template .= GalleryModel::generateGalleryTemplate($galleries, $this->moduleName, $this->attribute, $this->columns);
        }

        return $template;
    }

    private function registerAssets(){
        GalleryAssets::register($this->getView());

        $multipleGallery = null;
        if (!$this->multiple)
            $multipleGallery = '$($(element).parents(".sya_media_library").find(".active")).removeClass("active"); $("#image").val("");';

        $this->getView()->registerJs('
            function syaPreviewImage(element){
                var listImage = $("#image").val().split(","),
                    imagesSelected = [],
                    image = $(element).attr("id");
                if (!$(element).parent().hasClass("active")) {
                    $.ajax({
                        url: "' . Url::to(['/gallery/ajax/getinfoimage']) . '",
                        type: "post",
                        data: {image: image},
                    }).done(function (data) {
                        $("#sya_gallery_viewpath").html(data);
                    });
                    ' . $multipleGallery . '

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

                    $("#image").val(imagesSelected.length ? imagesSelected.join() : image);

                    $(element).parent().addClass("active");
                } else {
                    if ($("#image").val().length && listImage.length){
                        for(i = 0; i < listImage.length; i++){
                            if (listImage[i] == image){
                                listImage.splice(i, 1);
                            }
                        }
                    }

                    $("#image").val(listImage.length ? listImage.join() : "");

                    $(element).parent().removeClass("active");
                    $("#sya_gallery_viewpath").html("");
                }
            }

            function addImageByGallery(type){
                var module = "' . $this->moduleName . '",
                    attribute = "' . $this->attribute . '",
                    columns = \'' . Json::encode($this->columns) . '\',
                    image = $("#image").val(),
                    title = $("#sya_preview_title").val(),
                    caption = $("#sya_preview_caption").val(),
                    alt_text = $("#sya_preview_alt_text").val();

                if (image.length > 0) {
                    $.ajax({
                        url: "' . Url::to(['/gallery/ajax/additemimage']) . '",
                        type: "post",
                        data: {type: type, module: module, attribute: attribute, columns: columns, image: image, title: title, caption: caption, alt_text: alt_text},
                    }).done(function (data) {
                        if (data.length > 0) {
                            $("' . $this->syaContainer . '").append(data);
                            $("#sya_gallery_modal").modal("hide");

                            // Reset value
                            $("#sya_gallery_path").find(".active").removeClass("active");
                            $("#sya_gallery_viewpath").html("");
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
                    if ($("' . $this->syaContainer . '").children().length == 0) {
                        $("' . $this->syaContainer . '").append(\'' . Html::hiddenInput($this->moduleName . '[' . $this->attribute . ']', '', ['class' => 'form-control']) . '\');
                    }
                }
            }

            var page = 2;
            function syaloadMoreImage(){
                $.ajax({
                    url: "' . Url::to(['/gallery/ajax/getgallerypath']) . '",
                    type: "post",
                    data: {type: $("#insert_image").attr("data-type"), page: page},
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
                });
            }
        ', View::POS_END);

        $this->getView()->registerJs('
            // Sap xep anh
            $("' . $this->syaContainer . '").sortable({});

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

                $("#insert_image").attr("data-type", $(e.target).attr("data-type"));
            })

            $(".sya_input_info_image").on("input",function(e){
                $.ajax({
                    url: "' . Url::to(['/gallery/ajax/getimagepreview']) . '",
                    type: "post",
                    data: {image: $(this).val()},
                }).done(function (data) {
                    $("#embed_url_settings").html(data);
                });
            });

            $("#insert_image").click(function() {
                addImageByGallery($(this).attr("data-type"));
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
