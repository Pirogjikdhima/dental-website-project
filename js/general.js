const userRole = document.getElementById('user-role').value.trim();

if (userRole === "ADMIN") {
    const adminButtons = [
        document.getElementById("info-btn"),
        document.getElementById("dashboard-btn"),
        document.getElementById("newsletter-btn"),
        document.getElementById("password-btn"),
        document.getElementById("booking-btn"),
        document.getElementById("reviews-btn")
    ];
    const adminSections = [
        document.getElementById("info-section"),
        document.getElementById("dashboard-section"),
        document.getElementById("newsletter-section"),
        document.getElementById("change-password-section"),
        document.getElementById("booking-section"),
        document.getElementById("reviews-section"),
        document.getElementById("welcome-section")
    ];

    initializeTabs(adminButtons, adminSections);

    document.getElementById("products-btn").addEventListener('click', function () {
        window.location.href = "./edit_shopitems.php";
    })

    document.getElementById("services-btn").addEventListener('click', function () {
        window.location.href = "./editservices.php";
    })
    document.getElementById('newsletterForm').addEventListener('submit', function(event) {
        newsletter(event);
    });

    $("#dashboard-btn").on("click", function () {
        fetch("./profile/admin/dashboard.php", {
            method: "GET", headers: {
                "Content-Type": "application/json",
            },
        })
            .then((response) => response.json())
            .then((responseData) => {
                if (responseData.success) {
                    const sampleData = responseData.data;

                    $("#usersTable").DataTable({
                        destroy: true,
                        data: sampleData,
                        columns: [{data: "user_id"}, {data: "first_name"}, {data: "last_name"}, {data: "email"}, {data: "role"}, {
                            data: "user_id", render: function (data) {
                                return `
                                <button class="action-btn edit-btn" data-id="${data}">Edit</button>
                                <button class="action-btn delete-btn" data-id="${data}">Delete</button>
                            `;
                            },
                        },],
                        lengthChange: false,
                        pageLength: 8,
                        order: [[0, "desc"]],
                        responsive: true,
                        language: {
                            search: "Search users:",
                            lengthMenu: "Show _MENU_ users per page",
                            info: "Showing _START_ to _END_ of _TOTAL_ users",
                            emptyTable: "No users found",
                        },
                        columnDefs: [{
                            targets: -1, orderable: false, searchable: false,
                        },],
                    });
                } else {
                    iziToast.error({
                        title: 'Error',
                        message: `Error: ${responseData.message}`,
                        position: 'topCenter',
                        timeout: false,
                        pauseOnHover: false,
                    });
                }
            })
            .catch((error) => {
                iziToast.error({
                    title: 'Error',
                    message: `Error fetching users: ${error}`,
                    position: 'topCenter',
                    timeout: false,
                    pauseOnHover: false,
                });
            });
    });

    $("#booking-btn").on("click", function () {
        fetch("./profile/admin/get_booking.php", {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
            },
        })
            .then((response) => response.json())
            .then((responseData) => {
                if (responseData.success) {
                    const bookingData = responseData.data;

                    $("#bookingTable").DataTable({
                        destroy: true,
                        data: bookingData,
                        columns: [
                            { data: "booking_id" },
                            { data: "service" },
                            { data: "name" },
                            { data: "user_email" },
                            { data: "date" },
                            { data: "time" },
                            {
                                data: "booking_id",
                                render: function (data) {
                                    return `
                            <button class="action-btn delete-booking-btn" data-id="${data}">Delete</button>
                        `;
                                },
                            },
                        ],
                        lengthChange: false,
                        pageLength: 8,
                        order: [[0, "desc"]],
                        responsive: true,
                        language: {
                            search: "Search bookings:",
                            lengthMenu: "Show _MENU_ bookings per page",
                            info: "Showing _START_ to _END_ of _TOTAL_ bookings",
                            emptyTable: "No bookings found",
                        },
                        columnDefs: [
                            {
                                targets: -1,
                                orderable: false,
                                searchable: false,
                            },
                        ],
                    });
                } else {
                    iziToast.error({
                        title: 'Error',
                        message: `Error: ${responseData.message}`,
                        position: 'topCenter',
                        timeout: false,
                        pauseOnHover: false,
                    });
                }
            })
            .catch((error) => {
                iziToast.error({
                    title: 'Error',
                    message: 'Failed to fetch booking data',
                    position: 'topCenter',
                    timeout: false,
                    pauseOnHover: false,
                });
            });
    });

    $("#reviews-btn").on("click", function () {
        fetch("./profile/admin/review_table.php", {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
            },
        })
            .then((response) => response.json())
            .then((responseData) => {
                if (responseData.success) {
                    const reviewData = responseData.data;

                    $("#reviewsTable").DataTable({
                        destroy: true,
                        data: reviewData,
                        columns: [
                            { data: "full_name" },
                            { data: "email" },
                            {
                                data: "rating",
                                render: function (data) {
                                    return '⭐'.repeat(data);
                                }
                            },
                            { data: "comment" },
                            {
                                data: "created_at",
                                render: function (data) {
                                    return new Date(data).toLocaleDateString();
                                }
                            },
                            {
                                data: "review_id",
                                render: function (data) {
                                    return `
                                    <button class="action-btn edit-btn-review" data-id="${data}">
                                        Edit
                                    </button>
                                    <button class="action-btn delete-btn-review" data-id="${data}">
                                        Delete
                                    </button>
                                `;
                                }
                            }
                        ],
                        lengthChange: false,
                        pageLength: 5,
                        order: [[4, "desc"]],
                        responsive: true,
                        language: {
                            search: "Search reviews:",
                            lengthMenu: "Show _MENU_ reviews per page",
                            info: "Showing _START_ to _END_ of _TOTAL_ reviews",
                            emptyTable: "No reviews found"
                        },
                        columnDefs: [
                            {
                                targets: -1,
                                orderable: false,
                                searchable: false,
                            },
                        ],
                    });
                } else {
                    iziToast.error({
                        title: 'Error',
                        message: `Error: ${responseData.message}`,
                        position: 'topCenter',
                        timeout: false,
                        pauseOnHover: false,
                    });
                }
            })
            .catch((error) => {
                iziToast.error({
                    title: 'Error',
                    message: 'Failed to fetch reviews',
                    position: 'topCenter',
                    timeout: false,
                    pauseOnHover: false,
                });
            });
    });

    $(document).on("click", ".edit-btn", function () {
        let userData;
        const userId = $(this).data("id");

        fetch(`./profile/admin/edit_user.php?id=${userId}`, {
            method: "GET", headers: {
                "Content-Type": "application/json",
            },
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    const user = data.data;
                    Swal.fire({
                        title: 'Edit User Information', html: `
                        <form id="edit-user-form" style="text-align: left;">
                        
                            <div class="form-group">
                                <label for="profile-picture">Profile Picture</label>
                                <input type="file" id="profile-picture" name="profile-picture" accept="image/*">
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="first_name">First Name</label>
                                    <input type="text" id="first_name" name="first_name" value="${user.first_name}" required style="width: 100%; padding: 10px; margin-bottom: 10px;">
                                    <span id="first_name-error" class="error-message"></span>
                                </div>

                                <div class="form-group">
                                    <label for="last_name">Last Name</label>
                                    <input type="text" id="last_name" name="last_name" value="${user.last_name}" required style="width: 100%; padding: 10px; margin-bottom: 10px;">
                                    <span id="last_name-error" class="error-message"></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="user_email" name="email" value="${user.email}" required style="width: 100%; padding: 10px; margin-bottom: 10px;">
                                <span id="user_email-error" class="error-message"></span>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input type="tel" id="user_phone" name="phone" value="${user.phone}" style="width: 100%; padding: 10px; margin-bottom: 10px;">
                                    <span id="user_phone-error" class="error-message"></span>
                                </div>

                                <div class="form-group">
                                    <label for="gender">Gender</label>
                                    <select id="gender" name="gender" style="width: 100%; padding: 10px; margin-bottom: 10px;">
                                        <option value="male" ${user.gender === 'male' ? 'selected' : ''}>Male</option>
                                        <option value="female" ${user.gender === 'female' ? 'selected' : ''}>Female</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="role">Role</label>
                                    <select id="role" name="role" style="width: 100%; padding: 10px; margin-bottom: 10px;">
                                        <option value="ADMIN" ${user.role === 'ADMIN' ? 'selected' : ''}>Admin</option>
                                        <option value="USER" ${user.role === 'USER' ? 'selected' : ''}>User</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    `, showCancelButton: true, confirmButtonText: 'Update', preConfirm: () => {
                            const form = document.getElementById('edit-user-form');
                            const formData = new FormData(form);
                            formData.append("user_id", user.user_id);

                            let isNameValid = nameCheck('first_name');
                            let isSurnameValid = nameCheck('last_name');
                            let isEmailValid = emailCheck();
                            let isPhoneValid = phoneCheck();

                            if (!form.checkValidity() || !isNameValid || !isPhoneValid || !isEmailValid || !isSurnameValid) {
                                form.reportValidity();
                                return false;
                            }

                            return fetch('./profile/admin/edit_user.php', {
                                method: 'POST', body: formData,
                            })
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error("Response"+response.statusText);
                                    }
                                    return response.json();
                                })
                                .then(data => {
                                    if (!data.success) {
                                        throw new Error("Data"+data.message);
                                    }
                                    userData = data;
                                    return data;
                                })
                                .catch(error => {
                                    Swal.showValidationMessage(`Request failed: ${error}`);
                                });
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Success', text: 'User information updated successfully.', icon: 'success'
                            }).then(() => {

                                if (userData.location){
                                    window.location.href = userData.location;
                                }
                                const table = $('#usersTable').DataTable();
                                const row = table.row($(`button[data-id="${userId}"]`).parents('tr'));
                                const updatedUser = {
                                    user_id: userData.user_id,
                                    first_name: userData.first_name,
                                    last_name: userData.last_name,
                                    email: userData.email,
                                    role: userData.role
                                };
                                row.data(updatedUser).draw(false);

                            });
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            Swal.fire('Cancelled', 'User data is safe :)', 'info');
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Error!', text: "Error in retrieving the data", icon: 'error'
                    });
                }
            })
            .catch((error) => {
                Swal.fire({
                    title: 'Error!', text: error.message, icon: 'error'
                });
            });
    });

    $(document).on("click", ".delete-btn", function () {
        const userId = $(this).data("id");

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel!',
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'deleteUser');
                formData.append('user_id', userId);

                fetch("./profile/admin/delete_user.php", {
                    method: 'POST', body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Deleted!', text: 'User has been deleted.', icon: 'success'
                            }).then(() => {
                                if (data.location){
                                    window.location.href = data.location;
                                }
                                const table = $('#usersTable').DataTable();
                                table.row($(`button[data-id="${userId}"]`).parents('tr')).remove().draw(false);
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!', text: data.message, icon: 'error'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error!', text: 'An error occurred while deleting the user.', icon: 'error'
                        });
                    });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                Swal.fire('Cancelled', 'User was not deleted.', 'info');
            }
        });
    });

    $(".add-btn").on("click", function () {
        let userData;
        Swal.fire({
            title: 'Add New User', html: `
        <form id="add-user-form" style="text-align: left;">
        
        <input type="hidden" name="action" value="addUser">
            <div class="form-group" style="display: flex; gap: 10px; margin-bottom: 10px;">
                <div style="flex: 1;">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" required style="width: 100%; padding: 10px;">
                    <span id="first_name-error" class="error-message"></span>
                </div>
                <div style="flex: 1;">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required style="width: 100%; padding: 10px;">
                    <span id="last_name-error" class="error-message"></span>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="user_email" name="email" required style="width: 100%; padding: 10px; margin-bottom: 10px;">
                <span id="user_email-error" class="error-message"></span>
            </div>

            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="tel" id="user_phone" name="phone" style="width: 100%; padding: 10px; margin-bottom: 10px;">
                <span id="user_phone-error" class="error-message"></span>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="user_password" name="password" required style="width: 100%; padding: 10px; margin-bottom: 10px;">
                <span id="user_password-error" class="error-message"></span>
            </div>

            <div class="form-group" style="display: flex; gap: 10px; margin-bottom: 10px;">
                <div style="flex: 1;">
                    <label for="role">Role</label>
                    <select id="role" name="role" required style="width: 100%; padding: 10px; margin-bottom: 10px;">
                        <option value="USER">User</option>
                        <option value="ADMIN">Admin</option>
                    </select>
                </div>
                <div style="flex: 1;">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" required style="width: 100%; padding: 10px; margin-bottom: 10px;">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
            </div>
        </form>

    `,
            showCancelButton: true,
            confirmButtonText: 'Add',
            preConfirm: () => {
                const form = document.getElementById('add-user-form');

                let isNameValid = nameCheck("first_name");
                let isSurnameValid = nameCheck("last_name")
                let isPhoneValid = phoneCheck();
                let isEmailValid = emailCheck();
                let isPasswordValid = passwordCheck();

                if (!form.checkValidity() || !isNameValid || !isPhoneValid || !isEmailValid || !isPasswordValid || !isSurnameValid) {
                    form.reportValidity();
                    return false;
                }

                const formData = new FormData(form);

                return fetch('./connection.php', {
                    method: 'POST', body: formData,
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (!data.success) {
                            throw new Error(data.message || "Failed to add user.");
                        }
                        userData = data;
                        return data;
                    })
                    .catch(error => {
                        Swal.showValidationMessage(`Request failed: ${error.message}`);
                    });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Success', text: 'User added successfully.', icon: 'success',
                }).then(() => {
                    const table = $('#usersTable').DataTable();
                    const newUser = {
                        user_id: userData.user_id,
                        first_name: userData.first_name,
                        last_name: userData.last_name,
                        email: userData.email,
                        role: userData.role
                    };
                    table.row.add(newUser).draw(false);
                });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                Swal.fire('Cancelled', 'User not added. ', 'info');
            }
        });
    });

    $(document).on("click", ".delete-booking-btn", function () {
        const bookingId = $(this).data("id");

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel!',
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'deleteBooking');
                formData.append('booking_id', bookingId);

                fetch("./profile/admin/delete_booking.php", {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: 'Booking has been deleted.',
                                icon: 'success',
                            }).then(() => {
                                const table = $('#bookingTable').DataTable();
                                table.row($(`button[data-id="${bookingId}"]`).parents('tr')).remove().draw(false);
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message,
                                icon: 'error',
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while deleting the booking.',
                            icon: 'error',
                        });
                    });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                Swal.fire('Cancelled', 'Booking was not deleted.', 'error');
            }
        });
    });

    $(document).on('click', '.edit-btn-review', function () {
        const reviewId = $(this).data('id');
        let updatedData = "";

        fetch(`./profile/admin/review_table.php?id=${reviewId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const review = data.data;

                    Swal.fire({
                        title: 'Edit Review',
                        html: `
                            <form id="edit-review-form" class="swal-form new-review-form">
                                <div style="margin-bottom: 10px;">
                                    <label for="stars" style="display: block; font-weight: bold;">Rating:</label>
                                    <select id="stars" name="rating" required 
                                        style="width: 100%; padding: 10px; margin-bottom: 1rem; border-radius: 8px; border: 1px solid #ccc; font-size: 1rem; transition: border-color 0.3s ease;">
                                        <option value="" disabled selected>Choose a rating</option>
                                        <option value="1">★☆☆☆☆</option>
                                        <option value="2">★★☆☆☆</option>
                                        <option value="3">★★★☆☆</option>
                                        <option value="4">★★★★☆</option>
                                        <option value="5">★★★★★</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="comment" style="display: block; font-weight: bold;">Comment:</label>
                                    <textarea id="comment" name="comment" required
                                        style="width: 100%; height: 80px; padding: 10px; margin-bottom: 1rem; border-radius: 8px; border: 1px solid #ccc; font-size: 1rem; transition: border-color 0.3s ease;">${review.comment}</textarea>
                                </div>
                            </form>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Update',
                        cancelButtonText: 'Cancel',
                        preConfirm: () => {
                            const form = document.getElementById('edit-review-form');
                            const formData = new FormData(form);

                            formData.append('review_id', reviewId);
                            formData.append('action', 'editReview');

                            return fetch('./profile/admin/review_table.php', {
                                method: 'POST',
                                body: formData
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (!data.success) {
                                        throw new Error(data.message);
                                    }
                                    updatedData = data.data;
                                    return updatedData;
                                })
                                .catch(error => {
                                    Swal.showValidationMessage(`Request failed: ${error}`);
                                });
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Success!',
                                text: 'Review updated successfully.',
                                icon: 'success',
                                timer: 1000,
                                showConfirmButton: true
                            }).then(() => {
                                const row = $(`button[data-id="${reviewId}"]`).closest('tr');
                                row.find('td').eq(2).html('⭐'.repeat(updatedData.rating));
                                row.find('td').eq(3).text(updatedData.comment);
                            });
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            Swal.fire({
                                title: 'Cancelled',
                                text: 'Your review remains unchanged.',
                                icon: 'info',
                                timer: 1000,
                                showConfirmButton: true
                            });
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Could not retrieve review data.',
                        icon: 'error'
                    });
                }
            })
            .catch((error) => {
                Swal.fire({
                    title: 'Error!',
                    text: error.message,
                    icon: 'error'
                });
            });
    });

    $(document).on("click", ".delete-btn-review", function () {
        const reviewId = $(this).data("id");

        Swal.fire({
            title: 'Delete Review',
            text: 'Are you sure you want to delete this review?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'deleteReview');
                formData.append('review_id', reviewId);

                fetch("./profile/admin/review_table.php", {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const table = $('#reviewsTable').DataTable();
                            table.row($(this).parents('tr')).remove().draw();

                            Swal.fire({
                                title: 'Success',
                                text: 'Review deleted successfully',
                                icon: 'success',
                                showConfirmButton: true
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: data.message,
                                icon: 'error'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to delete review',
                            icon: 'error'
                        });
                    });
            }else if (result.dismiss === Swal.DismissReason.cancel) {
                Swal.fire('Cancelled', 'Review was not deleted.', 'info');
            }
        });
    });

}
else {
    const userButtons = [
        document.getElementById("info-btn"),
        document.getElementById("appointments-btn"),
        document.getElementById('completed-btn'),
        document.getElementById('payments-btn'),
        document.getElementById('password-btn')
    ];
    const userSections = [
        document.getElementById("info-section"),
        document.getElementById("appointments-section"),
        document.getElementById("completed-appointments-section"),
        document.getElementById("payments-section"),
        document.getElementById("change-password-section"),
        document.getElementById("welcome-section")
    ];

    initializeTabs(userButtons, userSections);

    document.getElementById("schedule-btn").addEventListener('click', function () {
        window.location.href = "./booking.php?service=Implants";
    });

    document.getElementById("payments-btn").addEventListener('click', function () {
        fetch("./profile/user/get_paid_products.php", {
            method: "GET"
        })
            .then((response) => response.json())
            .then((responseData) => {
                if (responseData.success) {
                    const sampleData = responseData.data;

                    $("#usersTable").DataTable({
                        destroy: true,
                        data: sampleData,
                        columns: [
                            { data: "name" },
                            { data: "price" },
                            { data: "quantity" },
                            { data: "time" }
                        ],
                        lengthChange: false,
                        pageLength: 8,
                        order: [[0, "desc"]],
                        responsive: true,
                        language: {
                            search: "Search payments:",
                            lengthMenu: "Show _MENU_ payments per page",
                            info: "Showing _START_ to _END_ of _TOTAL_ payments",
                            emptyTable: "No payments found",
                        },
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: responseData.message,
                        icon: 'error'
                    });
                }
            })
            .catch((error) => {
                Swal.fire({
                    title: 'Error',
                    text: 'An unexpected error occurred while fetching the payments.',
                    icon: 'error'
                });
            });
    });
}

