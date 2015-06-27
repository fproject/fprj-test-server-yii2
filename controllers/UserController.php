<?php
namespace app\controllers;

use fproject\rest\ActiveController;
use yii\db\ActiveQuery;

class UserController extends ActiveController{
    public $modelClass = 'app\models\User';

    /**
     * Condition to find all resources with relations.
     * Use '@findAllCondition' as the key for client-side condition
     * @param array $params
     * @return ActiveQuery
     */
    public function findAllCondition($params)
    {
        /** @var ActiveQuery $query */
        $query = \Yii::createObject(ActiveQuery::className(), [$this->modelClass]);

        $query->joinWith([
            'profile'=> function ($q) {
                /** @var ActiveQuery $q */
                $q->select('id,phone');
            },
            'department',
        ]);

        return $query;
    }
}