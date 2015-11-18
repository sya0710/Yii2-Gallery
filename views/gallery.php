<?php
use yii\bootstrap\Modal;
use yii\bootstrap\Html;
use sya\gallery\Gallery;
use yii\bootstrap\Tabs;

// Display button add image and modal add image
Modal::begin([
    'header' => Yii::t('yii', 'Add Image'),
    'footer' => '<button class="btn btn-success" id="insert_image" data-type="' . Gallery::TYPE_UPLOAD . '">' . Yii::t('yii', 'Insert Image'),
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
                        'data-type' => Gallery::TYPE_UPLOAD
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
                        'data-type' => Gallery::TYPE_PATH
                    ]
                ],
                [
                    'label' => Yii::t('gallery', 'Insert from URL'),
                    'content' => '<div class="panel-body">
                                <input type="url" class="form-control sya_image_input sya_input_info_image"/>
                                <div id="embed_url_settings" class="row"></div>
                            </div>',
                    'linkOptions' => [
                        'data-type' => Gallery::TYPE_URL
                    ]
                ],
            ]
        ]);
    echo Html::endTag('div');
echo Html::endTag('div');
Modal::end(); // End Display button add image and modal add image ?>
<!-- Begin Display image gallery -->
<?= $template; ?>