document.querySelector("#info-section > .update-form").addEventListener('submit', function (e) {
    e.preventDefault();
    const name = document.getElementById("name").value.trim();
    const surname = document.getElementById("surname").value.trim();
    const email = document.getElementById("email").value.trim();
    const phone = document.getElementById("phone").value.trim();

    const nameError = document.getElementById("nameError");
    const surnameError = document.getElementById("surnameError");
    const emailError = document.getElementById("emailError");
    const phoneError = document.getElementById("phoneError");

    const nameRegex = /^[a-zA-Z\s]+$/;
    const emailRegex = /^[a-zA-Z0-9.-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    const phoneRegex = /^\+?[0-9]{1,14}$/;

    let isValid = true;

    nameError.textContent = "";
    surnameError.textContent = "";
    emailError.textContent = "";
    phoneError.textContent = "";

    if (!nameRegex.test(name)) {
        nameError.textContent = "Please enter a valid name.";
        isValid = false;
    }
    if (!nameRegex.test(surname)) {
        surnameError.textContent = "Please enter a valid surname.";
        isValid = false;
    }

    if (!emailRegex.test(email)) {
        emailError.textContent = "Please enter a valid email address.";
        isValid = false;
    }

    if (!phoneRegex.test(phone)) {
        phoneError.textContent = "Please enter a valid phone number.";
        isValid = false;
    }
    
    if (isValid) {
        const formData = new FormData(this);

        Swal.fire({
            title: "Are you sure you want to update your profile?",
            text: "You will not be able to revert these changes.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, update it!",
            cancelButtonText: "No, keep it"
        }).then((result) => {
            if (result.isConfirmed) {

                fetch('./connection.php', {
                    method: 'POST', body: formData,
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: "Profile updated successfully!", icon: "success"
                            }).then(() => {
                                window.location.href = data.location;
                            });
                        } else {
                            Swal.fire({
                                title: data.message || "An error occurred.", icon: "error"
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: "Failed to update profile.", icon: "error"
                        });
                    });
            }
        });
    }
});

