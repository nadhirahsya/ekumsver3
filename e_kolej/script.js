//Login Form Validation 

function validateLoginForm(event){
    event.preventDefault();

    const user_id = document.querySelector('input[type="user_id').value;
    const password = document.querySelector('input[type="password').value;

    if(!user_id){
        alert('Please enter your user id');
        return false;
    }


}

document.getElementById("logoutButton").addEventListener("click", function () {
    window.location.href = "home.html";
});
