<?php
use yii\bootstrap\Modal;
use yii\helpers\ArrayHelper;

// Display button add image and modal add image
Modal::begin([
    'header' => Yii::t('yii', 'Add Image'),
    'toggleButton' => [
        'label' => Yii::t('yii', 'Add Image'),
        'class' => 'btn btn-success'
    ],
    'size' => 'modal-lg'
]);

echo yii\bootstrap\Tabs::widget([
    'itemOptions' => [
        'style' => 'margin-top: 30px;'
    ],
    'navType' => 'menuManager nav-tabs',
    'encodeLabels' => false,
    'items' => [
        [
            'label' => Yii::t('yii', 'Please upload a file.'),
            'content' => '<div id="my-awesome-dropzone" class="dropzone sya_custom_dropzone">
                            <div class="dropzone-previews"></div>
                            <button type="button" id="uploadFile" class="btn btn-primary pull-right">Submit this form!</button>
                        </div>'
        ],
        [
            'label' => Yii::t('gallery', 'Media library')
        ],
    ]
]);

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