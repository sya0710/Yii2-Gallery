# yii2-gallery
Module manager gallery, upload image

## Installation
Add 

```php
"sya/yii2-gallery": "~1.0"
```

to the require section of your composer.json file.

# Module
```php
use sya\gallery\Module;
'modules' => [
    'gallery' => [
        'class' => 'sya\gallery\Module',
        'syaDirPath' => '@webroot/',
        'syaDirUrl' => '@web/',
        'syaDirUpload' => 'uploads',
    ],
]
```

## Demo

# Gallery
```php
use sya\gallery\models\Gallery;
$form->field($model, 'gallery'])->widget(Gallery::className(), [
  'columns' => [
      'status' => [
          'title' => 'Status',
          'displayType' => Gallery::SYA_TYPE_COLUMN_DROPDOWN,
          'items' => [
              '0' => 'Ẩn',
              '1' => 'Hiện'
          ]
      ],
  ]
]),
```

# Single Image

```php
use sya\gallery\Image;
$form->field($model, 'image', ['horizontalCssClasses' => []])->widget(Image::className(), [
  'class_button' => 'form-control button_color ',
])
```

## License
**yii2-gallery** is released under the MIT License. See the bundled LICENSE.md for details.
