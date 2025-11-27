/* ========================== ATTENDANCE ROW LOGIC ========================== */
function evaluateRow(tr) {
    const sessionChecks = Array.from(tr.querySelectorAll('td:nth-child(n+5):nth-child(-n+10) input[type="checkbox"]'));
    const partChecks = Array.from(tr.querySelectorAll('td:nth-child(n+11):nth-child(-n+16) input[type="checkbox"]'));

    const presents = sessionChecks.filter(c => c.checked).length;
    const absences = sessionChecks.length - presents;
    const parts = partChecks.filter(c => c.checked).length;

    const absCell = tr.querySelector('.absences');
    const partCell = tr.querySelector('.participations');
    const msgCell = tr.querySelector('.message');

    if (absCell) absCell.textContent = absences;
    if (partCell) partCell.textContent = parts;

    tr.classList.remove('abs-low', 'abs-mid', 'abs-high');
    if (absences < 3) tr.classList.add('abs-low');
    else if (absences <= 4) tr.classList.add('abs-mid');
    else tr.classList.add('abs-high');

    let msg = "";
    if (absences >= 5) {
        msg = "Excluded – too many absences – You need to participate more";
    } else if (absences >= 3) {
        msg = (parts >= 3) ? "Warning – attendance low – Good participation" :
            "Warning – attendance low – You need to participate more";
    } else {
        msg = (parts >= 4) ? "Good attendance – Excellent participation" :
            "Good attendance – Keep participating";
    }
    if (msgCell) msgCell.textContent = msg;
}

function evaluateAll() {
    document.querySelectorAll('#attendanceTable tbody tr').forEach(evaluateRow);
}

document.addEventListener('change', e => {
    if (e.target.matches('#attendanceTable input[type="checkbox"]')) {
        const tr = e.target.closest('tr');
        if (tr) evaluateRow(tr);
    }
});

evaluateAll();

/* ========================== REPORT PER SESSION ========================== */
function computeSessionReport() {
    const rows = Array.from(document.querySelectorAll('#attendanceTable tbody tr'));
    const n = rows.length;
    const sessions = 6;

    const total = Array(sessions).fill(n);
    const present = Array(sessions).fill(0);
    const participated = Array(sessions).fill(0);

    rows.forEach(tr => {
        for (let k = 1; k <= sessions; k++) {
            const sCell = tr.querySelector(`td:nth-child(${4 + k}) input[type="checkbox"]`);
            const pCell = tr.querySelector(`td:nth-child(${10 + k}) input[type="checkbox"]`);
            if (sCell && sCell.checked) present[k - 1]++;
            if (pCell && pCell.checked) participated[k - 1]++;
        }
    });

    return { total, present, participated };
}

