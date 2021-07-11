$(function () {
    const $closeForm = $('#close-form');
    const $removeImagesForm = $('#removeImage-form');
    const $leaveComment = $('#leave-comment');
    const $cancelComment = $('#cancel-comment');
    const $createCommentForm = $('#create-comment-form');
    const $commentsWrapper = $('#comments-wrapper');
    const $commentCount = $('#comment-count');

    /* ********** Listeners **********  */


    /* ***** Buttons ***** */

    // Listen on close ticket form
    $closeForm.submit(ev => {
        ev.preventDefault();

        $.ajax({
            method: $closeForm.attr('method'),
            url: $closeForm.attr('action'),
            data: $closeForm.serializeArray(),
            success: function (res) {
                if (res.success) {
                    $closeForm.addClass('display-none');
                }
            }
        });
    });

    // Listen on delete images form
    $removeImagesForm.submit(ev => {
        ev.preventDefault();

        $.ajax({
            method: $removeImagesForm.attr('method'),
            url: $removeImagesForm.attr('action'),
            data: $removeImagesForm.serializeArray(),
            success: function (res) {
                if (res.success) {
                    $removeImagesForm.addClass('display-none');
                }
            }
        });
    });

    /* ***** Comments ***** */

    // Initialize every comment with the listeners
    initComments();

    // Listen on comment form section
    $leaveComment.click(formExpand);

    // When user cancels textarea input, it shrinks and clears
    $cancelComment.click(resetForm);

    // Listen on create comment
    $createCommentForm.submit(ev => {
        ev.preventDefault();

        // Ajax call when user presses create on comment form
        $.ajax({
            method: $createCommentForm.attr('method'),  // method is passed on (POST)
            url: $createCommentForm.attr('action'),
            data: $createCommentForm.serializeArray(),
            success: function (res) {
                if (res.success) {
                    $commentsWrapper.prepend(res.comment);
                    resetForm();    // clear form after creation
                    $commentCount.text(parseInt($commentCount.text()) + 1); // instant refresh on comment counter, without reload
                    const $firstComment = $commentsWrapper.find('.comment-item').eq(0);
                    initComment($firstComment); // append listeners on new comment without reload
                }
            }
        });
    });

    // Reset form window when user clicks away and no text has been entered yet
    $(window).click(ev => {
        const $target = $(ev.target);

        if (!$target.closest($leaveComment).length && !$leaveComment.val()) {
            resetForm();
        }
    });

    /* ********** Functions **********  */

    /**
     * Initializer for each comment
     * Append every listener on all comments, so everything is listened
     *
     * @param $comment
     */
    function initComment($comment) {
        const $cancel = $comment.find('.button-cancel');
        const $edit = $comment.find('.item-edit-comment');
        const $delete = $comment.find('.item-delete-comment');
        const $form = $comment.find('.comment-edit-section');
        const $textWrapper = $comment.find('.text-wrapper');
        const $input = $comment.find('textarea');

        // Reset form if form is cancelled
        $cancel.on('click', () => {
            $comment.removeClass('edit');
        });

        // Expand form when clicked
        $edit.on('click', ev => {
            ev.preventDefault();
            $comment.addClass('edit');
            $input.val($textWrapper.text().trim());
        });

        // Delete comment on delete pressed
        $delete.on('click', ev => {
            ev.preventDefault();

            // Ajax call on delete button
            $.ajax({
                method: 'post',
                url: $delete.attr('href'),
                success: function (res) {
                    if (res.success) {
                        $commentCount.text(parseInt($commentCount.text()) - 1); // decrease comment counter momentarily, without reload
                        $delete.closest('.comment-item').remove();  // remove item from DOM
                    }
                }
            });

        });

        // Handle on editing comment
        $form.on('submit', ev => {
            ev.preventDefault();

            // Ajax call when user edits comment
            $.ajax({
                method: $form.attr('method'),
                url: $form.attr('action'),
                data: $form.serializeArray(),
                success: function (res) {
                    if (res.success) {
                        $comment.removeClass('edit');   // removes textarea from DOM
                        $textWrapper.text($input.val());
                        const $div = $('<div>');
                        $div.html(res.comment);
                        const $newComment = $div.find('>div');
                        $comment.replaceWith($newComment);  // removes old comment and add new one
                        initComment($newComment);   // adds listeners on "new" (edited) comment
                    }
                }
            });
        });
    }

    /**
     * Initializes all comments with listeners
     */
    function initComments() {
        const $comments = $('.comment-item');
        $comments.each((ind, comment) => {
            const $comment = $(comment);
            initComment($comment);
        });
    }

    /**
     * Expands the add new comment textarea
     */
    function formExpand() {
        $leaveComment
            .attr('rows', '2')
            .closest('.create-comment')
            .addClass('focused');
    }

    /**
     * Resets the add new comment textarea to the minimal unfocused form
     */
    function resetForm() {
        $leaveComment
            .attr('rows', '1')
            .val('');
        $cancelComment
            .closest('.create-comment')
            .removeClass('focused');
    }
});
