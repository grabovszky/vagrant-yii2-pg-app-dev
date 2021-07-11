<?php

namespace backend\controllers;

use common\models\Comment;
use common\models\Ticket;
use Throwable;
use Yii;
use yii\base\Exception;
use yii\data\ActiveDataProvider;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\Response;

/**
 * TicketController implements the CRUD actions for Ticket model.
 */
class TicketController extends Controller
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
     * Lists all Ticket models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider(
            [
                'query' => Ticket::find()->sortByStatus(),
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
     * Displays a single Ticket model.
     *
     * @param string $id
     *
     * @return string
     */
    public function actionView($id)
    {
        $comments = Comment::find()->with(['createdBy'])->ticketId($id)->latest()->all();

        return $this->render(
            'view',
            [
                'model' => $this->findModel($id),
                'comments' => $comments,
            ]
        );
    }

    /**
     * Creates a new Ticket model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return string|Response
     */
    public function actionCreate()
    {
        $model = new Ticket();

        try {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->ticket_id]);
            }
        } catch (Exception $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'Error while creating ticket: : #' . $model->ticket_id);
            return Response::$httpStatuses[400];
        }

        return $this->render(
            'create',
            [
                'model' => $model,
            ]
        );
    }

    /**
     * Updates an existing Ticket model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param string $id
     *
     * @return Response|string
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        try {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->ticket_id]);
            }
        } catch (Exception $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'Error while updating ticket: : #' . $model->ticket_id);
            return Response::$httpStatuses[400];
        }

        return $this->render(
            'update',
            [
                'model' => $model,
            ]
        );
    }

    /**
     * Assigns admin to ticket
     *
     * @param $id
     * @param $userId
     *
     * @return Response
     */
    public function actionAssign($id, $userId)
    {
        $model = $this->findModel($id);

        $model->assigned_admin_id = $userId;
        try {
            $model->save();
        } catch (Exception $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash(
                'error',
                'Error while assigning ticket: : #' . $model->ticket_id . ' to admin #' . $userId
            );
            return Response::$httpStatuses[400];
        }

        return $this->redirect(['view', 'id' => $model->ticket_id]);
    }

    /**
     * Closes ticket
     *
     * @param $id
     *
     * @return Response
     */
    public function actionClose($id)
    {
        $model = $this->findModel($id);

        $model->status = false;
        try {
            $model->save();
        } catch (Exception $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'Error while closing ticket: : #' . $id);
            return Response::$httpStatuses[400];
        }

        return $this->redirect(['view', 'id' => $model->ticket_id]);
    }

    /**
     * Sets the ticket status to open
     *
     * @param $id
     *
     * @return bool
     */
    public static function openStatus($id)
    {
        $ticket = Ticket::find()->andWhere(['ticket_id' => $id])->one();
        $ticket->setTicketStatus(true);

        try {
            return $ticket->save();
        } catch (Exception $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'Error while opening ticket: : #' . $id);
            return Response::$httpStatuses[400];
        }
    }

    /**
     * Deletes an existing Ticket user.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param string $id
     *
     * @return Response
     */
    public function actionDelete($id)
    {
        try {
            $this->findModel($id)->delete();
        } catch (StaleObjectException $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'Error while deleting ticket: : #' . $id);
            return Response::$httpStatuses[400];
        } catch (Throwable $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'Error while deleting ticket: : #' . $id);
            return Response::$httpStatuses[400];
        }

        return $this->redirect(['index']);
    }

    /**
     * Generates carousel array items to display ticket images
     *
     * @param $id
     *
     * @return array
     */
    public static function generateCarouselItems($id)
    {
        $ticket = Ticket::find()->andWhere(['ticket_id' => $id])->one();
        $basePath = Yii::getAlias('@frontend/web/storage/ticketImages/' . $ticket->ticket_id . '/');
        try {
            $imageFiles = FileHelper::findFiles($basePath);
        } catch (\Exception $e) {
            return [];
        }

        $carouselItems = [];

        foreach ($imageFiles as $imageFile) {
            $carouselItems[] = [
                'content' => Html::img(
                    str_replace(
                        '/var/www/html/ticketing-system-template/frontend/web/',
                        'http://ticketing-system.test/',
                        $imageFile
                    ),
                    [
                        'alt' => 'Image not found',
                        'height' => '300px',
                        'class' => 'mx-auto d-block',
                    ]
                ),
                'options' => ['class' => 'img-fluid img-thumbnail'],
            ];
        }

        return $carouselItems;
    }

    /**
     * Finds the Ticket model based on its primary key value..
     *
     * @param string $id
     *
     * @return Ticket the loaded model
     */
    protected function findModel($id)
    {
        if (($model = Ticket::findOne($id)) !== null) {
            return $model;
        }

        Yii::error('Error: ticket: #' . $id . ' model not found');
        Yii::$app->session->setFlash('error', 'Error: ticket #' . $id . ' model not found');
        return Response::$httpStatuses[404];
    }
}
