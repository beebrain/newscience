<?php
/*
 * Google reCAPTCHA v3 — วางไว้ "ภายใน" <form> (หลัง csrf_field())
 * รับตัวแปร $rcAction (ชื่อ action เช่น 'complaint', 'scienceweek')
 * ถ้ายังไม่ได้ตั้ง siteKey ใน .env → ไม่แสดงอะไรเลย (reCAPTCHA ปิดอยู่)
 * ชื่อ action ต้องตรงกับที่ controller ส่งให้ passesRecaptcha()
 */
$siteKey = (string) config('Recaptcha')->siteKey;
if ($siteKey === '') {
    return;
}
$rcAction = $rcAction ?? 'submit';
?>
<input type="hidden" name="g-recaptcha-response" value="">
<script src="https://www.google.com/recaptcha/api.js?render=<?= esc($siteKey, 'attr') ?>"></script>
<script>
(function () {
    var script = document.currentScript;
    var form = script ? script.closest('form') : null;
    if (!form) { return; }

    var siteKey = <?= json_encode($siteKey) ?>;
    var action = <?= json_encode($rcAction) ?>;
    var done = false;

    form.addEventListener('submit', function (e) {
        if (done) { return; }            // submit จริงรอบสองหลังได้ token แล้ว
        e.preventDefault();

        if (typeof grecaptcha === 'undefined') { done = true; form.submit(); return; } // fail-open

        grecaptcha.ready(function () {
            grecaptcha.execute(siteKey, { action: action }).then(function (token) {
                var field = form.querySelector('[name="g-recaptcha-response"]');
                if (field) { field.value = token; }
                done = true;
                form.submit();
            }).catch(function () { done = true; form.submit(); });
        });
    });
})();
</script>
