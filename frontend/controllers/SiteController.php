<?php

namespace frontend\controllers;

use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResendVerificationEmailForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\VerifyEmailForm;
use Yii;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\captcha\CaptchaAction;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ErrorAction;
use yii\web\Response;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
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
                'class' => ErrorAction::class,
            ],
            'captcha' => [
                'class' => CaptchaAction::class,
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
        return $this->render('index');
    }

    /**
     * Logs in a user.
     *
     * @return string|Response
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        try {
            if ($model->load(Yii::$app->request->post()) && $model->login()) {
                return $this->goBack();
            }
        } catch (Exception $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'Error while logging in.');
            return Response::$httpStatuses[400];
        }

        $model->password = '';

        return $this->render(
            'login',
            [
                'model' => $model,
            ]
        );
    }

    /**
     * Logs out the current user.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays profile page.
     *
     * @return string
     */
    public function actionProfile()
    {
        $user = Yii::$app->user->identity;
        return $this->render(
            'profile',
            [
                'user' => $user,
            ]
        );
    }

    /**
     * Updates the user account.
     *
     * @return string
     */
    public function actionUpdateAccount()
    {
        $user = Yii::$app->user->identity;
        $success = false;
        $transaction = Yii::$app->db->beginTransaction();

        try {
            if ($user->load(Yii::$app->request->post()) && $user->save()) {
                $success = true;
                $transaction->commit();
            }
        } catch (\Exception $e) {
            $success = false;
            $transaction->rollBack();
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'Error while updating account.');
        }

        return $this->renderAjax(
            'user_account',
            [
                'user' => $user,
                'success' => $success,
            ]
        );
    }

    /**
     * Signs user up.
     *
     * @return Response|string
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        $transaction = Yii::$app->db->beginTransaction();

        try {
            if ($model->load(Yii::$app->request->post()) && $model->signup()) {
                Yii::$app->session->setFlash(
                    'success',
                    'Thank you for registration. Please check your inbox for verification email.'
                );

                $transaction->commit();
                return $this->goHome();
            }
        } catch (Exception $e) {
            $transaction->rollBack();
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'Error while signing up.');
            return Response::$httpStatuses[400];
        }

        return $this->render(
            'signup',
            [
                'model' => $model,
            ]
        );
    }

    /**
     * Requests password reset.
     *
     * @return Response|string
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            try {
                if ($model->sendEmail()) {
                    Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                    return $this->goHome();
                }
            } catch (Exception $e) {
                Yii::error($e, __METHOD__);
                Yii::$app->session->setFlash('error', 'Error while sending password reset email.');
                return Response::$httpStatuses[400];
            }

            Yii::$app->session->setFlash(
                'error',
                'Sorry, we are unable to reset password for the provided email address.'
            );
        }

        return $this->render(
            'requestPasswordResetToken',
            [
                'model' => $model,
            ]
        );
    }

    /**
     * Resets password.
     *
     * @param string $token
     *
     * @return Response|string
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'Error while resetting password with token.');
            return Response::$httpStatuses[400];
        }

        $transaciton = Yii::$app->db->beginTransaction();
        try {
            if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
                Yii::$app->session->setFlash('success', 'New password saved.');

                $transaciton->commit();
                return $this->goHome();
            }
        } catch (Exception $e) {
            $transaciton->rollBack();
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'Error while resetting password.');
            return Response::$httpStatuses[400];
        }

        return $this->render(
            'resetPassword',
            [
                'model' => $model,
            ]
        );
    }

    /**
     * Verify email address
     *
     * @param string $token
     *
     * @return Response
     */
    public function actionVerifyEmail($token)
    {
        try {
            $model = new VerifyEmailForm($token);
        } catch (InvalidArgumentException $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'Error while verifying email with token.');
            return Response::$httpStatuses[400];
        }
        try {
            if ($user = $model->verifyEmail()) {
                if (Yii::$app->user->login($user)) {
                    Yii::$app->session->setFlash('success', 'Your email has been confirmed!');
                    return $this->goHome();
                }
            }
        } catch (Exception $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'Error while verifying email.');
            return Response::$httpStatuses[400];
        }

        Yii::$app->session->setFlash('error', 'Sorry, we are unable to verify your account with provided token.');
        return $this->goHome();
    }

    /**
     * Resend verification email
     *
     * @return Response|string
     */
    public function actionResendVerificationEmail()
    {
        $model = new ResendVerificationEmailForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
                return $this->goHome();
            }
            Yii::$app->session->setFlash(
                'error',
                'Sorry, we are unable to resend verification email for the provided email address.'
            );
        }

        return $this->render(
            'resendVerificationEmail',
            [
                'model' => $model,
            ]
        );
    }
}
