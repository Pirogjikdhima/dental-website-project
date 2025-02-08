document.addEventListener("DOMContentLoaded", function () {
    const rememberToken = getCookie("remember_token");
    const loggedOut = getCookie("logged_out") === "true";

    if (!loggedOut && rememberToken) {
        console.log("Remember token found, sending AJAX request...");
        $.ajax({
            url: "./connection.php",
            type: "POST",
            data: {
                action: "check_remember_token",
                rememberToken: rememberToken,
            },
            dataType: "json",
            success: function (response) {
                console.log("AJAX response:", response);
                if (response.success) {
                    console.log("Redirecting to", response.location);
                    window.location.href = response.location;
                } else {
                    console.warn("Invalid token:", response.message);
                    deleteCookie("remember_token");
                }
            },
            error: function (status, error) {
                console.error("AJAX error:", status, error);
            }
        });
    } else {
        console.log("No remember token or logged_out flag set to true.");
    }

    function getCookie(name) {
        const match = document.cookie.match(new RegExp("(^| )" + name + "=([^;]+)"));
        return match ? match[2] : null;
    }

    function deleteCookie(name) {
        document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    }

    function sendVerificationCode(email) {
        $.ajax({
            url: "./sendVerificationCode.php",
            type: "POST",
            data: {email},
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    iziToast.success({
                        title: 'Success',
                        message: 'Verification code sent to your email!',
                        position: 'topCenter',
                        timeout: 3000,
                        backgroundColor: '#7066e0',
                        titleColor: '#FFFFFF',
                        messageColor: '#FFFFFF',
                        pauseOnHover: false,
                    });

                    document.getElementById("forgotPasswordModal").style.display = "none";
                    document.getElementById("verificationCodeModal").style.display = "flex";
                } else {
                    iziToast.error({
                        title: 'Error',
                        message: response.message || "Failed to send the verification code. Please try again.",
                        position: 'topCenter',
                        timeout: 3000,
                        backgroundColor: '#7066e0',
                        titleColor: '#FFFFFF',
                        messageColor: '#FFFFFF',
                        pauseOnHover: false,
                    });

                }
            },
            error: function (status, error) {
                console.error("Failed to send verification code:", status, error);
                iziToast.error({
                    title: 'Error',
                    message: "An error occurred while sending the verification code. Please try again.",
                    position: 'topCenter',
                    timeout: 3000,
                    backgroundColor: '#7066e0',
                    titleColor: '#FFFFFF',
                    messageColor: '#FFFFFF',
                    pauseOnHover: false,
                });

            },
        });
    }

    const emailRegex = /^[a-zA-Z0-9.-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    const phoneRegex = /^\+?[0-9]{1,14}$/;
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&.*])[A-Za-z\d!@#$%^&.*]{6,}$/;

    const loginButton = document.getElementById("loginButton");
    const emailOrPhoneInput = document.getElementById("loginEmailOrPhone");
    const forgotPasswordLink = document.querySelector(".forgot-password");
    const rememberMeCheckbox = document.querySelector(".form-checkbox");
    const leftSection = document.querySelector(".left-section");

    emailOrPhoneInput.addEventListener("input", function () {
        loginButton.disabled = false;
        loginButton.style.backgroundColor = "";
        loginButton.style.cursor = "pointer";
        loginButton.textContent = "Login";
        document.getElementById("accountLockedError").textContent = "";

        leftSection.style.border = "";
        leftSection.style.boxShadow = "";

        forgotPasswordLink.style.display = "inline";
        rememberMeCheckbox.style.display = "flex";
    });

    const savedEmailOrPhone = localStorage.getItem("loginEmailOrPhone");
    const savedPassword = localStorage.getItem("loginPassword");
    const rememberMeChecked = localStorage.getItem("rememberMeChecked");

    if (rememberMeChecked === "true") {
        if (savedEmailOrPhone) {
            document.getElementById("loginEmailOrPhone").value = savedEmailOrPhone;
        }
        if (savedPassword) {
            document.getElementById("loginPassword").value = savedPassword;
        }
        document.getElementById("rememberMe").checked = true;
    }

    document.getElementById("loginButton").addEventListener("click", function (e) {
        e.preventDefault();

        const emailOrPhone = document.getElementById("loginEmailOrPhone").value;
        const password = document.getElementById("loginPassword").value;
        const rememberMe = document.getElementById("rememberMe").checked;
        let isValid = true;

        document.getElementById("loginEmailPhoneError").textContent = "";
        document.getElementById("loginPasswordError").textContent = "";
        document.getElementById("accountLockedError").textContent = "";

        if (!emailRegex.test(emailOrPhone) && !phoneRegex.test(emailOrPhone)) {
            document.getElementById("loginEmailPhoneError").textContent =
                "Please enter a valid email or phone number.";
            isValid = false;
        }
        if (!passwordRegex.test(password)) {
            document.getElementById("loginPasswordError").textContent =
                "Password must contain at least 6 characters, including one uppercase letter, one lowercase letter, one number, and one special character.";
            isValid = false;
        }
        if (!isValid) {
            return;
        }

        $.ajax({
            url: "./connection.php",
            type: "POST",
            data: {
                action: "login",
                emailOrPhone: emailOrPhone,
                password: password,
                rememberMe: rememberMe
            },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    if (rememberMe) {
                        localStorage.setItem("loginEmailOrPhone", emailOrPhone);
                        localStorage.setItem("loginPassword", password);
                        localStorage.setItem("rememberMeChecked", "true");
                    } else {
                        localStorage.removeItem("loginEmailOrPhone");
                        localStorage.removeItem("loginPassword");
                        localStorage.removeItem("rememberMeChecked");
                    }
                    iziToast.success({
                        title: 'Success',
                        message: 'Login successful!',
                        position: 'topCenter',
                        timeout: 1000,
                        backgroundColor: '#7066e0',
                        titleColor: '#FFFFFF',
                        messageColor: '#FFFFFF',
                        pauseOnHover: false,
                        onClosing: function() {
                            window.location.href = response.location;
                        }
                    });
                } else {
                    if (response.message.emailOrPhone) {
                        document.getElementById("loginEmailPhoneError").textContent =
                            response.message.emailOrPhone || "";
                    }
                    if (response.message.password) {
                        document.getElementById("loginPasswordError").textContent =
                            response.message.password || "";
                    }
                    if (response.message.accountLocked) {
                        document.getElementById("accountLockedError").textContent =
                            response.message.accountLocked || "";

                        loginButton.disabled = true;
                        loginButton.style.backgroundColor = "#d9534f";
                        loginButton.style.cursor = "not-allowed";
                        loginButton.textContent = "Account Locked";
                        forgotPasswordLink.style.display = "none";
                        rememberMeCheckbox.style.display = "none";

                        leftSection.style.border = "2px solid #d9534f";
                        leftSection.style.boxShadow = "0 0 8px rgba(217, 83, 79, 0.8)";
                    }
                }
            },
            error: function (status, error) {
                console.error("AJAX error:", status, error);
            },
        });
    });

    document.querySelector(".forgot-password").addEventListener("click", function (e) {
        e.preventDefault();
        document.getElementById("forgotPasswordModal").style.display = "flex";
    });

    document.getElementById("closeForgotPasswordModal").addEventListener("click", function () {
        document.getElementById("forgotPasswordModal").style.display = "none";
    });

    document.getElementById("verifyEmailButton").addEventListener("click", function () {
        const email = document.getElementById("forgotEmail").value;
        const emailError = document.getElementById("forgotEmailError");
        emailError.textContent = "";

        if (!emailRegex.test(email)) {
            emailError.textContent = "Please enter a valid email.";
            return;
        }

        $.ajax({
            url: "./checkEmail.php",
            type: "POST",
            data: {email},
            dataType: "json",
            success: function (response) {
                if (response.message === "Email is already registered.") {
                    sendVerificationCode(email);
                } else if (response.message === "Email is available.") {
                    emailError.textContent = "This email is not registered. Please enter a valid email.";
                } else {
                    emailError.textContent = response.message || "An error occurred. Please try again.";
                }
            },
            error: function (status, error) {
                emailError.textContent = "An error occurred while processing your request. Please try again.";
                console.error("AJAX error:", status, error);
            },
        });
    });

    document.getElementById("verifyCodeButton").addEventListener("click", function () {
        const code = document.getElementById("verificationCode").value;
        const codeError = document.getElementById("verificationCodeError");
        codeError.textContent = "";

        if (!code) {
            codeError.textContent = "Please enter the verification code.";
            return;
        }

        $.ajax({
            url: "./verifyCode.php",
            type: "POST",
            data: {code},
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    iziToast.success({
                        title: 'Success',
                        message: 'Code verified successfully!',
                        position: 'topCenter',
                        timeout: 3000,
                        backgroundColor: '#7066e0',
                        titleColor: '#FFFFFF',
                        messageColor: '#FFFFFF',
                        pauseOnHover: false,
                    });
                    document.getElementById("verificationCodeModal").style.display = "none";
                    document.getElementById("resetPasswordModal").style.display = "flex";
                } else {
                    codeError.textContent = response.message || "Invalid verification code.";
                }
            },
            error: function () {
                codeError.textContent = "An error occurred. Please try again.";
            },
        });
    });

    document.getElementById("resetPasswordButton").addEventListener("click", function (e) {
        e.preventDefault();

        const newPassword = document.getElementById("newPassword").value;
        const confirmNewPassword = document.getElementById("confirmNewPassword").value;
        const newPasswordError = document.getElementById("newPasswordError");
        const confirmPasswordError = document.getElementById("confirmPasswordError");
        const email = document.getElementById("forgotEmail").value;

        newPasswordError.textContent = "";
        confirmPasswordError.textContent = "";

        let isValid = true;
        if (!newPassword || !confirmNewPassword) {
            newPasswordError.textContent = "All fields are required.";
            isValid = false;
        }
        if (newPassword !== confirmNewPassword) {
            confirmPasswordError.textContent = "Passwords do not match.";
            isValid = false;
        }
        if (!passwordRegex.test(newPassword)) {
            newPasswordError.textContent =
                "Password must contain at least 6 characters, including one uppercase letter, one lowercase letter, one number, and one special character.";
            isValid = false;
        }
        if (!isValid) return;

        $.ajax({
            url: "./connection.php",
            type: "POST",
            data: {
                action: "resetPassword",
                email: email,
                password: newPassword,
                confirmPassword: confirmNewPassword
            },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    iziToast.success({
                        title: 'Success',
                        message: 'Password reset successfully!',
                        position: 'topCenter',
                        timeout: 3000,
                        backgroundColor: '#7066e0',
                        titleColor: '#FFFFFF',
                        messageColor: '#FFFFFF',
                        pauseOnHover: false,
                    });
                    window.location.href = "./signin.html";

                } else {
                    newPasswordError.textContent = response.message || "Failed to reset password.";
                }
            },
            error: function (status, error) {
                console.error("AJAX Error:", status, error);
                newPasswordError.textContent = "An error occurred: " + (xhr.responseText || "Please try again later.");
            },
        });
    });

    document.getElementById("closeVerificationCodeModal").addEventListener("click", function () {
        document.getElementById("verificationCodeModal").style.display = "none";
    });

    document.getElementById("closeResetPasswordModal").addEventListener("click", function () {
        document.getElementById("resetPasswordModal").style.display = "none";

    })
})