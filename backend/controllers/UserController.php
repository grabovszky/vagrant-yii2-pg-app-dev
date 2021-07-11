<?php

namespace backend\controllers;

use common\models\Ticket;
use common\models\User;
use Throwable;
use Yii;
use yii\base\Exception;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

/**
 * UserController implements the CRUD actions for User user.
 */
class UserController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all User models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider(
            [
                'query' => User::find()->regularUsers(),
            ]
        );

        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
            ]
        );
    }

    /**
     * Displays a single User user.
     *
     * @param int $id
     *
     * @return string|Response
     */
    public function actionView($id)
    {
        $user = $this->findModel($id);

        if (!isset($user->id)) {
            Yii::warning('Warning: user not found');
            Yii::$app->session->setFlash('error', 'Warning: user not found');
            return $this->redirect(['index']);
        }

        if ($user->is_admin === true) {
            Yii::warning('Warning: cannot edit an amin');
            Yii::$app->session->setFlash('error', 'Warning: cannot edit an admin');
            return $this->redirect(['index']);
        }

        $dataProvider = new ActiveDataProvider(
            [
                'query' => Ticket::find()->creator($id)->sortByStatus(),
            ]
        );

        return $this->render(
            'view',
            [
                'user' => $user,
                'dataProvider' => $dataProvider,
            ]
        );
    }

    /**
     * Updates an existing User user.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $id
     *
     * @return string|Response
     */
    public function actionUpdate($id)
    {
        $user = $this->findModel($id);

        if (!isset($user->id)) {
            Yii::warning('Warning: user not found');
            Yii::$app->session->setFlash('error', 'Warning: user not found');
            return $this->redirect(['index']);
        }

        if ($user->is_admin === true) {
            Yii::warning('Warning: cannot edit an amin');
            Yii::$app->session->setFlash('error', 'Warning: cannot edit an admin');
            return $this->redirect(['index']);
        }

        try {
            if ($user->load(Yii::$app->request->post()) && $user->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } catch (Exception $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'Error while updating user: #' . $id);
            return Response::$httpStatuses[400];
        }

        return $this->render(
            'update',
            [
                'model' => $user,
            ]
        );
    }

    /**
     * Deletes an existing User user.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param int $id
     *
     * @return Response
     */
    public function actionDelete($id)
    {
        try {
            $this->findModel($id)->delete();
        } catch (Exception $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'Error while deleting user: #' . $id);
            return Response::$httpStatuses[400];
        } catch (Throwable $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'Error while deleting user: #' . $id);
            return Response::$httpStatuses[400];
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the User user based on its primary key value.
     * If the user is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id
     *
     * @return User the loaded user
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        Yii::error('Error: user: #' . $id . ' model not found');
        Yii::$app->session->setFlash('error', 'Error: user #' . $id . ' model not found');
        return Response::$httpStatuses[404];
    }
}
