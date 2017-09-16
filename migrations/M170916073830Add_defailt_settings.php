<?php

namespace yuncms\attachment\migrations;

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
        $this->insert('{{%settings}}', ['type' => 'string', 'section' => 'attachment', 'key' => 'uploadRoot', 'value' => '@root/uploads', 'active' => 1, 'created' => date('Y-m-d H:i:s'), 'modified' => date('Y-m-d H:i:s')]);
        $this->insert('{{%settings}}', ['type' => 'string', 'section' => 'attachment', 'key' => 'uploads', 'value' => '@web/uploads', 'active' => 1, 'created' => date('Y-m-d H:i:s'), 'modified' => date('Y-m-d H:i:s')]);
        $this->insert('{{%settings}}', ['type' => 'string', 'section' => 'attachment', 'key' => 'imageMaxSize', 'value' => '2M', 'active' => 1, 'created' => date('Y-m-d H:i:s'), 'modified' => date('Y-m-d H:i:s')]);
        $this->insert('{{%settings}}', ['type' => 'string', 'section' => 'attachment', 'key' => 'imageAllowFiles', 'value' => 'png,jpg,jpeg,gif,bmp', 'active' => 1, 'created' => date('Y-m-d H:i:s'), 'modified' => date('Y-m-d H:i:s')]);
        $this->insert('{{%settings}}', ['type' => 'string', 'section' => 'attachment', 'key' => 'videoMaxSize', 'value' => '100M', 'active' => 1, 'created' => date('Y-m-d H:i:s'), 'modified' => date('Y-m-d H:i:s')]);
        $this->insert('{{%settings}}', ['type' => 'string', 'section' => 'attachment', 'key' => 'videoAllowFiles', 'value' => 'flv,swf,mkv,avi,rm,rmvb,mpeg,mpg,ogg,ogv,mov,wmv,mp4,webm,mp3,wav,mid', 'active' => 1, 'created' => date('Y-m-d H:i:s'), 'modified' => date('Y-m-d H:i:s')]);
        $this->insert('{{%settings}}', ['type' => 'string', 'section' => 'attachment', 'key' => 'fileMaxSize', 'value' => '100M', 'active' => 1, 'created' => date('Y-m-d H:i:s'), 'modified' => date('Y-m-d H:i:s')]);
        $this->insert('{{%settings}}', ['type' => 'string', 'section' => 'attachment', 'key' => 'fileAllowFiles', 'value' => 'rar,zip,tar,gz,7z,bz2,cab,iso,doc,docx,xls,xlsx,ppt,pptx,pdf,txt,md,xml,xmind', 'active' => 1, 'created' => date('Y-m-d H:i:s'), 'modified' => date('Y-m-d H:i:s')]);
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
