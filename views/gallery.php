<?php
use yii\bootstrap\Modal;
use yii\helpers\ArrayHelper;
use yii\bootstrap\Html;

// Display button add image and modal add image
Modal::begin([
    'header' => Yii::t('yii', 'Add Image'),
    'footer' => '<button class="btn btn-success" id="insert_image" data-type="' . \sya\gallery\Gallery::TYPE_UPLOAD . '">' . Yii::t('yii', 'Insert Image') . '</button>',
    'toggleButton' => [
        'label' => Yii::t('yii', 'Add Image'),
        'class' => 'btn btn-success'
    ],
    'size' => 'modal-lg custom_modal_gallery',
    'options' => [
        'id' => 'sya_gallery'
    ]
]);
echo Html::beginTag('div', ['class' => 'tabs-container']);
    echo Html::beginTag('div', ['class' => 'tabs-left']);
        echo yii\bootstrap\Tabs::widget([
            'navType' => 'nav-tabs',
            'encodeLabels' => false,
            'items' => [
                [
                    'label' => Yii::t('yii', 'Insert Media'),
                    'content' => '<div class="panel-body"><div id="my-awesome-dropzone" class="dropzone sya_custom_dropzone">
                        <div class="dropzone-previews"></div>
                    </div></div>',
                    'linkOptions' => [
                        'data-type' => \sya\gallery\Gallery::TYPE_UPLOAD
                    ]
                ],
                [
                    'label' => Yii::t('gallery', 'Insert from URL'),
                    'content' => '<div class="panel-body">
                        <input type="url" id="image" class="form-control"/>
                        <div id="embed_url_settings" class="row">

                        </div>
                    </div>',
                    'linkOptions' => [
                        'data-type' => \sya\gallery\Gallery::TYPE_URL
                    ]
                ],
            ]
        ]);
    echo Html::endTag('div');
echo Html::endTag('div');
Modal::end(); // End Display button add image and modal add image ?>
<!-- Begin Display image gallery -->
<table class="table table-bordered table-stripped" style="margin-top: 30px;">
    <!-- Begin header table -->
    <thead>
        <tr>
            <th>
                Image preview
            </th>
            <th>
                Image url
            </th>
             <?php foreach ($columns as $column): $titleColumn = ArrayHelper::getValue($column, 'title'); ?>
                <th>
                    <?= $titleColumn ?>
                </th>
            <?php endforeach; ?>
            <th>
                Action
            </th>
        </tr>
    </thead>
    <!-- End header table -->
    <!-- Begin content table -->
    <tbody id="tableImage">
        <?php if (!empty($galleries)){
            echo sya\gallery\models\Gallery::generateGalleryTemplate($galleries, $moduleName, $columns);
        } ?>
    </tbody>
    <!-- End content table -->
</table>
<!-- End Display image gallery -->