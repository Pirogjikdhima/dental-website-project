document.addEventListener("DOMContentLoaded", function () {
    const nameRegex = /^[a-zA-Z\s]+$/;
    const emailRegex = /^[a-zA-Z0-9.-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    const phoneRegex = /^\+?[0-9]{1,14}$/;
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*\.])[A-Za-z\d!@#$%^&*\.]{6,}$/;

    window.closeModal = () => {
        const modal = document.getElementById("verificationModal");
        modal.style.display = "none";
        modal.style.visibility = "hidden";
        modal.style.opacity = "0";
    };
    const showModal = () => {
        const modal = document.getElementById("verificationModal");
        modal.style.display = "flex";
        modal.style.visibility = "visible";
        modal.style.opacity = "1";
    };

    document.querySelector(".modal-confirm").addEventListener("click", function () {
        const verificationCode = document.querySelector(".modal-input").value;

        $.ajax({
            type: "POST",
            url: "./verifyCode.php",
            data: {code: verificationCode},
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    iziToast.success({
                        title: 'Success',
                        message: 'Code verified successfully!',
                        position: 'topCenter',
                        timeout: 1000,
                        backgroundColor: '#7066e0',
                        titleColor: '#FFFFFF',
                        messageColor: '#FFFFFF',
                        pauseOnHover: false,
                        onClosing: function () {
                            registerUser();
                        }
                    });

                } else {
                    document.querySelector(".modal .error").innerText = response.message;
                }
            },
            error: function () {
                iziToast.error({
                    title: 'Error',
                    message: 'Verification failed. Please try again.',
                    position: 'topRight',
                    timeout: 1200,
                    pauseOnHover: false,
                });

            }
        });
    });

    const registerUser = () => {
        const name = document.getElementById("name").value.trim();
        const lastName = document.getElementById("lastName").value.trim();
        const email = document.getElementById("email").value.trim();
        const phone = document.getElementById("phone").value.trim();
        const gender = document.getElementById("gender").value;
        const password = document.getElementById("password").value.trim();
        const newsletterConsent = document.getElementById("newsletterConsent").checked;

        $.ajax({
            type: "POST",
            url: "./connection.php",
            data: {name, lastName, email, phone, gender, password, newsletterConsent, action: "register"},
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    iziToast.success({
                        title: 'Success',
                        message: 'Code verified successfully!',
                        position: 'topCenter',
                        timeout: 1000,
                        backgroundColor: '#7066e0',
                        titleColor: '#FFFFFF',
                        messageColor: '#FFFFFF',
                        pauseOnHover: false,
                        onClosing: function () {
                            window.location.href = response.location || "signin.html";
                        }
                    });
                } else {
                    document.getElementById("nameError").innerText = response.message.name || "";
                    document.getElementById("lastNameError").innerText = response.message.lastName || "";
                    document.getElementById("emailError").innerText = response.message.email || "";
                    document.getElementById("phoneError").innerText = response.message.phone || "";
                    document.getElementById("genderError").innerText = response.message.gender || "";
                    document.getElementById("passwordError").innerText = response.message.password || "";
                    document.getElementById("confirmPasswordError").innerText = response.message.confirmPassword || "";
                }
            },
            error: function (xhr, status, error) {
                let errorMessage = "Registration failed. ";

                if (xhr.responseText) {
                    try {
                        const jsonResponse = JSON.parse(xhr.responseText);
                        errorMessage += jsonResponse.message || "Unexpected server response.";
                    } catch (e) {
                        errorMessage += "Invalid JSON response from server.";
                    }
                } else {
                    errorMessage += "No response received from server.";
                }
                console.error("AJAX Error Details:", {
                    status: status,
                    error: error,
                    xhr: xhr,
                    responseText: xhr.responseText,
                    responseStatus: xhr.status,
                    responseStatusText: xhr.statusText
                });
                alert(errorMessage);
            }
        });
    };

    const checkEmailExists = (email) => {
        return $.ajax({
            type: "POST",
            url: "./checkEmail.php",
            data: {email},
            dataType: "json",
        });
    };

    const sendVerificationCode = async () => {
        const email = document.getElementById("email").value.trim();

        try {
            const response = await checkEmailExists(email);
            if (!response.success) {
                iziToast.error({
                    title: 'Error',
                    message: response.message,
                    position: 'topCenter',
                    timeout: 3000,
                    backgroundColor: '#7066e0',
                    titleColor: '#FFFFFF',
                    messageColor: '#FFFFFF',
                    pauseOnHover: false,
                });
                return;
            }

            $.ajax({
                type: "POST",
                url: "./sendVerificationCode.php",
                data: {email},
                dataType: "json",
                success: function (response) {
                    if (response.success) {
                        showModal();
                    } else {
                        iziToast.error({
                            title: 'Error',
                            message: response.message,
                            position: 'topCenter',
                            timeout: 3000,
                            backgroundColor: '#7066e0',
                            titleColor: '#FFFFFF',
                            messageColor: '#FFFFFF',
                            pauseOnHover: false,
                        });
                    }
                },
                error: function (xhr) {
                    console.error("Error:", xhr.responseText);
                    iziToast.error({
                        title: 'Error',
                        message: "Failed to send verification email. Please try again.",
                        position: 'topCenter',
                        timeout: 3000,
                        backgroundColor: '#7066e0',
                        titleColor: '#FFFFFF',
                        messageColor: '#FFFFFF',
                        pauseOnHover: false,
                    });
                }
            });
        } catch (error) {
            iziToast.error({
                title: 'Error',
                message: "An error occurred. Please try again.",
                position: 'topCenter',
                timeout: 3000,
                backgroundColor: '#7066e0',
                titleColor: '#FFFFFF',
                messageColor: '#FFFFFF',
                pauseOnHover: false,
            });
            console.error(error);
        }
    };

    document.getElementById("signupButton").addEventListener("click", function (event) {
        event.preventDefault();
        const valid = validateForm();
        if (valid) {
            sendVerificationCode();
        } else {
            console.log("Form validation failed");
        }
    });

    const validateForm = () => {
        let valid = true;
        const name = document.getElementById("name").value.trim();
        const lastName = document.getElementById("lastName").value.trim();
        const email = document.getElementById("email").value.trim();
        const phone = document.getElementById("phone").value.trim();
        const gender = document.getElementById("gender").value;
        const password = document.getElementById("password").value.trim();
        const confirmPassword = document.getElementById("confirmPassword").value.trim();


        document.getElementById("nameError").innerText = "";
        document.getElementById("lastNameError").innerText = "";
        document.getElementById("emailError").innerText = "";
        document.getElementById("phoneError").innerText = "";
        document.getElementById("genderError").innerText = "";
        document.getElementById("passwordError").innerText = "";
        document.getElementById("confirmPasswordError").innerText = "";

        if (!name) {
            document.getElementById("nameError").innerText = "First Name is required!";
            valid = false;
        } else if (!nameRegex.test(name)) {
            document.getElementById("nameError").innerText = "First Name must only contain letters and spaces!";
            valid = false;
        }

        if (!lastName) {
            document.getElementById("lastNameError").innerText = "Last Name is required!";
            valid = false;
        } else if (!nameRegex.test(lastName)) {
            document.getElementById("lastNameError").innerText = "Last Name must only contain letters and spaces!";
            valid = false;
        }

        if (!email) {
            document.getElementById("emailError").innerText = "Email is required!";
            valid = false;
        } else if (!emailRegex.test(email)) {
            document.getElementById("emailError").innerText = "Invalid email address!";
            valid = false;
        }

        if (phone && !phoneRegex.test(phone)) {
            document.getElementById("phoneError").innerText = "Invalid phone number!";
            valid = false;
        }

        if (!gender) {
            document.getElementById("genderError").innerText = "Please select a gender!";
            valid = false;
        }

        if (!password) {
            document.getElementById("passwordError").innerText = "Password is required!";
            valid = false;
        } else if (!passwordRegex.test(password)) {
            document.getElementById("passwordError").innerText = "Password must be at least 6 characters long, include uppercase, lowercase, a number, and a special character!";
            valid = false;
        }

        if (!confirmPassword) {
            document.getElementById("confirmPasswordError").innerText = "Please confirm your password!";
            valid = false;
        } else if (password !== confirmPassword) {
            document.getElementById("confirmPasswordError").innerText = "Passwords do not match!";
            valid = false;
        }

        return valid;
    };
});