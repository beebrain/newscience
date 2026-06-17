<?php /*
 * กับดักบอตสแปม (honeypot) — วางหลัง csrf_field() ในฟอร์มสาธารณะ
 * ช่อง name="website" ถูกซ่อนจากผู้ใช้จริงและต้องเว้นว่างเสมอ
 * ดันออกนอกจอแทน display:none เพราะบอตบางตัวข้ามช่อง display:none
 * ชื่อช่องต้องตรงกับ Concerns\AntiBot::antiBotHoneypotName()
 */ ?>
<div class="antibot-hp" aria-hidden="true" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;">
    <label>กรุณาเว้นว่างช่องนี้
        <input type="text" name="website" tabindex="-1" autocomplete="off" value="">
    </label>
</div>
