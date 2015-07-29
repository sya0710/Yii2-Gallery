# yii2-gallery
Module manager gallery, upload image

## Installation
Add 

```php
"sya/yii2-gallery": "dev-master"
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

```php
use sya\gallery\Gallery;
$form->field($model, 'gallery'])->widget(Gallery::className(), [
  'columns' => [
      'status' => [
          'title' => 'Status',
          'displayType' => 'dropdown',
          'items' => [
              '0' => 'Ẩn',
              '1' => 'Hiện'
          ]
      ],
  ]
]),
```

## License
**yii2-gallery** is released under the MIT License. See the bundled LICENSE.md for details.
