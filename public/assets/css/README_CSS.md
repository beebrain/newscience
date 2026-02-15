# โครงสร้าง CSS (แบ่งจาก style.css)

โหลดใน `app/Views/layouts/main_layout.php`:

**โหลดทุกหน้า (parallel):**

1. **theme.css** — ตัวแปรสี, font, spacing (ต้องโหลดก่อน)
2. **base.css** — Reset, Typography, Layout, Header/Nav, Buttons, Hero, Cards, News, Sections, Footer, Responsive
3. **components.css** — Responsive (ต่อ), Animations, Mobile Nav, Search Modal, Page Header, Utilities, Modals, News Detail, AJAX, Service Grid, Hero Science

**โหลดตามหน้าที่ใช้ (โหลดไวขึ้น):**

- **home.css** — เฉพาะหน้าแรก: Hero Carousel, Programs Carousel, Executive/Dean, Events, QA, Team Grid
- **pages.css** — ทุกหน้ายกเว้นหน้าแรก: Personnel, Executives, Academics ฯลฯ

การโหลดใช้ `?v=filemtime(base.css)` เพื่อให้ cache ทำงานได้จนกว่าจะแก้ไข CSS

สร้างไฟล์แยกด้วย: `php scripts/split_style_css.php` (จากรากโปรเจกต์)
