# Logout ของ Edoc — Redirect กลับ newScience

## วัตถุประสงค์

เมื่อผู้ใช้ล็อกอินผ่าน **Edoc** (จาก newScience) แล้วไปกด **ออกจากระบบ** บน Edoc ต้องให้ redirect กลับมาหน้า login ของ newScience (sci.uru.ac.th) ไม่ค้างอยู่ที่ Edoc

---

## สิ่งที่ Edoc ต้อง implement

### 1. URL ปลายทาง (คงที่)

หลัง logout ให้ redirect ไปที่ URL นี้เสมอ (แทน `sci.uru.ac.th` ด้วยโดเมนจริงของ newScience ถ้าต่างกัน):

```
https://sci.uru.ac.th/admin/edoc-logout-return
```

### 2. ลำดับการทำงานใน Logout

1. ล้าง session ของ Edoc (ผู้ใช้ออกจากระบบฝั่ง Edoc)
2. Redirect ไปที่ URL ข้างบน

### 3. ตัวอย่างโค้ด (Pseudocode)

```php
// ตัวอย่างใน Controller ที่จัดการ logout (เช่น AuthController)
public function logout()
{
    // 1. ล้าง session ของ Edoc
    session()->destroy(); // หรือเทียบเท่าของ framework ที่ Edoc ใช้

    // 2. Redirect กลับ newScience
    $newscienceLogoutReturn = config('NewscienceSso')->logoutReturnUrl
        ?: 'https://sci.uru.ac.th/admin/edoc-logout-return';
    return redirect()->to($newscienceLogoutReturn);
}
```

### 4. Config แนะนำใน Edoc

เก็บ URL ปลายทางใน config หรือ `.env` เพื่อเปลี่ยนโดเมนได้ง่าย:

- ตัวแปรตัวอย่าง: `newscience.logout_return_url` หรือใน `NewscienceSso`
- ค่า: `https://sci.uru.ac.th/admin/edoc-logout-return`

---

## ฝั่ง newScience (มีอยู่แล้ว)

- **Route:** `GET /admin/edoc-logout-return` → `Admin\Auth::edocLogoutReturn`
- **การทำงาน:** redirect ไป `admin/login?logout=1` และแสดงข้อความ "ออกจากระบบแล้ว"

ดังนั้น Edoc แค่ redirect ไปที่ URL ข้างบนหลัง logout ก็เพียงพอ

---

## ทางเลือก: รับ return_url จาก query

ถ้า Edoc ต้องการรับ `return_url` จาก query (เช่น เมื่อ newScience ส่ง user ไป logout ที่ Edoc พร้อม `return_url`):

- ตรวจ **allowlist** ว่า `return_url` เป็นโดเมน newScience เท่านั้น (เช่น `https://sci.uru.ac.th`)
- หลังล้าง session แล้ว redirect ไปที่ `return_url` ที่ส่งมา
- ตัวอย่าง URL ที่ newScience อาจส่งมา:  
  `https://edoc.sci.uru.ac.th/index.php/auth/logout?return_url=https%3A%2F%2Fsci.uru.ac.th%2Fadmin%2Fedoc-logout-return`
