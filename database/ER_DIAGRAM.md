# ER Diagram - ฐานข้อมูล NewScience (คณะวิทยาศาสตร์และเทคโนโลยี)

## Mermaid ER Diagram

ดูได้ใน GitHub, VS Code (Mermaid extension), หรือ [Mermaid Live Editor](https://mermaid.live)

```mermaid
erDiagram
    user ||--o{ news : author_id
    departments ||--o{ personnel : department_id
    departments ||--o{ programs : department_id
    personnel ||--o{ personnel_programs : ""
    programs ||--o{ personnel_programs : ""
    personnel }o--o| programs : program_id
    news ||--o{ news_images : news_id
    activities ||--o{ activity_images : activity_id

    user {
        int uid PK
        varchar email
        varchar password
        varchar role
        enum status
    }

    site_settings {
        int id PK
        varchar setting_key UK
        text setting_value
    }

    departments {
        int id PK
        varchar name_th name_en
        int head_personnel_id
        int sort_order
        enum status
    }

    personnel {
        int id PK
        varchar first_name last_name
        varchar position
        int department_id FK
        int program_id FK
        varchar email image
        int sort_order
        enum status
    }

    programs {
        int id PK
        varchar name_th name_en
        enum level
        int department_id FK
        int coordinator_id
        int sort_order
        enum status
    }

    personnel_programs {
        int id PK
        int personnel_id FK
        int program_id FK
        varchar role_in_curriculum
    }

    news {
        int id PK
        varchar title slug
        int author_id FK
        datetime published_at
    }

    news_images {
        int id PK
        int news_id FK
        varchar image_path
    }

    activities {
        int id PK
        varchar title slug
        enum status
    }

    activity_images {
        int id PK
        int activity_id FK
        varchar image_path
    }

    links {
        int id PK
        varchar title url
        varchar category
    }

    hero_slides {
        int id PK
        varchar title image
        int sort_order
    }
```

---

## ความสัมพันธ์ (Relationships)

| จาก (Parent) | ไป (Child) | ความสัมพันธ์ | คอลัมน์ |
|-------------|------------|--------------|---------|
| **user** | news | 1 → 0..* | news.author_id → user.uid |
| **departments** | personnel | 1 → 0..* | personnel.department_id → departments.id |
| **departments** | programs | 1 → 0..* | programs.department_id → departments.id |
| **personnel** | personnel_programs | 1 → 0..* | personnel_programs.personnel_id → personnel.id |
| **programs** | personnel_programs | 1 → 0..* | personnel_programs.program_id → programs.id |
| **personnel** | programs | 0..1 → 0..1 | personnel.program_id → programs.id (หลักสูตรหลัก) |
| **personnel** | departments | 0..1 | departments.head_personnel_id → personnel.id |
| **personnel** | programs | 0..1 | programs.coordinator_id → personnel.id |
| **news** | news_images | 1 → 0..* | news_images.news_id → news.id |
| **activities** | activity_images | 1 → 0..* | activity_images.activity_id → activities.id |

---

## ตารางสรุป

| ตาราง | คำอธิบาย |
|-------|----------|
| **user** | ผู้ใช้ระบบ (admin/editor) |
| **site_settings** | การตั้งค่าเว็บ (ชื่อคณะ, ที่อยู่, วิสัยทัศน์ ฯลฯ) |
| **departments** | แผนก/สาขาวิชา |
| **personnel** | บุคลากร (อาจารย์, ผู้บริหาร) |
| **programs** | หลักสูตร |
| **personnel_programs** | Pivot: บุคลากรสังกัดหลายหลักสูตร + บทบาท (ประธานหลักสูตร ฯลฯ) |
| **news** | ข่าวสาร |
| **news_images** | รูปประกอบข่าว (หลายรูปต่อข่าว) |
| **activities** | กิจกรรม/อัลบั้ม |
| **activity_images** | รูปกิจกรรม |
| **links** | ลิงก์ภายนอก/ภายใน |
| **hero_slides** | สไลด์หน้าแรก |

---

## หมายเหตุ

- **personnel.program_id** = หลักสูตรหลัก (หลักสูตรแรก) สำหรับ backward compatibility
- **personnel_programs** = ความสัมพันธ์หลายต่อหลาย (อาจารย์ 1 คน สังกัดได้หลายหลักสูตร) พร้อมบทบาท (role_in_curriculum)
- **news.author_id** อ้างอิง **user.uid** (schema บางไฟล์อาจไม่มี FK)
- **departments.head_personnel_id**, **programs.coordinator_id** อ้างอิง **personnel.id** (optional)
