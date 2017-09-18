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
        $this->batchInsert('{{%settings}}', ['type','section','key','value','active','created','modified'], [
            ['string','attachment','storePath','@root/uploads',1,date('Y-m-d H:i:s'),date('Y-m-d H:i:s')],
            ['string','attachment','storeUrl','@web/uploads',1,date('Y-m-d H:i:s'),date('Y-m-d H:i:s')],

            ['string','attachment','imageMaxSize','2M',1,date('Y-m-d H:i:s'),date('Y-m-d H:i:s')],
            ['string','attachment','imageAllowFiles','png,jpg,jpeg,gif,bmp',1,date('Y-m-d H:i:s'),date('Y-m-d H:i:s')],

            ['string','attachment','videoMaxSize','100M',1,date('Y-m-d H:i:s'),date('Y-m-d H:i:s')],
            ['string','attachment','videoAllowFiles','flv,swf,mkv,avi,rm,rmvb,mpeg,mpg,ogg,ogv,mov,wmv,mp4,webm,mp3,wav,mid',1,date('Y-m-d H:i:s'),date('Y-m-d H:i:s')],

            ['string','attachment','fileMaxSize','100M',1,date('Y-m-d H:i:s'),date('Y-m-d H:i:s')],
            ['string','attachment','fileAllowFiles','rar,zip,tar,gz,7z,bz2,cab,iso,doc,docx,xls,xlsx,ppt,pptx,pdf,txt,md,xml,xmind',1,date('Y-m-d H:i:s'),date('Y-m-d H:i:s')],
        ]);
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
