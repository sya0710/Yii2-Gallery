<?php

namespace sya\gallery;

use Yii;
use yii\bootstrap\Html;

class Gallery extends \yii\widgets\InputWidget {

    // Type upload
    CONST TYPE_UPLOAD = 'upload';
    CONST TYPE_URL = 'url';
    CONST TYPE_PATH = 'path';

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
        $this->moduleName = \yii\helpers\StringHelper::basename(get_class($this->model));

        $this->registerAssets();
    }

    public function run() {
        $galleries = Html::getAttributeValue($this->model, $this->attribute);

        return $this->render('gallery', [
            'moduleName' => $this->moduleName,
            'columns' => $this->columns,
            'galleries' => $galleries,
        ]);
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
                        url: "' . \yii\helpers\Url::to(['/gallery/ajax/getinfoimage']) . '",
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
                    columns = \'' . \yii\helpers\Json::encode($this->columns) . '\',
                    image = $("#image").val(),
                    title = $("#sya_preview_title").val(),
                    caption = $("#sya_preview_caption").val(),
                    alt_text = $("#sya_preview_alt_text").val();

                if (image.length > 0) {
                    $.ajax({
                        url: "' . \yii\helpers\Url::to(['/gallery/ajax/additemimage']) . '",
                        type: "post",
                        data: {type: type, module: module, columns: columns, image: image, title: title, caption: caption, alt_text: alt_text},
                    }).done(function (data) {
                        if (data.length > 0) {
                            $("#tableImage").append(data);
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
                    if ($("#tableImage").children().length == 0) {
                        $("#tableImage").append(\'' . Html::hiddenInput($this->moduleName . '[gallery]', '', ['class' => 'form-control']) . '\');
                    }
                }
            }

            var page = 2;
            function syaloadMoreImage(){
                $.ajax({
                    url: "' . \yii\helpers\Url::to(['/gallery/ajax/getgallerypath']) . '",
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
                    url: "' . \yii\helpers\Url::to(['/gallery/ajax/getgallerypath']) . '",
                    type: "post",
                    data: {type: "' . self::TYPE_PATH . '"},
                }).done(function (data) {
                    $(".sya_media_library").html(data);
                });
            }
        ', \yii\web\View::POS_END);

        $this->getView()->registerJs('
            // Sap xep anh
            $("#tableImage").sortable({});

            // Dropzone drop and drag file
            Dropzone.options.myAwesomeDropzone = {
                uploadMultiple: true,
                parallelUploads: 100,
                maxFiles: 100,
                paramName: "image",
                params: {
                    type: "' . self::TYPE_UPLOAD . '",
                    module: "' . $this->moduleName . '",
                    columns: \'' . \yii\helpers\Json::encode($this->columns) . '\'
                },
                autoDiscover: false,
                url: "' . \yii\helpers\Url::to(['/gallery/ajax/additemimage']) . '",
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
                    url: "' . \yii\helpers\Url::to(['/gallery/ajax/getimagepreview']) . '",
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
        ', yii\web\View::POS_READY);
    }
}
