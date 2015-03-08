<?php
namespace app\controllers;

use fproject\rest\ActiveController;

class UserController extends ActiveController{
    public $modelClass = 'app\models\User';
}