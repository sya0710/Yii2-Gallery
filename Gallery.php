<?php

namespace sya\gallery;

use Yii;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap\Modal;
use yii\bootstrap\ActiveForm;

class Gallery extends \yii\widgets\InputWidget {
    // Kieu upload anh
    private $typeImage = [
        'upload' => 'Upload ảnh mới',
        'url' => 'Link trực tiếp',
        'path' => 'Chọn từ gallery',
    ];
    
    /**
     * Thong tin cua image
     * $columns = [
     *      'status' => [
     *          'title' => 'Status',
     *          'displayType' => 'dropdown',
     *          'items' => [
                    'An',
                    'Hien'
                ]
     *      ],
     *      'sort' => [
                'title' => 'Sort',
                'displayType' => 'text'
            ],
     * ]
     * 
     * @var array
     */
    public $columns = [];
    
    // Ten model
    private $moduleName;
    
    public function init() {
        $this->moduleName = \yii\helpers\StringHelper::basename(get_class($this->model));
        $this->registerAssets();
    }

    public function run() {
        $galleries = Html::getAttributeValue($this->model, $this->attribute);
        
        return $this->render('gallery', [
            'typeImage' => $this->typeImage,
            'moduleName' => $this->moduleName,
            'columns' => $this->columns,
            'galleries' => $galleries,
        ]);
    }
    
    private function registerAssets(){
        GalleryAssets::register($this->getView());
        
        $this->getView()->registerCss('
            .letImgPreview {border: 1px solid #B5B5B5; padding: 4px; margin-bottom: 20px; cursor: pointer;}
            .letImgPreview img {max-width: 100%; height: 200px;}
        ');
        
        $this->getView()->registerJs('
            function letUploadImage(){
                //kiem tra trinh duyet co ho tro File API
                if (window.File && window.FileReader && window.FileList && window.Blob)
                {
                    var type = $(".input_image").attr("data-type");
                    // Kiem tra kieu upload anh
                    if (type == "upload"){
                        var image = $(".input_image")[0].files[0];
                        // Neu khong upload anh
                        if (image == "undefined")
                            image = null;
                    } else {
                        var image = $(".input_image").val();
                    }
                        
                    var module = "' . $this->moduleName . '";
                    var columns = \'' . \yii\helpers\Json::encode($this->columns) . '\';

                    var data = new FormData();
                    data.append("type", type);
                    data.append("module", module);
                    data.append("columns", columns);
                    data.append("image", image);

                    $.ajax({
                        url: "' . \yii\helpers\Url::to(['/gallery/ajax/additemimage']) . '",
                        type: "post",
                        data: data,
                        processData: false,
                        contentType: false,
                        enctype: "multipart/form-data",
                    }).done(function (data) {
                        $("#tableImage").append(data);
                    });
                } else {
                    alert("Please upgrade your browser, because your current browser lacks some new features we need!");
                }
            }
            
            function insertImagePath(element){
                var data = $("#let_galleries").val();
                if (data == ""){
                    $("#let_galleries").val($(element).attr("id"));
                } else {
                    $("#let_galleries").val(data.concat("," + $(element).attr("id")));
                }
            }
            
            // Ham xoa anh
            function removeImage(element){
                var confirmAlert = confirm("' . Yii::t('yii', 'Are you sure you want to delete this item?') . '");
                if (confirmAlert == true){
                    $(element).parents("#imageItem").remove();
                    // Kiem tra co anh trong gallery hay khong
                    if ($("#tableImage").children().length == 0) {
                        $("#tableImage").append(\'' . Html::hiddenInput($this->moduleName . '[gallery]', '', ['class' => 'form-control']) . '\');
                    }
                }
            }
        ', \yii\web\View::POS_END);
        
        $this->getView()->registerJs('
            // Sap xep anh
            $("#tableImage").sortable({});

            // Change type upload anh
            $("#changeTypeImage").change(function () {
                var type = $(this).val();
                $.ajax({
                    url: "' . \yii\helpers\Url::to(['/gallery/ajax/getinputupload']) . '",
                    type: "post",
                    data: {type: type},
                }).done(function (data) {
                    $("#field-type-image").html(data);
                });
            }).change();
        ', yii\web\View::POS_READY);
    }
}
