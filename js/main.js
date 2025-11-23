import { initializeApp } from "https://www.gstatic.com/firebasejs/12.6.0/firebase-app.js";
import { getAuth, GoogleAuthProvider, signInWithPopup } from "https://www.gstatic.com/firebasejs/12.6.0/firebase-auth.js";

const firebaseConfig = {
    apiKey: "AIzaSyBqX_64SPfoT40st2gywj7PRvFDK7KgOZI",
    authDomain: "elparqueaderola64-88430.firebaseapp.com",
    projectId: "elparqueaderola64-88430",
    storageBucket: "elparqueaderola64-88430.firebasestorage.app",
    messagingSenderId: "167467764960",
    appId: "1:167467764960:web:d78e16bad8868e35ea9485"
};

const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
auth.languageCode = "en";
const provider = new GoogleAuthProvider();

const googleLogin = document.getElementById("google-login-btn");

googleLogin.addEventListener("click", function () {
    signInWithPopup(auth, provider)
        .then((result) => {
            const user = result.user;

            const userData = {
                firebaseUid: user.uid,
                email: user.email,
                displayName: user.displayName
            };

            console.log("Sending user data:", userData);

            // Enviar los datos del usuario al backend
            return fetch("../php/save_user.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(userData)
            });
        })
        .then(response => response.json())
        .then(data => {
            console.log("Server response:", data);
            window.location.href = "../html/vistaUsuario.html";
        })
        .catch((error) => {
            console.error("Login error:", error);
        });
});
