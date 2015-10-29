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
                uploadMultiple: true,
                parallelUploads: 100,
                maxFiles: 100,
                paramName: "image",
                params: {
                    type: "' . Gallery::TYPE_UPLOAD . '",
                    module: "' . $this->moduleName . '",
                    columns: \'' . \yii\helpers\Json::encode($this->columns) . '\'
                },
                autoDiscover: false,
                url: "http://sya' . \yii\helpers\Url::to(['/gallery/ajax/additemimage']) . '",
                headers: {
                    "Accept": "*/*",
                    "' . \yii\web\Request::CSRF_HEADER . '": "' . Yii::$app->getRequest()->csrfToken . '"
                },

                // Dropzone settings
                init: function() {
                    this.on("sendingmultiple", function(image, xhr, formData) {
                        formData.processData = false;
                        formData.contentType = false;
                    });

                    this.on("successmultiple", function(files, responseText, e) {
                        $("#tableImage").append(responseText);
                    });
                }
            }
        ', yii\web\View::POS_READY);
    }
}
