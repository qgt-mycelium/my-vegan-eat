// Reply
document.addEventListener('DOMContentLoaded', function() {
    const replyComments = document.querySelectorAll('.reply-comment');
    replyComments.forEach(replyComment => {
        replyComment.addEventListener('click', function() {
            const commentId = replyComment.getAttribute('data-comment-id');
            const url = replyComment.getAttribute('data-url');

            var xhr = new XMLHttpRequest();
            xhr.open('GET', url, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.onreadystatechange = function () {
                if (xhr.readyState !== 4) return;

                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    
                    document.getElementById('reply-comment-' + commentId).innerHTML = response.form;
                    replyComment.classList.add('hidden');

                } else {
                    console.error('HTTP error!', xhr.status, xhr.statusText);
                }
            };
            xhr.send();
        });
    });
});