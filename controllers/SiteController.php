<?php

namespace app\controllers;

use Yii;
use app\models\Levels;
use app\models\ContactForm;
use yii\filters\AccessControl;
use yii\web\Controller;

use app\models\Personal;

use mdm\admin\models\Assignment;
use yii\web\Session;
use yii\web\Response;
use mdm\admin\models\User;
use app\models\Profile;
use mdm\admin\models\searchs\User as UserSearch;

use yii\filters\VerbFilter;

use app\models\LoginForm as Login;
use app\models\Signup;

use mdm\admin\models\form\PasswordResetRequest;
use mdm\admin\models\form\ResetPassword;

use yii\web\NotFoundHttpException;
use yii\base\UserException;
use yii\mail\BaseMailer;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
		if (Yii::$app->getUser()->isGuest) {
		    return $this->redirect(['login']);
//			$model = new Login();
//			if ($model->load(Yii::$app->getRequest()->post()) && $model->login()) {
//				return $this->goBack();
//			} else {
//				return $this->render('login', [
//					'model' => $model,
//				]);
//			}
		}else{
            return $this->render('index');
		}
        
    }

    /**
     * Login action.
     *
     * @return Response|string
     */

//    public function actionLogin()
//    {
//        if (!Yii::$app->getUser()->isGuest) {
//            return $this->goHome();
//        }
//
//        $model = new Login();
//        if ($model->load(Yii::$app->getRequest()->post()) && $model->login()) {
//            return $this->goBack();
//        } else {
//            return $this->render('login', [
//                'model' => $model,
//            ]);
//        }
//    }

    function univerLogin($data, $check = 0){

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_COOKIESESSION, true);
        curl_setopt($curl, CURLOPT_COOKIEFILE, $_SERVER['DOCUMENT_ROOT'].'/web/cookie/'.$data['login'].'.txt');
        curl_setopt($curl, CURLOPT_COOKIEJAR, $_SERVER['DOCUMENT_ROOT'].'/web/cookie/'.$data['login'].'.txt' );
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.0; ru; rv:1.9.1.3) Gecko/20090824 Firefox/3.5.3');

        $post = [
            'login' => $data['login'],
            'password' => $data['password'],
        ];

        curl_setopt($curl, CURLOPT_URL, $data['url_login']);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
        $html = curl_exec($curl);

        curl_setopt($curl, CURLOPT_URL, $data['url_stud']);
        $html = curl_exec($curl);

        $html = json_decode($html,true);
        //var_dump($html);die;
        if($html["code"] == 0){
            $data = array_merge($html["data"]["0"], $html["data"]["1"]);
            $arrays = array_values($data); // массив массивов данных Data

            for($i=0;count($arrays)>$i;$i++){
                $values[$i] = array_values($arrays[$i])[0];
                $keys[$i] = array_keys($arrays[$i])[0];
            }

            $array = array_combine($keys, $values);
            $object = (object)$array;
            return $object;
        }else{
            return false;
        }
    }

    public function actionLogin()
    {
        if (!Yii::$app->getUser()->isGuest) {
            return $this->goHome();
        }

        $model = new Login();

        if ($model->load(Yii::$app->getRequest()->post())) {

            $auth_data = [
                "login"     =>  $model->username,
                "password"  =>  $model->password,
                "url_login"  =>  'https://univerapi.kstu.kz/user/login',
                "url_stud"  =>  'https://univerapi.kstu.kz/student/profile/ru-RU',
            ];

            // получили пользователя
            $user = $model->getUser($model->username);
            #var_dump(in_array ('curl', get_loaded_extensions()));die;
            if ($user == NULL){

                //есть ли доступ в универ
                $uCheck = $this->univerLogin($auth_data);

                #var_dump($uCheck);die;

                if($uCheck) {


                    // регистрация в системе
                    $model1 = new Signup();
                    $model1->username = $model->username;
                    $model1->password = $model->password;
                    $model1->retypePassword = $model->password;
                    $model1->email = $model->username . "@kstu.kz";
//                    $model1->status = 10;
//                    $signup = $model1->signup();
                    #$id = $model1->id;
//                    var_dump($model1->email);die();
                    // сохраняем персональные данные студента
//                    $profile = new Profile();
                    if ($model1->signup()){
                        $signup = $model1->signup();
                        $profile = new Profile();
    //                    var_dump($profile);die;
                        $profile->fname = str_replace(":", "", stristr($uCheck->fname, ':'));
                        $profile->name = str_replace(":", "", stristr($uCheck->name, ':'));
                        $profile->sname = str_replace(":", "", stristr($uCheck->sname, ':'));
                        $profile->profile_type_id = 1;
                        $profile->faculty_id = str_replace(":", "", stristr($uCheck->faculty, ':'));
                        $profile->edu_form_id = str_replace(":", "", stristr($uCheck->edu_form, ':'));
                        $profile->edu_level_id = str_replace(":", "", stristr($uCheck->edu_level, ':'));
                        $profile->lang_id = str_replace(":", "", stristr($uCheck->lang_div, ':'));
                        $profile->stage_id = str_replace(":", "", stristr($uCheck->stage, ':'));
                        $profile->course_num = str_replace(":", "", stristr($uCheck->course_num, ':'));
                        $profile->sex_id = str_replace(":", "", stristr($uCheck->sex, ':'));
                        $profile->student_id = str_replace(":", "", stristr($uCheck->studentId, ':'));
                        $profile->user_id = $signup->id;
                        $profile->speciality_id = str_replace(":", "", stristr($uCheck->speciality, ':'));
                        $profile->save(false);

                        $model2 = new Login();
                        // авторизация в системе, редирект
                        $model2->load(Yii::$app->getRequest()->post());

                        if ($model2->login()) {
                            $profile_id = Profile::find()->where(['user_id'=>Yii::$app->user->id])->one();

                            $this->Assign(Yii::$app->user->id);

                            Yii::$app->session->set('profileId', $profile_id->id);
                            return $this->redirect(['site/login']);
                        }
                    }else{
                        var_dump('$model2->username');die();
                    }


                }else{
                    // НЕТ такого пользователя в системе UNIVER
                    Yii::$app->session->setFlash('warning', 'Ошибка. Логин или Пароль введены не верно');
                    return $this->redirect(['site/login']);
                    #var_dump();die();
                }

            }else{
                if($user && $user->validatePassword($model->password) == true){

                    // авторизация после регистрации в системе
                    if ($model->login()) {
                        $profile_id = Profile::find()->where(['user_id'=>Yii::$app->user->id])->one();
                        Yii::$app->session->set('profileId', $profile_id->id);
                        return $this->redirect(['site/login']);
                    }

                }else{
                    $uCheck = $this->univerLogin($auth_data);
                    if($uCheck){
                        // обновляем пароль в системе на новый из UNIVER
                        $user->setPassword($model->password);
                        $user->generateAuthKey();
                        if ($user->save()) {
                            return $this->redirect(['site/login']);
                        }
                    }else {
                        // НЕТ такого пользователя в системе UNIVER
                        Yii::$app->session->setFlash('warning', 'Ошибка, такой логин или пароль не найдены - UNIVER');
                        return $this->redirect(['site/login']);
                        #var_dump();die();
                    }                }
            }

        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }


    public function Assign($id)
    {
        $items = [Yii::$app->params['USER_GROUP']];
        $model = new Assignment($id);
        $model->assign($items);
    }

//    public function actionSignup()
//    {
//        $model = new Signup();
//		//$personal = new Personal();
//		//$lvl = Levels::find()->where(['type_id'=>2])->all();
//		#var_dump($_POST);die();
//
//        if ($model->load(Yii::$app->getRequest()->post())) {
//           # $model->person_id = (int)$personal->id;
//            #$model->save();
//            #$model->save();
//            #$model->id;
//
//            if ($user = $model->signup()) {
//                #var_dump($user->id);die();
//                    return $this->goHome();
//                }
//        }
//        return $this->render('signup', [
//            'model' => $model,
//        ]);
//
//    }
    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Request reset password
     * @return string
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequest();
        if ($model->load(Yii::$app->getRequest()->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->getSession()->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->getSession()->setFlash('error', 'Sorry, we are unable to reset password for email provided.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Reset password
     * @return string
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPassword($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->getRequest()->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->getSession()->setFlash('success', 'New password was saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }


    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionXui()
    {
        if (!Yii::$app->getUser()->isGuest) {
            return $this->goHome();
        }

        $model = new Login();
        if ($model->load(Yii::$app->getRequest()->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }


    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }


}
