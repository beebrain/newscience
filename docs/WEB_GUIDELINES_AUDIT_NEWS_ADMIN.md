# Web Interface Guidelines – Audit หน้าข่าว Admin

ตรวจตาม `.cursor/rule/web.mdc` (Vercel Web Interface Guidelines)

---

## app/Views/admin/news/index.php

index.php:66 - img มี alt="" (decorative/thumb) ผ่าน แต่ไม่มี width/height → CLS ได้
index.php:76 - "..." ควรเป็น "…" (typography)
index.php:100 - วันที่ใช้ date() แบบ hardcode ควรใช้ Intl (locale)
index.php:104 - ปุ่มลบใช้ <a> + onclick confirm ผ่าน (destructive มี confirmation)
index.php:65-66 - img ข่าวไม่มี width/height (Images rule)

---

## app/Views/admin/news/create.php

create.php:58 - Anti-pattern: <div onclick="..."> ควรเป็น <button type="button"> หรือ <label> หรือ role="button" + tabindex="0" + keydown (semantic / a11y)
create.php:33 - placeholder ไม่ลงท้าย "…" และไม่มีตัวอย่าง (Forms)
create.php:58 - ปุ่ม/พื้นที่คลิกเลือกไฟล์ไม่มี aria-label (ถ้าเป็น icon-only)

---

## app/Views/admin/news/edit.php

edit.php:78 - img ภาพปก alt="" ควรเป็น alt อธิบายข่าวถ้ามี (หรือ alt="" ถ้า decorative)
edit.php:174 - img รูปเพิ่ม alt=""
edit.php:74 - featuredImageBox เป็น div ที่ bind click ใน JS → เหมือน create ควรใช้ <button> หรือ role="button" (semantic)
edit.php:47 - placeholder ไม่ลงท้าย "…"
edit.php:214-218 - ปุ่มลบรูป .remove-btn เป็น icon-only ต้องมี aria-label

---

## public/assets/css/admin.css

admin.css:70 - transition: all 0.2s → ควรระบุ property (Animation)
admin.css:189 - transition: all 0.2s → 同上
admin.css:289-291 - outline: none มี box-shadow ชดเชย ผ่าน แต่ควรใช้ :focus-visible แทน :focus (Focus States)
admin.css:322 - transition border-color ผ่าน
admin.css:633 - transition: all 0.2s → ระบุ property
admin.css:668 - transition: all 0.2s → ระบุ property
admin.css:387 - transition: transform ผ่าน
admin.css:484 - transition: background ผ่าน
admin.css:691 - transition: border-color ผ่าน
admin.css - ไม่มี prefers-reduced-motion สำหรับ animation (Accessibility)

---

## สรุปที่แก้ในโค้ดแล้ว

- **Typography:** ใช้ "…" แทน "..." (index)
- **Focus:** เพิ่ม `.form-control:focus-visible` ชดเชย outline (admin.css)
- **Animation:** เปลี่ยน `transition: all` เป็น property ชัดเจน (sidebar, .btn, .form-check-inline, .radio-option)
- **Accessibility:** ปุ่มลบรูปมี `aria-label="ลบรูปภาพนี้"` / `aria-label="ลบรูป"`, SVG ในหัว section มี `aria-hidden="true"`, กล่องเลือกภาพใช้ `<label for="featured_image">` แทน div+onclick
- **Forms:** placeholder ลงท้าย "…" และมีตัวอย่าง (เช่น ประกาศรับสมัคร…)
- **Images:** รูป thumb มี width/height (72x48, 280 auto), รูปใน grid preview 120x120
- **Reduced motion:** เพิ่ม `@media (prefers-reduced-motion: reduce)` ใน admin.css

## ที่ยังไม่แก้ (ยอมรับได้ / ต้องแก้ที่ backend)

- **วันที่:** ยังใช้ `date('d/m/Y')` แบบ hardcode — ควรใช้ Intl.DateTimeFormat ถ้าต้องการ i18n เต็มรูปแบบ
- **beforeunload:** ยังไม่มีเตือนก่อนออกเมื่อมีการแก้ไขแต่ยังไม่บันทึก (ถ้าต้องการเพิ่มค่อยทำ)
