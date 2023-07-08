document.addEventListener('DOMContentLoaded', function() {
    const likeElements = document.querySelectorAll('button[data-action="like"]');
    likeElements.forEach(likeElement => {
        likeElement.addEventListener('click', function () {
            const url = this.dataset.url;
            var xhr = new XMLHttpRequest();
            xhr.open('POST', url, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.onreadystatechange = function () {
                if (xhr.readyState !== 4) return;

                if (xhr.status === 200) {
                    const likeCount = JSON.parse(xhr.responseText);
                    likeElement.querySelector('span').innerHTML = likeCount;

                    console.log(likeElement);

                    const thumbsUpFilled = likeElement.querySelector('svg.filled');
                    const thumbsUpUnfilled = likeElement.querySelector('svg.unfilled');

                    thumbsUpFilled.classList.toggle('hidden');
                    thumbsUpUnfilled.classList.toggle('hidden');

                } else {
                    console.error('HTTP error!', xhr.status, xhr.statusText);
                }
            };
            xhr.send();
        });
    });
});