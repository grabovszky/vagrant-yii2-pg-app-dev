<?php

namespace backend\controllers;

use common\models\Comment;
use Throwable;
use Yii;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

class CommentController extends Controller
{

    /**
     * Allow access behaviors for the comment section
     *
     * @return array[]
     */
    public function behaviors()
    {
        return [
            // Only allow logged in users to comment
            'access' => [
                'class' => AccessControl::class,
                'only' => ['create'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'content' => [
                'class' => ContentNegotiator::class,
                'only' => ['create', 'update', 'delete'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            // Only allow POST requests
            'verb' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Handle comment creation
     * Saves new comment, and returns it as a view to be displayed
     *
     * @return array
     */
    public function actionCreate()
    {
        $comment = new Comment();

        if ($comment->load(Yii::$app->request->post(), '') && $comment->save()) {
            TicketController::openStatus($comment->ticket_id);
            $comment = Comment::find()->with(['createdBy'])->ticketId($comment->ticket_id)->one();

            return [
                'success' => true,
                // returns only a partial to render without applying a whole layout
                'comment' => $this->renderPartial(
                    '@frontend/views/ticket/_comment_item',
                    [
                        'model' => $comment,
                    ]
                ),
            ];
        }

        return [
            'success' => false,
            'errors' => $comment->errors,
        ];
    }

    /**
     * Handle comment update
     * Saves updated comment, and returns it as a view to be displayed
     *
     * @param $id
     *
     * @return array|bool[]
     */
    public function actionUpdate($id)
    {
        $comment = $this->findModel($id);

        if ($comment->belongsTo(Yii::$app->user->id)) {
            $commentText = Yii::$app->request->post('comment');
            $comment->comment = $commentText;

            if ($comment->save()) {
                return [
                    'success' => true,
                    // returns only a partial to render without applying a whole layout
                    'comment' => $this->renderPartial(
                        '@frontend/views/ticket/_comment_item',
                        [
                            'model' => $comment,
                        ]
                    ),
                ];
            }

            return [
                'success' => true,
                'errors' => $comment->errors,
            ];
        }

        Yii::error('Error while updating comment: #' . $id . ' update not allowed');
        Yii::$app->session->setFlash('error', 'Error while updating comment: #' . $id . ' update not allowed');
        return Response::$httpStatuses[403];
    }

    /**
     * Handle comment delete
     * Deletes comment
     *
     * @param $id
     *
     * @return bool[]
     */
    public function actionDelete($id)
    {
        $comment = $this->findModel($id);
        if ($comment->belongsTo(Yii::$app->user->id)) {
            try {
                $comment->delete();
            } catch (StaleObjectException $e) {
                Yii::error($e, __METHOD__);
                Yii::$app->session->setFlash('error', 'Error while deleting comment: #' . $id);
                return Response::$httpStatuses[400];
            } catch (Throwable $e) {
                Yii::error($e, __METHOD__);
                Yii::$app->session->setFlash('error', 'Error while deleting comment: #' . $id);
                return Response::$httpStatuses[400];
            }

            return ['success' => true];
        }

        Yii::error('Error while deleting comment: #' . $id . ' deletion not allowed');
        Yii::$app->session->setFlash('error', 'Error while deleting comment: #' . $id . ' deletion not allowed');
        return Response::$httpStatuses[403];
    }

    /**
     * Searches for the model with id
     *
     * @param $id
     *
     * @return Comment
     */
    protected function findModel($id)
    {
        $comment = Comment::findOne($id);

        if (!$comment) {
            Yii::error('Error comment: : #' . $id . ' not found.');
            Yii::$app->session->setFlash('error', 'Error comment: : #' . $id . ' not found.');
            return Response::$httpStatuses[404];
        }

        return $comment;
    }
}