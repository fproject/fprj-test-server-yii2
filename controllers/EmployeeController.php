<?php
namespace app\controllers;

use fproject\rest\ActiveController;
use yii\db\ActiveQuery;

class EmployeeController extends ActiveController
{
    public $modelClass = 'app\models\Employee';

    public $useSecureSearch = false;
}