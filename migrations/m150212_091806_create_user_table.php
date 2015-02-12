<?php

use yii\db\Schema;
use yii\db\Migration;

class m150212_091806_create_user_table extends Migration
{
    public function up()
    {
        $this->createTable('user', [
            'id' => 'pk',
            'username' => Schema::TYPE_STRING . ' NOT NULL',
            'password' => Schema::TYPE_STRING . ' NOT NULL',
            'authKey' => Schema::TYPE_STRING,
            'accessToken' => Schema::TYPE_STRING,
        ]);
        $this->insert('user', [
            'username' => 'admin',
            'password' => 'admin',
            'authKey' => 'test100key',
            'accessToken' => '100-token',
        ]);
        $this->insert('user', [
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
