document.getElementById("add-doctor-btn").addEventListener("click", function () {
    const form = document.getElementById("add-doctor-form");
    form.style.display = "block";
    document.getElementById("doctor_id").value = "";
    document.querySelector("form h2").textContent = "Add a New Dentist";

    form.querySelector("input[name='name']").value = "";
    form.querySelector("input[name='experience']").value = "";
    form.querySelector("textarea[name='about']").value = "";
    form.querySelector("input[name='photo']").value = "";
    form.scrollIntoView({behavior: "smooth", block: "start"});
});

document.querySelectorAll(".edit-btn").forEach(button => {
    button.addEventListener("click", function () {
        const form = document.getElementById("add-doctor-form");
        form.style.display = "block";

        document.getElementById("doctor_id").value = this.getAttribute("data-doctor-id");
        document.querySelector("input[name='name']").value = this.getAttribute("data-name");
        document.querySelector("input[name='specialization']").value = this.getAttribute("data-specialization");
        document.querySelector("input[name='experience']").value = this.getAttribute("data-experience");
        document.querySelector("textarea[name='about']").value = this.getAttribute("data-about");
        form.querySelector("input[name='photo']").value = "";

        document.querySelector("form h2").textContent = "Edit Dentist";
        form.scrollIntoView({behavior: "smooth", block: "start"});
    });
});

document.querySelectorAll(".delete-btn").forEach(button => {
    button.addEventListener("click", function () {
        const doctorId = this.getAttribute("data-doctor-id");

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location = `?action=delete&doctor_id=${doctorId}`;
            }
        });
    });
});