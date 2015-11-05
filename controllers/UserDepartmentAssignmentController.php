<?php
namespace app\controllers;

use fproject\rest\ActiveController;
use yii\db\ActiveQuery;

class UserDepartmentAssignmentController extends ActiveController
{
    public $modelClass = 'app\models\base\UserDepartmentAssignment';
}