<?php
use yii\helpers\Url;
use yii\helpers\Html;
use xutl\plupload\Plupload;
use xutl\plupload\PluploadJuiAsset;

$this->title = Yii::t('attachment', 'File Upload');
$this->registerMetaTag(['charset' => Yii::$app->charset]);
$asset = PluploadJuiAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <?= Html::tag('title', Html::encode($this->title)); ?>
    <?= Html::csrfMetaTags() ?>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<div id="uploader">
    <p>Your browser doesn't have Flash, Silverlight or HTML5 support.</p>
</div>
<?php $this->endBody() ?>
<script type="text/javascript">
    // Initialize the widget when the DOM is ready
    var uplodfiles = '';
    jQuery(function () {
        jQuery("#uploader").plupload({
            // General settings
            runtimes: 'html5,flash,silverlight,html4',
            url: '<?=Url::to(['/attachment/upload/multiple-upload'])?>',
            // User can upload no more then 20 files in one go (sets multiple_queues to false)
            max_file_count: <?=$maxFileCount;?>,

            chunk_size: '<?=$chunkSize;?>',
            filters: {
                // Maximum file size
                max_file_size: '<?=$maxFileSize;?>',
                // Specify what files to browse for
                mime_types: [
                    {title: "Image files", extensions: "jpg,gif,png"},
                    {title: "Zip files", extensions: "zip"},
                    {title: "Word files", extensions: "docx"},
                    {title: "pdf", extensions: "pdf"},
                ]
            },
            init: {
                FileUploaded: function (up, file, info) {

                    myres = JSON.parse(info.response);
                    if (myres['error']) {
                        alert(myres['error']['message']);
                        return;
                    }
                    if (myres['result']) {
                        if (<?=$maxFileCount;?> > 1) uplodfiles += myres['result'] + ',' + myres['filename'] + ',' + myres['id'] + '|';
                        else uplodfiles += myres['result'] + '|';
                    }
                },
                UploadComplete: function () {
                    if (uplodfiles != '') {
                        uplodfiles = uplodfiles.substring(0, uplodfiles.lastIndexOf('|'));
                    }
                    console.info(uplodfiles);
                    //console.info(file);
                },
            },
            // Rename files by clicking on their titles
            rename: true,

            // Sort files
            sortable: true,

            // Enable ability to drag'n'drop files onto the widget (currently only HTML5 supports that)
            dragdrop: true,

            // Views to activate
            views: {
                list: true,
                thumbs: true, // Show thumbs
                active: 'thumbs'
            },

            // Flash settings
            flash_swf_url: '<?=$asset->baseUrl;?>Moxie.swf',

            // Silverlight settings
            silverlight_xap_url: '<?=$asset->baseUrl;?>Moxie.xap'
        });
    });

    var dialog = '';
    jQuery(function () {
        try {
            dialog = top.dialog.get(window);
        } catch (e) {
            jQuery('body').append(
                '<p><strong>Error:</strong> 跨域无法无法操作 iframe 对象</p>'
                + '<p>chrome 浏览器本地会认为跨域，请使用 http 方式访问当前页面</p>'
            );
            return;
        }

        dialog.title('<?=Yii::t('attachment', 'File Upload')?>');
        dialog.reset();
    })
</script>
</body>
</html><?php $this->endPage() ?>
