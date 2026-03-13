<?= $this->extend($layout) ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1 class="page-header__title">ปฏิทินผู้บริหาร</h1>
        <div class="page-header__breadcrumb">
            <a href="<?= base_url() ?>">หน้าแรก</a>
            <span>/</span>
            <a href="<?= base_url('about') ?>">เกี่ยวกับคณะ</a>
            <span>/</span>
            <span>ปฏิทินผู้บริหาร</span>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <p class="section-header__description" style="margin-bottom: 1rem;">
            ปฏิทินนัดหมายและกิจกรรมของคณะวิทยาศาสตร์และเทคโนโลยี — เปิดให้ทุกคนเข้าดูได้
        </p>

        <!-- ปุ่มเชื่อมต่อปฏิทินในมือถือ -->
        <div style="margin-bottom: 1.5rem;">
            <button type="button" id="btnConnectMobileCalendar" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                📱 เชื่อมต่อปฏิทินในมือถือ
            </button>
        </div>

        <div class="calendar-public-wrap" style="background: var(--dash-card-bg, #fff); border-radius: 12px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08); border: 1px solid var(--dash-border, #e5e7eb);">
            <div id="publicCalendar" style="min-height: 500px;"></div>
        </div>
    </div>
</section>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css">
<style>
#publicCalendar { font-family: var(--font-primary, 'Sarabun', sans-serif); }
.fc { --fc-border-color: var(--dash-border, #e5e7eb); }
.fc .fc-button-primary { background: var(--color-primary, #eab308); border-color: var(--color-primary); color: #1a1a1a; }
.fc .fc-button-primary:hover { filter: brightness(1.1); }
.fc-toolbar-title { font-size: 1.25rem; }
</style>

<?= $this->endSection() ?>

<?= $this->section('footer_scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core/locales/th.global.min.js"></script>
<script>
(function() {
    var baseUrl = '<?= base_url() ?>';
    var apiUrl = baseUrl + 'api/calendar/public/events';
    var calendarEl = document.getElementById('publicCalendar');
    if (!calendarEl) return;
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'th',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        buttonText: {
            today: 'วันนี้',
            month: 'เดือน',
            week: 'สัปดาห์',
            day: 'วัน',
            list: 'รายการ'
        },
        editable: false,
        selectable: false,
        dayMaxEvents: true,
        weekends: true,
        slotMinTime: '06:00:00',
        slotMaxTime: '22:00:00',
        events: function(info, successCallback) {
            var url = apiUrl + '?start=' + encodeURIComponent(info.startStr) + '&end=' + encodeURIComponent(info.endStr);
            fetch(url)
                .then(function(r) { return r.json(); })
                .then(function(events) {
                    successCallback(Array.isArray(events) ? events : []);
                })
                .catch(function() { successCallback([]); });
        },
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            var desc = (info.event.extendedProps && info.event.extendedProps.description) ? info.event.extendedProps.description : '';
            var loc = (info.event.extendedProps && info.event.extendedProps.location) ? info.event.extendedProps.location : '';
            var msg = info.event.title;
            if (loc) msg += '\nสถานที่: ' + loc;
            if (desc) msg += '\n\n' + desc;
            alert(msg);
        }
    });
    calendar.render();

    var feedUrl = '<?= addslashes(base_url('api/calendar/public/feed')) ?>';
    var connectBtn = document.getElementById('btnConnectMobileCalendar');
    if (connectBtn) {
        connectBtn.addEventListener('click', function() {
            var html = '<div class="calendar-connect-swal" style="text-align: left;">' +
                '<p style="margin-bottom: 1rem;">ใช้ลิงก์ด้านล่างสมัครรับ (Subscribe) ปฏิทินนี้ในแอปปฏิทินของมือถือ กิจกรรมจะอัปเดตอัตโนมัติ</p>' +
                '<div style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center; margin-bottom: 1rem;">' +
                '<input type="text" id="swalFeedUrl" readonly value="' + feedUrl.replace(/"/g, '&quot;') + '" style="flex:1; min-width:180px; padding:0.5rem; font-size:0.85rem; border:1px solid #ddd; border-radius:6px;">' +
                '<button type="button" id="swalCopyLinkBtn" class="swal2-styled" style="padding:0.5rem 1rem; background:#eab308; color:#1a1a1a; border:none; border-radius:6px; cursor:pointer; font-weight:600;">คัดลอกลิงก์</button>' +
                '</div>' +
                '<div style="font-size:0.9rem; line-height:1.5;">' +
                '<p style="margin:0 0 0.5rem 0; font-weight:600;">iPhone (แอป ปฏิทิน):</p>' +
                '<p style="margin:0 0 0.75rem 0;">ตั้งค่า → ปฏิทิน → บัญชี → เพิ่มบัญชี → บัญชีที่สมัครรับ → วางลิงก์ด้านบน → ถัดไป → บันทึก</p>' +
                '<p style="margin:0 0 0.5rem 0; font-weight:600;">Android (Google Calendar):</p>' +
                '<p style="margin:0 0 0.75rem 0;">แอปมือถือไม่มีเมนูจาก URL — เปิด<strong> เบราว์เซอร์</strong> แล้วไปที่ <strong>calendar.google.com</strong> → เมนู (≡) → การตั้งค่า → เพิ่มปฏิทิน → จาก URL → วางลิงก์ด้านบน → เพิ่ม หลังจากนั้นปฏิทินจะโผล่ในแอปอัตโนมัติ</p>' +
                '<p style="margin:0; font-weight:600;">แอปอื่น:</p>' +
                '<p style="margin:0;">หาเมนู "เพิ่มปฏิทินจาก URL" หรือ "Subscribe" แล้ววางลิงก์เดียวกัน</p>' +
                '</div></div>';
            if (typeof Swal === 'undefined') {
                alert('ลิงก์สำหรับเชื่อมต่อ: ' + feedUrl + '\n\nAndroid: เปิดเบราว์เซอร์ไป calendar.google.com → เมนู → การตั้งค่า → เพิ่มปฏิทิน → จาก URL');
                return;
            }
            Swal.fire({
                title: 'วิธีเชื่อมต่อปฏิทินในมือถือ',
                html: html,
                width: '90%',
                maxWidth: '480px',
                confirmButtonText: 'ปิด',
                confirmButtonColor: '#eab308',
                didOpen: function() {
                    var inp = document.getElementById('swalFeedUrl');
                    var copyBtn = document.getElementById('swalCopyLinkBtn');
                    if (inp && copyBtn) {
                        copyBtn.addEventListener('click', function() {
                            inp.select();
                            inp.setSelectionRange(0, 99999);
                            try {
                                navigator.clipboard.writeText(inp.value);
                                copyBtn.textContent = 'คัดลอกแล้ว';
                                setTimeout(function() { copyBtn.textContent = 'คัดลอกลิงก์'; }, 2000);
                            } catch (e) {
                                document.execCommand('copy');
                                copyBtn.textContent = 'คัดลอกแล้ว';
                                setTimeout(function() { copyBtn.textContent = 'คัดลอกลิงก์'; }, 2000);
                            }
                        });
                    }
                }
            });
        });
    }
})();
</script>
<?= $this->endSection() ?>
