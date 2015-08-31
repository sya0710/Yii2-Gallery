<?php
use yii\bootstrap\Modal;
use yii\bootstrap\Html;
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
?>
    <div class="row">
        <?= Html::dropDownList('typeImage', 'upload', $typeImage, ['class' => 'form-control', 'id' => 'changeTypeImage']); ?>
        <div id="field-type-image" class="field-type-image form-group" style="margin-top: 20px;">
            <?= sya\gallery\models\Gallery::getInputImageByType('upload'); ?>
        </div>
        <?= Html::button('Upload', ['class' => 'btn btn-success pull-right', 'onclick' => 'syaUploadImage();']); ?>
    </div>
<?php Modal::end(); // End Display button add image and modal add image ?>
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