document.querySelector("#change-password-section > .update-form").addEventListener('submit', function (e) {
    e.preventDefault();

    const password = document.getElementById("password").value.trim();
    const confirmPassword = document.getElementById("confirm-password").value.trim();

    const passwordError = document.getElementById("password-error");
    const confirmPasswordError = document.getElementById("confirm-password-error");

    const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*\.])[A-Za-z\d!@#$%^&*\.]{6,}$/;

    let isValid = true;

    passwordError.textContent = "";
    confirmPasswordError.textContent = "";

    if(password === ""){
        passwordError.textContent = "Please enter a password.";
        isValid = false;
        return;
    }
    if(confirmPassword === ""){
        confirmPasswordError.textContent = "Please enter a password.";
        isValid = false;
        return;
    }
    if (!regex.test(password)) {
        passwordError.textContent = "Password must contain at least one lowercase, one uppercase, one digit, one special character, and be at least 6 characters long.";
        isValid = false;
        return;
    }
    if (password !== confirmPassword) {
        confirmPasswordError.textContent = "Passwords do not match.";
        isValid = false;
        return;
    }

    if (isValid) {

        Swal.fire({
            title: "Are you sure you want to change your password?",
            text: "Make sure you remember your new password.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, change it!",
            cancelButtonText: "No, keep it"
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData(this);

                fetch('./connection.php', {
                    method: 'POST', body: formData,
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: "Password updated successfully!", icon: "success"
                            }).then(() => {
                                window.location.href = data.location;
                            });
                        } else {
                            Swal.fire({
                                title: data.message || "An error occurred.", icon: "error"
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: "Failed to update password.", icon: "error"
                        });
                    });
            }
        });
    }
});

