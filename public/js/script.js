const slider = document.querySelector('.slider');
const list = document.querySelector('.list');
const thumbnails = document.querySelector('.thumbnail');
const next = document.querySelector('#next');
const prev = document.querySelector('#prev');


// auto play
let runAutoPlay = setTimeout(() =>{
    next.click();
}, 7000);


next.addEventListener('click', () => {
initSlider('next');
});

prev.addEventListener('click', () => {
initSlider('prev');
});

const initSlider = (type) => {
    const sliderItems = list.querySelectorAll('.item');
    const thumbnailItems = thumbnails.querySelectorAll('.item');

if (type === 'next') {
    list.appendChild(sliderItems[0]);
    thumbnails.appendChild(thumbnailItems[0]);
    slider.classList.add('next');
}
else {
    const lastItemPosition = sliderItems.length - 1;
    list.prepend(sliderItems[lastItemPosition]);
    thumbnails.prepend(thumbnailItems[lastItemPosition]);
    slider.classList.add('prev');
}

setTimeout(() => {
    slider.classList.remove('next')
    slider.classList.remove('prev');
}, 2000);

clearTimeout(runAutoPlay);
runAutoPlay = setTimeout(() =>{
    next.click();
}, 7000);
};

