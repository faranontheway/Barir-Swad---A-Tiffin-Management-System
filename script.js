const container = document.querySelector(".container");
const registerButton = document.querySelector(".register-btn");
const loginButton = document.querySelector(".login-btn");

registerButton.addEventListener("click", () => {
    container.classList.add("active");
});

loginButton.addEventListener("click", () => {
    container.classList.remove("active");
});