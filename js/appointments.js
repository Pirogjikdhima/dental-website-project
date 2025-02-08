class AppointmentsPagination {

    constructor(containerId, appointments, itemsPerPage = 3) {
        this.containerId = containerId;
        this.appointments = appointments;
        this.originalAppointments = [...appointments];
        this.itemsPerPage = itemsPerPage;
        this.currentPage = 1;
        this.totalPages = Math.ceil(appointments.length / itemsPerPage);
        this.init();
    }

    init() {
        const section = document.getElementById(this.containerId);
        section.innerHTML = `
            <div class="search-bar">
                <input type="text" id="${this.containerId}-search-bar" placeholder="Search by date" />
                <button id="${this.containerId}-search-btn">Search</button>
                <button id="${this.containerId}-reset-btn">Reset</button>
            </div>
            <div id="${this.containerId}-container"></div>
            <div class="pagination">
                <button id="${this.containerId}-prev" onclick="paginators['${this.containerId}'].prevPage()">Previous</button>
                <span class="page-info">Page <span id="${this.containerId}-current">1</span> of <span id="${this.containerId}-total">${this.totalPages}</span></span>
                <button id="${this.containerId}-next" onclick="paginators['${this.containerId}'].nextPage()">Next</button>
            </div>
        `;
        this.addSearchListeners();
        this.updateDisplay();
    }

    addSearchListeners() {
        const searchBar = document.getElementById(`${this.containerId}-search-bar`);
        const searchBtn = document.getElementById(`${this.containerId}-search-btn`);
        const resetBtn = document.getElementById(`${this.containerId}-reset-btn`);

        searchBtn.addEventListener('click', () => {
            const query = searchBar.value.trim();
            if (query) {
                this.searchAppointments(query);
            }
        });

        resetBtn.addEventListener('click', () => {
            this.resetSearch();
        });
    }

    searchAppointments(query) {
        this.appointments = this.originalAppointments.filter(appointment => appointment.date.includes(query));
        this.currentPage = 1;
        this.totalPages = Math.ceil(this.appointments.length / this.itemsPerPage);
        this.updateDisplay();

        document.getElementById(`${this.containerId}-total`).textContent = this.totalPages;
    }

    resetSearch() {
        this.appointments = [...this.originalAppointments];
        this.currentPage = 1;
        this.totalPages = Math.ceil(this.appointments.length / this.itemsPerPage);
        this.updateDisplay();

        document.getElementById(`${this.containerId}-search-bar`).value = '';
        document.getElementById(`${this.containerId}-total`).textContent = this.totalPages;
    }

    updateDisplay() {
        const container = document.getElementById(`${this.containerId}-container`);
        const start = (this.currentPage - 1) * this.itemsPerPage;
        const end = start + this.itemsPerPage;
        const pageAppointments = this.appointments.slice(start, end);

        if (pageAppointments.length === 0) {
            container.innerHTML = '<p>No appointments available.</p>';
        } else {
            container.innerHTML = pageAppointments.map(appointment => {
                const date = new Date(appointment.date);
                const formattedDate = date.toLocaleDateString('en-GB', {
                    day: '2-digit', month: '2-digit', year: 'numeric'
                });
                const formattedTime = appointment.time.slice(0, 5);
                return `
            <div class="appointment-schedule">
                <span class="date">${formattedDate} </span>
                <span class="time">${formattedTime} </span>
                <p> ${appointment.service} - ${appointment.description}</p>
                <span class="name">${appointment.doctor}</span>
            </div>
        `;
            }).join('');
        }

        document.getElementById(`${this.containerId}-current`).textContent = this.currentPage;
        document.getElementById(`${this.containerId}-prev`).disabled = this.currentPage === 1;
        document.getElementById(`${this.containerId}-next`).disabled = this.currentPage === this.totalPages || this.totalPages === 0;
    }

    nextPage() {
        if (this.currentPage < this.totalPages) {
            this.currentPage++;
            this.updateDisplay();
        }
    }

    prevPage() {
        if (this.currentPage > 1) {
            this.currentPage--;
            this.updateDisplay();
        }
    }
}

const paginators = {};

document.addEventListener('DOMContentLoaded', function () {
    fetch('./profile/user/get_appointments.php')
        .then(response => response.json())
        .then(responseData => {
            if (responseData.success) {
                const data = responseData.data;
                data.sort((a, b) => new Date(a.date) - new Date(b.date));
                const scheduledAppointments = data.filter(appointment => new Date(appointment.date) >= new Date());
                const completedAppointments = data.filter(appointment => new Date(appointment.date) < new Date());

                paginators['appointments-section'] = new AppointmentsPagination('appointments-section', scheduledAppointments);
                paginators['completed-appointments-section'] = new AppointmentsPagination('completed-appointments-section', completedAppointments);
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
        .catch(error => {
            iziToast.error({
                title: 'Error',
                message: `Error fetching appointments: ${error}`,
                position: 'topCenter',
                timeout: false,
                pauseOnHover: false,
            });
        });
});