function drawSessionChart({ total, present, participated }) {
    const canvas = document.getElementById('reportChart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    const labels = ['S1', 'S2', 'S3', 'S4', 'S5', 'S6'];
    const groups = labels.length;

    const datasets = [
        { name: 'Total', values: total, color: '#3F2F03' },
        { name: 'Present', values: present, color: '#807043' },
        { name: 'Participated', values: participated, color: '#B9B29F' }
    ];

    const padding = 44;
    const chartW = canvas.width - padding * 2;
    const chartH = canvas.height - padding * 2;

    const barGap = 10;
    const groupGap = 26;
    const barsPerGroup = datasets.length;
    const maxVal = Math.max(...datasets.flatMap(d => d.values), 1);

    const totalGroupGaps = groupGap * (groups - 1);
    const groupWidth = (chartW - totalGroupGaps) / groups;
    const innerWidth = groupWidth - barGap * (barsPerGroup - 1);
    const singleBarW = innerWidth / barsPerGroup;

    ctx.strokeStyle = '#b9ac8b';
    ctx.lineWidth = 1.25;
    ctx.beginPath();
    ctx.moveTo(padding, canvas.height - padding);
    ctx.lineTo(canvas.width - padding, canvas.height - padding);
    ctx.moveTo(padding, padding);
    ctx.lineTo(padding, canvas.height - padding);
    ctx.stroke();

    ctx.strokeStyle = 'rgba(0,0,0,0.06)';
    ctx.lineWidth = 1;
    const guides = 5;
    for (let i = 1; i <= guides; i++) {
        const y = canvas.height - padding - (chartH / guides) * i;
        ctx.beginPath();
        ctx.moveTo(padding, y);
        ctx.lineTo(canvas.width - padding, y);
        ctx.stroke();
    }

    ctx.textAlign = 'center';
    for (let g = 0; g < groups; g++) {
        const groupX = padding + g * (groupWidth + groupGap);
        ctx.fillStyle = '#6f644b';
        ctx.font = '12px Inter, sans-serif';
        ctx.fillText(labels[g], groupX + groupWidth / 2, canvas.height - padding + 16);

        let barX = groupX;
        datasets.forEach(ds => {
            const value = ds.values[g];
            const h = (value / maxVal) * (chartH - 8);
            const y = canvas.height - padding - h;

            ctx.fillStyle = ds.color;
            ctx.fillRect(barX, y, singleBarW, h);

            ctx.fillStyle = '#1A1917';
            ctx.font = 'bold 12px Inter, sans-serif';
            ctx.fillText(String(value), barX + singleBarW / 2, y - 6);

            barX += singleBarW + barGap;
        });
    }

    ctx.fillStyle = '#6f644b';
    ctx.font = '12px Inter, sans-serif';
    ctx.textAlign = 'right';
    ctx.fillText(String(maxVal), padding - 6, padding + 6);
}

const showReportBtn = document.getElementById('showReportBtn');
if (showReportBtn) {
    showReportBtn.addEventListener('click', () => {
        const data = computeSessionReport();
        drawSessionChart(data);
        const section = document.getElementById('report');
        if (section) section.style.display = 'block';
    });
}

/* ========================== PHP BACKEND INTEGRATION ========================== */
function saveAttendanceToPHP() {
    const rows = Array.from(document.querySelectorAll('#attendanceTable tbody tr'));
    const attendanceData = {};

    rows.forEach(tr => {
        const studentId = tr.querySelector('td:nth-child(1)').textContent.trim();
        const sessionChecks = Array.from(tr.querySelectorAll('td:nth-child(n+5):nth-child(-n+10) input[type="checkbox"]'));
        const partChecks = Array.from(tr.querySelectorAll('td:nth-child(n+11):nth-child(-n+16) input[type="checkbox"]'));

        attendanceData[studentId] = {
            sessions: sessionChecks.map(c => c.checked ? 1 : 0),
            participation: partChecks.map(c => c.checked ? 1 : 0)
        };
    });

    fetch('take_attendance.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `attendance=${encodeURIComponent(JSON.stringify(attendanceData))}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Attendance saved successfully to server!');
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to save attendance to server');
        });
}

document.addEventListener('DOMContentLoaded', function() {
    const saveAttendanceBtn = document.getElementById('saveAttendanceBtn');
    if (saveAttendanceBtn) {
        saveAttendanceBtn.addEventListener('click', saveAttendanceToPHP);
    }
});

