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
            'moduleName' => $this->moduleName,
            'columns' => $this->columns,
            'galleries' => $galleries,
        ]);
    }
    
    private function registerAssets(){
        GalleryAssets::register($this->getView());

        $this->getView()->registerJs('
            function syaUploadImage(type, image){
                //kiem tra trinh duyet co ho tro File API
                if (window.File && window.FileReader && window.FileList && window.Blob)
                {
                        
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

            Dropzone.options.myAwesomeDropzone = {

                autoProcessQueue: false,
                uploadMultiple: true,
                parallelUploads: 100,
                maxFiles: 100,
                paramName: "image",
                url: "' . \yii\helpers\Url::to(['/gallery/ajax/additemimage']) . '",
                headers: {
                    "Accept": "*/*"
                },

                // Dropzone settings
                init: function() {
                    var myDropzone = this;

                    this.element.querySelector("#uploadFile").addEventListener("click", function(e) {
                        myDropzone.options.autoProcessQueue = true;
                        myDropzone.processQueue();
                    });

                    this.on("sending", function(file, xhr, formData) {
                        var module = "' . $this->moduleName . '";
                        var columns = \'' . \yii\helpers\Json::encode($this->columns) . '\';

                        formData.append("type", "upload");
                        formData.append("module", module);
                        formData.append("columns", columns);
                    });
                }

//                accept: function(file, done) {
//                    var myDropzone = this;
//                    syaUploadImage("upload", file);
//                    myDropzone.processQueue();
//                },
            }
        ', yii\web\View::POS_READY);
    }
}
