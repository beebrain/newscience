# แนวทางทำงานของเว็บไซต์แบบ Ajax เป็นหลัก

เอกสารนี้อธิบายสถาปัตยกรรม Ajax ปัจจุบันและแนวทางเลือกเทคโนโลยี (jQuery vs เทคโนโลยีใหม่กว่า) สำหรับคณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยราชภัฏอุตรดิตถ์

---

## 1. สถานะปัจจุบัน (Current Setup)

### 1.1 การนำทางแบบ SPA-like (app.js)
- **app.js** ใช้ jQuery ดักลิงก์ภายใน (`a[href]`) และโหลดเนื้อหาผ่าน **$.ajax** แทนการโหลดทั้งหน้า
- Server ตรวจสอบ `X-Requested-With: XMLHttpRequest` ใน **Pages.php** และ **Home.php** แล้วเลือก layout:
  - ถ้าเป็น Ajax request → ส่งเฉพาะส่วนเนื้อหา (`layouts/ajax_layout`)
  - ถ้าไม่ใช่ → ส่งทั้งหน้า (`layouts/main_layout`)
- การกด Back/Forward ใช้ **History API** (`pushState` / `popstate`) เพื่อโหลดหน้าที่ตรงกับ URL

### 1.2 การดึงข้อมูล (api.js + API Routes)
- **public/assets/js/api.js** ใช้ **jQuery $.ajax** เรียก API ที่กลุ่ม `api/` (Routes ใน `Config/Routes.php`)
- มี cache แบบ in-memory (TTL 5 นาที) สำหรับ response
- หน้า Home โหลดข่าวตาม category ด้วย **Fetch API** โดยตรง (`fetch(api/news/category/...)`)

### 1.3 สรุปเทคโนโลยีที่ใช้อยู่
| ส่วน | เทคโนโลยี |
|------|------------|
| SPA navigation | jQuery `$.ajax` + History API |
| API module (api.js) | jQuery `$.ajax` |
| โหลดข่าวตาม category (home) | Native `fetch()` |
| Admin (hero slides, upload) | Native `fetch()` |

---

## 2. แนะนำ: Ajax เป็นหลักด้วย jQuery หรือเทคโนโลยีใหม่กว่า

### 2.1 ตัวเลือกเทคโนโลยี

| ตัวเลือก | ข้อดี | ข้อเสีย | แนะนำเมื่อ |
|----------|--------|--------|------------|
| **jQuery $.ajax** | มีอยู่แล้ว, รองรับ browser เก่า, ใช้ร่วมกับโค้ดเดิมได้ | ต้องโหลด jQuery, รูปแบบ callback | ต้องการความต่อเนื่องกับโค้ดเดิม |
| **Native Fetch API** | ไม่ต้องพึ่ง library, Promise-based, รองรับใน browser สมัยใหม่ | ต้องจัดการ error/JSON เอง, IE ไม่รองรับ | ต้องการลด dependency และใช้ Promise/async-await |
| **Axios** | Promise-based, interceptors, รองรับ XSRF/Cancel | เพิ่ม dependency หนึ่งตัว | ต้องการ API client ที่สมบูรณ์และรองรับทุก browser |

### 2.2 แนะนำแนวทางแบบ Hybrid (ใช้ได้ทันที)
1. **คง jQuery ไว้** สำหรับ SPA navigation ใน `app.js` (เพราะดักลิงก์และอัปเดต DOM อยู่แล้ว)
2. **ใช้ Fetch API** สำหรับการเรียก API ข้อมูลใหม่ (เช่น ข่าว, personnel, settings) เพื่อลดการพึ่งพา jQuery ในส่วน data layer และใช้ async/await ได้
3. **กำหนดรูปแบบมาตรฐาน**:
   - ใช้ **BASE_URL + 'api/...'** สำหรับทุก API call
   - คืนค่า JSON จาก server เสมอ (`Content-Type: application/json`)
   - ฝั่ง client ใช้ `response.json()` แล้วค่อย render

### 2.3 โครงสร้างที่แนะนำสำหรับหน้าใหม่ (Ajax-first)
```
1. โหลดหน้าแรก → แสดง layout + โครงหน้า (อาจเป็น static HTML หรือโหลดจาก server ครั้งเดียว)
2. โหลดข้อมูลหลัก (ข่าว, เมนู, settings) ผ่าน API (GET /api/news, /api/settings ฯลฯ)
3. แทนที่ placeholder ใน DOM ด้วยผลจาก API (innerHTML หรือ template)
4. การคลิกลิงก์ภายใน → ใช้ SPA logic เหมือนเดิม (โหลด partial HTML หรือโหลดเฉพาะข้อมูลจาก API แล้ว render)
5. Form submit → ส่งผ่าน fetch() POST/PUT แล้วอัปเดต DOM ตามผลลัพธ์ (ไม่ต้อง reload ทั้งหน้า)
```

### 2.4 ตัวอย่างการเปลี่ยนไปใช้ Fetch แทน $.ajax (ข้อมูลข่าว)
```javascript
// แทนที่
$.ajax({ url: baseUrl + 'api/news', ... })

// ด้วย
const response = await fetch(baseUrl + 'api/news');
const result = await response.json();
if (result.success) { /* render result.data */ }
```

ใช้ร่วมกับ **async/await** ในฟังก์ชันที่โหลดหลาย endpoint ได้สะดวก (เช่น โหลดข่าวหลาย category พร้อมกันด้วย `Promise.all`).

---

## 3. สรุปข้อแนะนำ
- **ระยะสั้น**: คง SPA navigation แบบเดิม (jQuery ใน app.js), ค่อยๆ แปลงการเรียก API ไปใช้ **Fetch API** หรือ **api.js** ให้เป็นมาตรฐานเดียว (เลือกอย่างใดอย่างหนึ่ง)
- **ระยะกลาง**: เพิ่ม API ให้ครบทุกส่วนที่หน้าเว็บต้องใช้ (ข่าว, แท็กข่าว, personnel, settings) แล้วให้ทุกหน้าโหลดข้อมูลผ่าน API และ render ด้วย JavaScript
- **เทคโนโลยีใหม่กว่า**: ถ้าไม่จำเป็นต้องรองรับ IE แนะนำ **Fetch API + async/await**; ถ้าต้องการเครื่องมือครบ (interceptors, cancel) ให้พิจารณา **Axios**

---

## 4. ไฟล์ที่เกี่ยวข้อง
- `public/assets/js/app.js` – SPA navigation (jQuery)
- `public/assets/js/api.js` – โมดูล API (jQuery $.ajax)
- `app/Views/pages/home.php` – โหลดข่าวด้วย fetch()
- `app/Controllers/Pages.php`, `Home.php` – ตรวจสอบ `isAJAX()` และเลือก layout
- `app/Config/Routes.php` – กลุ่ม `api/*`