/* ========================== FORM VALIDATION ========================== */
const form = document.getElementById('studentForm');
if (form) {
    const fields = {
        studentId: {
            el: document.getElementById('studentId'),
            wrap: document.getElementById('f-studentId'),
            test: v => /^\d+$/.test(v.trim())
        },
        lastName: {
            el: document.getElementById('lastName'),
            wrap: document.getElementById('f-lastName'),
            test: v => /^[A-Za-zÀ-ÖØ-öø-ÿ' -]+$/.test(v.trim())
        },
        firstName: {
            el: document.getElementById('firstName'),
            wrap: document.getElementById('f-firstName'),
            test: v => /^[A-Za-zÀ-ÖØ-öø-ÿ' -]+$/.test(v.trim())
        },
        email: {
            el: document.getElementById('email'),
            wrap: document.getElementById('f-email'),
            test: v => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v.trim())
        }
    };

    function showError(wrap, ok) {
        wrap.classList.toggle('invalid', !ok);
        const msg = wrap.querySelector('.error-msg');
        if (msg) msg.style.display = ok ? 'none' : 'block';
    }

    function validateField(f) {
        const ok = f.test(f.el.value);
        showError(f.wrap, ok);
        return ok;
    }

    Object.values(fields).forEach(f => {
        if (!f.el) return;
        f.el.addEventListener('input', () => validateField(f));
        f.el.addEventListener('blur', () => validateField(f));
    });

    form.addEventListener('submit', e => {
        const results = Object.values(fields).map(validateField);
        if (results.some(ok => !ok)) { e.preventDefault(); return; }

        e.preventDefault();

        // Send to PHP backend instead of local storage
        const formData = new FormData();
        formData.append('studentId', fields.studentId.el.value.trim());
        formData.append('lastName', fields.lastName.el.value.trim());
        formData.append('firstName', fields.firstName.el.value.trim());
        formData.append('email', fields.email.el.value.trim());

        // Use Database version
        fetch('add_student_db.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Add student to table locally
                    const tbody = document.querySelector('#attendanceTable tbody');
                    if (!tbody) return;

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                  <td>${fields.studentId.el.value.trim()}</td>
                  <td>${fields.lastName.el.value.trim()}</td>
                  <td>${fields.firstName.el.value.trim()}</td>
                  <td>AWP</td>
                  ${Array.from({length:6}).map(()=>'<td><input type="checkbox"></td>').join('')}
                  ${Array.from({length:6}).map(()=>'<td><input type="checkbox"></td>').join('')}
                  <td class="absences">0</td>
                  <td class="participations">0</td>
                  <td class="message"></td>
                `;
                    tbody.appendChild(tr);
                    evaluateRow(tr);

                    // Reset form
                    form.reset();
                    Object.values(fields).forEach(f => showError(f.wrap, true));

                    alert('Student added successfully to database!');

                } else if (data.errors) {
                    // Show validation errors from server
                    Object.keys(data.errors).forEach(field => {
                        if (fields[field]) {
                            showError(fields[field].wrap, false);
                        }
                    });
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to add student to database');
            });
    });
}

/* ========================== jQuery interactions ========================== */
$(document).ready(function() {
    $('#attendanceTable tbody').on('mouseenter', 'tr', function() {
        $(this).css('background-color', '#fff1c6');
    });

    $('#attendanceTable tbody').on('mouseleave', 'tr', function() {
        if (!$(this).hasClass('row-excellent')) {
            $(this).css('background-color', '');
        }
    });

    $('#attendanceTable tbody').on('click', 'tr', function() {
        const lastName = $(this).find('td:nth-child(2)').text().trim();
        const firstName = $(this).find('td:nth-child(3)').text().trim();
        const abs = $(this).find('.absences').text().trim();
        const absDisplay = abs || 'not evaluated';
        const fullName = `${firstName} ${lastName}`;
        alert(`Student: ${fullName}\nAbsences: ${absDisplay}`);
    });

    function ensureComputed() {
        if (typeof evaluateAll === 'function') evaluateAll();
    }

    $('#highlightExcellentBtn').on('click', function() {
        ensureComputed();
        $('#attendanceTable tbody tr').each(function() {
            const absText = $(this).find('.absences').text().trim();
            const absences = parseInt(absText || '0', 10);
            if (!isNaN(absences) && absences < 3) {
                const $row = $(this);
                $row.addClass('row-excellent')
                    .fadeTo(150, 0.4)
                    .fadeTo(150, 1.0);
            }
        });
    });

    $('#resetColorsBtn').on('click', function() {
        $('#attendanceTable tbody tr').removeClass('row-excellent').css({
            'background-color': '',
            'opacity': ''
        });
    });

    $('#attendanceTable').on('change', 'input[type="checkbox"]', function() {
        const $tr = $(this).closest('tr');
        if (typeof evaluateRow === 'function') evaluateRow($tr[0]);
        const abs = parseInt($tr.find('.absences').text().trim() || '0', 10);
        if (!isNaN(abs)) {
            if (abs < 3) {
                $tr.addClass('row-excellent');
            } else {
                $tr.removeClass('row-excellent');
            }
        }
    });

    $('#searchInput').on('input', function() {
        const searchTerm = $(this).val().toLowerCase().trim();
        if (searchTerm === '') {
            $('#attendanceTable tbody tr').show();
            return;
        }
        $('#attendanceTable tbody tr').each(function() {
            const lastName = $(this).find('td:nth-child(2)').text().toLowerCase();
            const firstName = $(this).find('td:nth-child(3)').text().toLowerCase();
            const fullName = `${firstName} ${lastName}`;
            if (lastName.includes(searchTerm) || firstName.includes(searchTerm) || fullName.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    $('#sortAbsencesBtn').on('click', function() {
        sortTable('absences', 'asc');
        $('#sortMessage').text('Currently sorted by absences (ascending)');
    });

    $('#sortParticipationBtn').on('click', function() {
        sortTable('participation', 'desc');
        $('#sortMessage').text('Currently sorted by participation (descending)');
    });

    function sortTable(criteria, order) {
        ensureComputed();
        const $tbody = $('#attendanceTable tbody');
        const $rows = $tbody.find('tr').get();
        $rows.sort(function(a, b) {
            let aValue, bValue;
            if (criteria === 'absences') {
                aValue = parseInt($(a).find('.absences').text().trim() || '0', 10);
                bValue = parseInt($(b).find('.absences').text().trim() || '0', 10);
            } else if (criteria === 'participation') {
                aValue = parseInt($(a).find('.participations').text().trim() || '0', 10);
                bValue = parseInt($(b).find('.participations').text().trim() || '0', 10);
            }
            if (order === 'asc') {
                return aValue - bValue;
            } else {
                return bValue - aValue;
            }
        });
        $.each($rows, function(index, row) {
            $tbody.append(row);
        });
    }
});