document.getElementById("delete-btn").addEventListener('click', function () {

    Swal.fire({
        title: "Are you sure you want to delete your profile?",
        text: "Your account will be deleted permanently.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "No, keep it"
    }).then((result) => {
        if (result.isConfirmed) {

            const data = new FormData();
            data.append('action', 'deleteProfile');

            fetch('./connection.php', {
                method: 'POST', body: data
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: "Account deleted successfully!", icon: "success"
                        }).then(() => {
                            window.location.href = data.location;
                        });
                    } else {
                        Swal.fire({
                            title: data.message || "An error occurred.", icon: "error"
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: "Failed to Delete profile.", icon: "error"
                    });
                });
        }
    });
});

document.getElementById("logout-btn").addEventListener('click', function () {

    Swal.fire({
        title: "Are you sure you want to log out of your profile?",
        text: "You will be required to log in again.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, log out it!",
        cancelButtonText: "Stay logged in!"
    }).then((result) => {
        if (result.isConfirmed) {

            const data = new FormData();
            data.append('action', 'logout');

            fetch('./connection.php', {
                method: 'POST', body: data
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: "Logged out successfully!", icon: "success", timer: 1000, showConfirmButton: false,  timerProgressBar: true
                        }).then(() => {
                            window.location.href = data["location"];
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: "Failed to Log out of profile.", icon: "error"
                    });
                });
        }
    });
});

