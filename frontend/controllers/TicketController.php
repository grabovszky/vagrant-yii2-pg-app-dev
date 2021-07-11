<?php

namespace frontend\controllers;

use common\models\Comment;
use common\models\Ticket;
use Throwable;
use Yii;
use yii\base\ErrorException;
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
        // Only allow logged in users to list tickets
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
     * Only list tickets owned by the user
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider(
            [
                // First filter for the owner, than sort by status and update date
                'query' => Ticket::find()->creator(Yii::$app->user->id)->sortByStatus(),
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
     * @return string|Response
     */
    public function actionView($id)
    {
        $ticket = $this->findModel($id);

        if (!isset($ticket->created_by)) {
            Yii::warning('Warning: cannot find ticket: #' . $id);
            Yii::$app->session->setFlash('error', 'Warning: cannot find ticket: #' . $id);
            return $this->redirect(['index']);
        }

        if (Yii::$app->user->id !== $ticket->created_by) {
            Yii::$app->session->setFlash('error', 'You are not authorized to view that ticket');
            return $this->redirect(['index']);
        }

        $comments = Comment::find()->with(['createdBy'])->ticketId($id)->latest()->all();

        return $this->render(
            'view',
            [
                'model' => $ticket,
                'comments' => $comments,
            ]
        );
    }

    /**
     * Generates ticket image carousel items
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
                    str_replace('/var/www/html/ticketing-system-template/frontend/web', '', $imageFile),
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
     * Finds the Ticket model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
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

        Yii::error('Error: cannot find ticket: #' . $id);
        Yii::$app->session->setFlash('error', 'Error: cannot find ticket: #' . $id);
        return Response::$httpStatuses[404];
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
        $transaction = Yii::$app->db->beginTransaction();

        try {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                $transaction->commit();
                return $this->redirect(['view', 'id' => $model->ticket_id]);
            }
        } catch (Exception $e) {
            $transaction->rollBack();
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'Error while saving ticket.');
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
     * @return string|Response
     */
    public function actionUpdate($id)
    {
        $ticket = $this->findModel($id);

        if (!isset($ticket->created_by)) {
            Yii::warning('Warning: cannot find ticket: #' . $id);
            Yii::$app->session->setFlash('error', 'Warning: cannot find ticket: #' . $id);
            return $this->redirect(['index']);
        }

        if (Yii::$app->user->id !== $ticket->created_by) {
            Yii::$app->session->setFlash('error', 'You are not authorized to edit that ticket');
            return $this->redirect(['index']);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($ticket->load(Yii::$app->request->post()) && $ticket->save()) {
                $transaction->commit();
                return $this->redirect(['view', 'id' => $ticket->ticket_id]);
            }
        } catch (Exception $e) {
            $transaction->rollBack();
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'Error while updating ticket: #' . $id);
            return Response::$httpStatuses[400];
        }

        return $this->render(
            'update',
            [
                'model' => $ticket,
            ]
        );
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
        $transaction = Yii::$app->db->beginTransaction();

        try {
            return $ticket->save();
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'Error while opening ticket: #' . $id);
            return Response::$httpStatuses[400];
        }
    }

    /**
     * Sets the ticket status to closed
     *
     * @param $id
     *
     * @return Response
     */
    public function actionClose($id)
    {
        $model = $this->findModel($id);
        $model->status = false;
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $model->save();
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'Error while closing ticket: #' . $id);
            return Response::$httpStatuses[400];
        }
        
        return $this->redirect(['view', 'id' => $model->ticket_id]);
    }

    /**
     * Deletes all images
     *
     * @param $id
     *
     * @return Response
     */
    public function actionRemove($id)
    {
        $model = $this->findModel($id);

        try {
            $model->deleteImgs();
        } catch (ErrorException $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'Error while deleting images of ticket: #' . $id);
            return Response::$httpStatuses[400];
        }

        return $this->redirect(['view', 'id' => $model->ticket_id]);
    }

    /**
     * Deletes an existing Ticket model.
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
            Yii::$app->session->setFlash('error', 'Error while deleting ticket: #' . $id);
            return Response::$httpStatuses[400];
        } catch (Throwable $e) {
            YYii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'Error while deleting ticket: #' . $id);
            return Response::$httpStatuses[400];
        }

        return $this->redirect(['index']);
    }
}
