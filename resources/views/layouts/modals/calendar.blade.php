<div class="modal fade" id="modal-calendar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-success">
                <div class="modal-header border-success bg-danger">
                    <h1 class="h1 mb-0 modal-title text-white d-flex align-items-center w-100 justify-content-center" id="modal-calendar-title">
                        Activity Calendar
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="cal-prev" aria-label="Previous month">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <h2 class="h5 mb-0" id="cal-title"></h2>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="cal-next" aria-label="Next month">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                    <table class="table table-bordered text-center mb-0">
                        <thead>
                            <tr class="table-light">
                                <th class="text-danger">Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th>
                            </tr>
                        </thead>
                        <tbody id="cal-body"></tbody>
                    </table>
                </div>
                <div class="modal-footer border-0 justify-content-between">
                    <button type="button" class="btn btn-outline-success btn-sm" id="cal-today">Today</button>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const body = document.getElementById('cal-body');
        const title = document.getElementById('cal-title');
        const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July',
            'August', 'September', 'October', 'November', 'December'];
        const now = new Date();
        let year = now.getFullYear();
        let month = now.getMonth();

        function render() {
            title.textContent = months[month] + ' ' + year;
            body.innerHTML = '';

            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            let day = 1;

            for (let row = 0; row < 6 && day <= daysInMonth; row++) {
                const tr = document.createElement('tr');
                for (let col = 0; col < 7; col++) {
                    const td = document.createElement('td');
                    if ((row > 0 || col >= firstDay) && day <= daysInMonth) {
                        td.textContent = day;
                        if (col === 0) td.classList.add('text-danger');
                        if (day === now.getDate() && month === now.getMonth() && year === now.getFullYear()) {
                            td.classList.add('bg-success', 'text-white', 'fw-bold');
                        }
                        day++;
                    }
                    tr.appendChild(td);
                }
                body.appendChild(tr);
            }
        }

        document.getElementById('cal-prev').addEventListener('click', function () {
            month--;
            if (month < 0) { month = 11; year--; }
            render();
        });
        document.getElementById('cal-next').addEventListener('click', function () {
            month++;
            if (month > 11) { month = 0; year++; }
            render();
        });
        document.getElementById('cal-today').addEventListener('click', function () {
            year = now.getFullYear();
            month = now.getMonth();
            render();
        });

        render();
    });
</script>