function initializeTabs(buttons, sections) {

    function setActiveButton(activeButton) {
        buttons.forEach(button => button.classList.remove('active'));
        activeButton.classList.add('active');
    }

    function hideAllSections() {
        sections.forEach(section => section.classList.add('hidden'));
    }

    buttons.forEach((button, index) => {
        button.addEventListener("click", () => {
            hideAllSections();
            setActiveButton(button);
            sections[index].classList.remove("hidden");
        });
    });
}

function newsletter(event) {
    event.preventDefault();

    const subject = document.getElementById('subject').value.trim();
    const content = document.getElementById('content').value.trim();

    if (!subject || !content) {
        Swal.fire({
            title: 'Error',
            text: 'Please fill out both the subject and content fields.',
            icon: 'error'
        });
        return;
    }

    const formData = {
        action: "newsletter",
        subject: subject,
        body: content
    };

    $.ajax({
        url: "./connection.php",
        type: "POST",
        data: formData,
        dataType: "json",
        success: function (response) {
            if (response.success) {
                Swal.fire({
                    title: 'Success',
                    text: response.message,
                    icon: 'success'
                });
                document.getElementById('newsletterForm').reset();
            } else {
                Swal.fire({
                    title: 'Error',
                    text: "Error: " + response.message,
                    icon: 'error'
                });
            }
        },
        error: function () {
            Swal.fire({
                title: 'Error',
                text: 'An unexpected error occurred while sending the newsletter.',
                icon: 'error'
            });
        }
    });
}

