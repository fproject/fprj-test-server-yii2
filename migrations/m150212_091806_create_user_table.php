<?php

use yii\db\Schema;
use yii\db\Migration;

class m150212_091806_create_user_table extends Migration
{
    public function up()
    {
        $this->createTable('user_profile', [
            'id' => 'pk',
            'email' => Schema::TYPE_STRING,
            'phone' => Schema::TYPE_STRING ,
        ]);

        $this->createTable('user', [
            'id' => 'pk',
            'profileId' => "int(11)",
            'username' => Schema::TYPE_STRING . ' NOT NULL',
            'password' => Schema::TYPE_STRING . ' NOT NULL',
            'authKey' => Schema::TYPE_STRING,
            'accessToken' => Schema::TYPE_STRING,
        ]);

        $this->addForeignKey('fk_user_profile','user','profileId','user_profile','id');

        $this->insert('user_profile', [
            'id'=>1,
            'email' => 'admin@fproject.net',
            'phone' => '0123456789',
        ]);

        $this->insert('user_profile', [
            'id'=>2,
            'email' => 'demo@fproject.net',
            'phone' => '9876543210',
        ]);

        $this->insert('user', [
            'profileId'=>1,
            'username' => 'admin',
            'password' => 'admin',
            'authKey' => 'test100key',
            'accessToken' => '100-token',
        ]);
        $this->insert('user', [
            'profileId'=>2,
            'username' => 'demo',
            'password' => 'demo',
            'authKey' => 'test101key',
            'accessToken' => '101-token',
        ]);
    }

    public function down()
    {
        echo "m150212_091806_create_user_table cannot be reverted.\n";

        return false;
    }
}
