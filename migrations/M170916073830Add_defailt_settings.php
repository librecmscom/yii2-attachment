<?php

namespace yuncms\attachment\migrations;

use Yii;
use yii\db\Migration;

/**
 * Class M170916073830Add_defailt_settings
 */
class M170916073830Add_defailt_settings extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        Yii::$app->settings->set('storePath', '@root/uploads', 'attachment');
        Yii::$app->settings->set('storeUrl', '@web/uploads', 'attachment');

        Yii::$app->settings->set('imageMaxSize', '2M', 'attachment');
        Yii::$app->settings->set('imageAllowFiles', 'png,jpg,jpeg,gif,bmp', 'attachment');

        Yii::$app->settings->set('videoMaxSize', '100M', 'attachment');
        Yii::$app->settings->set('videoAllowFiles', 'flv,swf,mkv,avi,rm,rmvb,mpeg,mpg,ogg,ogv,mov,wmv,mp4,webm,mp3,wav,mid', 'attachment');

        Yii::$app->settings->set('fileMaxSize', '100M', 'attachment');
        Yii::$app->settings->set('fileAllowFiles', 'rar,zip,tar,gz,7z,bz2,cab,iso,doc,docx,xls,xlsx,ppt,pptx,pdf,txt,md,xml,xmind', 'attachment');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->delete('{{%settings}}', ['section' => 'attachment']);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M170916073830Add_defailt_settings cannot be reverted.\n";

        return false;
    }
    */
}