function passwordCheck() {

    let isValid = true;

    const password = document.getElementById("user_password").value;
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*\.])[A-Za-z\d!@#$%^&*\.]{6,}$/;
    const passwordError = document.getElementById("user_password-error");
    passwordError.textContent = "";

    if (!passwordRegex.test(password)) {
        passwordError.textContent = "Password must contain at least one lowercase, one uppercase, one digit, one special character, and be at least 6 characters long.";
        isValid = false;
    } else {
        passwordError.textContent = "";
    }
    return isValid;
}

function phoneCheck() {

    let isValid = true;

    const phone = document.getElementById("user_phone").value;
    const phoneRegex = /^\+?[0-9]{1,14}$/;
    const phoneError = document.getElementById("user_phone-error");
    phoneError.textContent = "";

    if (!phoneRegex.test(phone)) {
        phoneError.textContent = "Please enter a valid phone number.";
        isValid = false;
    } else {
        phoneError.textContent = "";
    }
    return isValid;
}

function emailCheck() {

    let isValid = true;

    const email = document.getElementById("user_email").value;
    const emailRegex = /^[a-zA-Z0-9.-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    const emailError = document.getElementById("user_email-error");
    emailError.textContent = "";

    if (!emailRegex.test(email)) {
        emailError.textContent = "Please enter a valid email address.";
        isValid = false;
    } else {
        emailError.textContent = "";
    }
    return isValid;
}

function nameCheck(id) {

    let isValid = true;

    const name = document.getElementById(id).value;
    const nameRegex = /^[a-zA-Z\s]+$/;
    const nameError = document.getElementById(id + "-error")
    nameError.textContent = "";

    if (!nameRegex.test(name)) {
        nameError.textContent = "Please ensure your name contains only letters.";
        isValid = false;
    } else {
        nameError.textContent = "";
    }
    return isValid;
}

function togglePassword(id, iconElement) {
    const password = document.getElementById(id);
    const type = password.getAttribute("type") === "password" ? "text" : "password";
    password.setAttribute("type", type);

    iconElement.classList.toggle("bi-eye");
    iconElement.classList.toggle("bi-eye-slash");
}