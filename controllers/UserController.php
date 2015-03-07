<?php
namespace app\controllers;

use app\components\rest\ActiveController;

class UserController extends ActiveController{
    public $modelClass = 'app\models\User';
}