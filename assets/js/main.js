document.addEventListener('DOMContentLoaded', () => {
    const defaultHostelGroups = {
        male: ['YKSG-1', 'YKSG-2'],
        female: ['RASG-1', 'RASG-2'],
    };

    const hostelGroups = (typeof window.registerHostelsByGender !== 'undefined' && window.registerHostelsByGender)
        ? window.registerHostelsByGender
        : defaultHostelGroups;

    const genderSelect = document.getElementById('gender');
    const hostelSelect = document.getElementById('hostel_name');

    if (genderSelect && hostelSelect) {
        const renderHostelsForGender = (gender) => {
            hostelSelect.innerHTML = '<option value="">Select Hostel</option>';

            if (!gender || !hostelGroups[gender]) {
                hostelSelect.disabled = true;
                return;
            }

            hostelGroups[gender].forEach((hostel) => {
                const option = document.createElement('option');
                option.value = hostel;
                option.textContent = hostel;
                hostelSelect.appendChild(option);
            });

            hostelSelect.disabled = false;
        };

        genderSelect.addEventListener('change', () => {
            renderHostelsForGender(genderSelect.value);
        });

        renderHostelsForGender(genderSelect.value);
    }

    const statusCharts = document.getElementById('statusChart');
    if (statusCharts && typeof window.dashboardStatusData !== 'undefined' && typeof Chart !== 'undefined') {
        new Chart(statusCharts, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'In Progress', 'Resolved'],
                datasets: [{
                    data: window.dashboardStatusData,
                    backgroundColor: ['#f59e0b', '#3b82f6', '#22c55e'],
                }],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                },
            },
        });
    }

    const hostelChart = document.getElementById('hostelChart');
    if (hostelChart && typeof window.dashboardHostelData !== 'undefined' && typeof Chart !== 'undefined') {
        const hostelLabels = (typeof window.dashboardHostelLabels !== 'undefined' && Array.isArray(window.dashboardHostelLabels) && window.dashboardHostelLabels.length)
            ? window.dashboardHostelLabels
            : ['YKSG-1', 'YKSG-2', 'RASG-1', 'RASG-2'];

        new Chart(hostelChart, {
            type: 'bar',
            data: {
                labels: hostelLabels,
                datasets: [{
                    label: 'Complaints',
                    data: window.dashboardHostelData,
                    backgroundColor: ['#145da0', '#1f7bb6', '#f95738', '#f77f00'],
                    borderRadius: 10,
                }],
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                        },
                    },
                },
            },
        });
    }

    const trendChart = document.getElementById('trendChart');
    if (trendChart
        && typeof window.dashboardTrendLabels !== 'undefined'
        && typeof window.dashboardNewTrendData !== 'undefined'
        && typeof window.dashboardResolvedTrendData !== 'undefined'
        && typeof Chart !== 'undefined') {
        new Chart(trendChart, {
            type: 'line',
            data: {
                labels: window.dashboardTrendLabels,
                datasets: [
                    {
                        label: 'New Complaints',
                        data: window.dashboardNewTrendData,
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.15)',
                        tension: 0.3,
                        fill: true,
                    },
                    {
                        label: 'Resolved Complaints',
                        data: window.dashboardResolvedTrendData,
                        borderColor: '#145da0',
                        backgroundColor: 'rgba(20, 93, 160, 0.12)',
                        tension: 0.3,
                        fill: true,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                        },
                    },
                },
            },
        });
    }

    const complaintForm = document.getElementById('complaint-form');
    if (complaintForm) {
        complaintForm.addEventListener('submit', (event) => {
            const title = document.getElementById('title');
            const room = document.getElementById('room_number');
            const description = document.getElementById('description');

            if (!title.value.trim() || !room.value.trim() || !description.value.trim()) {
                event.preventDefault();
                alert('Title, room number, and description are required.');
                return;
            }

            if (description.value.trim().length < 12) {
                event.preventDefault();
                alert('Description should be at least 12 characters.');
            }
        });
    }

    const metricValues = document.querySelectorAll('[data-count]');
    if (metricValues.length) {
        metricValues.forEach((metricEl) => {
            const target = Number(metricEl.getAttribute('data-count'));
            if (!Number.isFinite(target) || target <= 0) {
                return;
            }

            let current = 0;
            const step = Math.max(1, Math.ceil(target / 20));
            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    metricEl.textContent = String(target);
                    clearInterval(timer);
                    return;
                }
                metricEl.textContent = String(current);
            }, 35);
        });
    }
});
