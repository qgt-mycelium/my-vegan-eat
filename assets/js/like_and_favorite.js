document.addEventListener('DOMContentLoaded', function() {
    const likeElements = document.querySelectorAll('button[data-action="like"]');
    const favoriteElements = document.querySelectorAll('button[data-action="favorite"]');

    initToggleButtonsAction(likeElements);
    initToggleButtonsAction(favoriteElements);
});

function initToggleButtonsAction(elems) 
{
    elems.forEach(elem => {
        elem.addEventListener('click', function () {
            const url = this.dataset.url;
            var xhr = new XMLHttpRequest();
            xhr.open('POST', url, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.onreadystatechange = function () {
                if (xhr.readyState !== 4) return;

                if (xhr.status === 200) {
                    const count = JSON.parse(xhr.responseText);
                    elem.querySelector('span').innerHTML = count;

                    const thumbsUpFilled = elem.querySelector('svg.filled');
                    const thumbsUpUnfilled = elem.querySelector('svg.unfilled');

                    thumbsUpFilled.classList.toggle('hidden');
                    thumbsUpUnfilled.classList.toggle('hidden');

                } else {
                    console.error('HTTP error!', xhr.status, xhr.statusText);
                }
            };
            xhr.send();
        });
    });
}