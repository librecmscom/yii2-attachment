<?php

namespace yuncms\attachment\migrations;

use yii\db\Migration;

class M170109093051Create_attachment_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%attachment}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->unsigned()->comment('用户ID'),
            'filename' => $this->string(255)->notNull()->comment('文件名'),
            'original_name'=> $this->string(255)->notNull()->comment('文件原始名称'),
            'size' => $this->integer()->defaultValue(0)->comment('字节数'),
            'type' => $this->string(255)->notNull()->comment('文件类型'),
            'path' => $this->string(255)->comment('存储路径'),
            'ip' => $this->string(255)->notNull()->comment('上传者IP'),
            'created_at' => $this->integer()->unsigned()->notNull()->comment('上传时间'),
        ], $tableOptions);
        $this->addForeignKey('ibfk_1', '{{%attachment}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%attachment}}');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
