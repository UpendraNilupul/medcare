let menu = document.querySelector('#menu-btn');
let navbar = document.querySelector('.navbar');

menu.onclick = () => {
    menu.classList.toggle('fa-times');
    navbar.classList.toggle('active');
}

window.onscroll = () => {
    menu.classList.remove('fa-times');
    navbar.classList.remove('active');
}

// Dynamic home image rotation
let images = [
    "image/home-img.svg",
    "image/about-img.svg",
    "image/book-img.svg"
];

let index = 0;
let homeImg = document.getElementById("dynamic-home-img");

setInterval(() => {
    homeImg.style.opacity = 0; // fade out
    setTimeout(() => {
        index = (index + 1) % images.length;
        homeImg.src = images[index];
        homeImg.style.opacity = 1; // fade in
    }, 1000); // wait for fade-out
}, 3000); // change every 3 seconds
