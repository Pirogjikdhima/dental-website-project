async function fetchBookedSlots(doctorId) {
    try {
        const response = await fetch(`handleBooking.php?doctor_id=${doctorId}`);
        if (response.ok) {
            const data = await response.json();
            return Array.isArray(data) ? data : [];
        } else {
            console.error('Error fetching booked slots:', response.statusText);
            return [];
        }
    } catch (error) {
        console.error('Fetch error:', error);
        return [];
    }
}

function setupDropdowns() {
    const doctorSelect = document.getElementById('doctor_id');
    const dateSelect = document.getElementById('date');
    const timeSelect = document.getElementById('time');

    let bookedSlots = [];

    const updateDates = () => {
        dateSelect.innerHTML = '<option value="" disabled selected>Select a Date</option>';
        const now = new Date();
        const maxDate = new Date();
        maxDate.setDate(now.getDate() + 7);

        const bookedDates = bookedSlots.map(slot => slot.date);
        const tomorrow = new Date();
        tomorrow.setDate(now.getDate() + 1);

        for (let d = new Date(tomorrow); d <= maxDate; d.setDate(d.getDate() + 1)) {
            const day = d.getDay();
            if (day === 0) continue;

            const dateStr = d.toISOString().split('T')[0];

            const isFullyBooked = checkFullyBooked(dateStr);

            const dateOption = document.createElement('option');
            dateOption.value = dateStr;
            dateOption.textContent = dateStr;

            if (isFullyBooked) {
                dateOption.disabled = true;
                dateOption.textContent += ' (Fully Booked)';
            }

            dateSelect.appendChild(dateOption);
        }
    };

    const checkFullyBooked = (date) => {
        const day = new Date(date).getDay();
        const workingHours = day === 6 ? [9, 14] : [8, 18];
        const totalSlots = (workingHours[1] - workingHours[0]);
        const bookedSlotsForDate = bookedSlots.filter(slot => slot.date === date);

        return bookedSlotsForDate.length >= totalSlots;
    };

    const updateTimes = (selectedDate) => {
        timeSelect.innerHTML = '<option value="" disabled selected>Select a Time</option>';
        if (!selectedDate) return;

        const day = new Date(selectedDate).getDay();
        const workingHours = day === 6 ? [9, 14] : [8, 18];

        for (let hour = workingHours[0]; hour < workingHours[1]; hour++) {
            const time = `${hour.toString().padStart(2, '0')}:00`;

            const isTimeBooked = bookedSlots.some((slot) => {
                return slot.date === selectedDate && slot.time === time;
            });

            const timeOption = document.createElement('option');
            timeOption.value = time;
            timeOption.textContent = time;

            if (isTimeBooked) {
                timeOption.disabled = true;
                timeOption.textContent += ' (Booked)';
            }

            timeSelect.appendChild(timeOption);
        }

        if (timeSelect.options.length === 1) {
            const noSlotsOption = document.createElement('option');
            noSlotsOption.value = '';
            noSlotsOption.disabled = true;
            noSlotsOption.textContent = 'No available slots';
            timeSelect.appendChild(noSlotsOption);
        }
    };

    doctorSelect.addEventListener('change', async function () {
        const selectedDoctor = doctorSelect.value;
        if (!selectedDoctor) return;

        bookedSlots = await fetchBookedSlots(selectedDoctor);
        updateDates();

        timeSelect.innerHTML = '<option value="" disabled selected>Select a Time</option>';
    });

    dateSelect.addEventListener('change', function () {
        const selectedDate = dateSelect.value;
        updateTimes(selectedDate);
    });
}

setupDropdowns();


function updateServiceDetails() {
    const select = document.getElementById('service');
    const selectedOption = select.options[select.selectedIndex];
    const iconUrl = selectedOption.getAttribute('data-icon');
    const description = selectedOption.getAttribute('data-description');
    const serviceName = selectedOption.textContent;
    const price = selectedOption.getAttribute('data-price');

    document.getElementById('service-icon').src = iconUrl;
    document.getElementById('service-description-text').textContent = description;
    document.getElementById('service-description').getElementsByTagName('h2')[0].innerHTML = `${serviceName} <br> $${price}`;

    const serviceDesc = document.getElementById('service-description');
    const serviceDescText = document.getElementById('service-description-text');

    serviceDesc.style.opacity = 0;
    serviceDesc.style.transform = 'scale(0.9)';
    serviceDescText.style.opacity = 0;
    serviceDescText.style.transform = 'scale(0.9)';

    serviceDesc.offsetHeight;

    setTimeout(function () {
        serviceDesc.style.transition = 'opacity 1s, transform 1s';
        serviceDescText.style.transition = 'opacity 1s, transform 1s';

        serviceDesc.style.opacity = 1;
        serviceDesc.style.transform = 'scale(1)';
        serviceDescText.style.opacity = 1;
        serviceDescText.style.transform = 'scale(1)';
    }, 50);
}

document.getElementById('service').addEventListener('change', updateServiceDetails);

document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('service').value) {
        updateServiceDetails();
    }
});
