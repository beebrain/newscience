# Data Mapping Summary - sci.uru.ac.th → newScience

## Overview

This document summarizes the data scraped from sci.uru.ac.th and how it maps to the new website pages.

---

## Scraped Data Summary

| Data Type | Count | Source |
|-----------|-------|--------|
| Site Settings | 22 | organized_data.json |
| Departments | 11 | organized_data.json |
| Programs (Bachelor) | 10 | organized_data.json |
| Programs (Master) | 2 | organized_data.json |
| Programs (Doctorate) | 1 | organized_data.json |
| News Articles | 143 | import_data.json |
| Quick Links | 8 | organized_data.json |

---

## Page Mapping

### 1. Homepage (`/`)
**Data Used:**
- Site name (Thai/English)
- University name (Thai/English)
- Philosophy (hero description)
- Latest 3 news articles
- Featured programs (first 6)
- Statistics (programs: 13, departments: 11, years: 89)

**Files:**
- `app/Controllers/Home.php`
- `app/Views/pages/home.php`

---

### 2. About Page (`/about`)
**Data Used:**
- Philosophy: `"สร้างองค์ความรู้และพัฒนาคนในชาติ ด้วยวิทยาศาสตร์และเทคโนโลยี"`
- Identity: `"บัณฑิตนักปฏิบัติ"`
- Vision: `"คณะวิทยาศาสตร์และเทคโนโลยี เป็นองค์กรแห่งความสุข มุ่งพัฒนาและผลิตบัณฑิตให้เป็นคนดี คนเก่ง มีจิตอาสา นำพาสังคม..."`
- Mission (4 items)
- Department list (11 departments)

**Files:**
- `app/Controllers/Pages.php` → `about()`
- `app/Views/pages/about.php`

---

### 3. Academics Page (`/academics`)
**Data Used:**
- **Bachelor Programs (10):**
  - คณิตศาสตร์ประยุกต์ (Applied Mathematics)
  - ชีววิทยา (Biology)
  - เคมี (Chemistry)
  - เทคโนโลยีสารสนเทศ (Information Technology)
  - วิทยาการคอมพิวเตอร์ (Computer Science)
  - วิทยาการข้อมูล (Data Science)
  - วิทยาศาสตร์การกีฬาและการออกกำลังกาย (Sports and Exercise Science)
  - วิทยาศาสตร์สิ่งแวดล้อม (Environmental Science)
  - สาธารณสุขศาสตร์ (Public Health)
  - อาหารและโภชนาการ (Food and Nutrition)

- **Master Programs (2):**
  - วิทยาศาสตร์ประยุกต์ - วท.ม. (M.Sc.)
  - วิศวกรรมคอมพิวเตอร์และปัญญาประดิษฐ์ - วศ.ม. (M.Eng.)

- **Doctorate Programs (1):**
  - วิทยาศาสตร์ประยุกต์ - ปร.ด. (Ph.D.)

**Files:**
- `app/Controllers/Pages.php` → `academics()`
- `app/Views/pages/academics.php`

---

### 4. News Page (`/news`)
**Data Used:**
- 143 news articles with:
  - Thai titles
  - Publication dates
  - Featured images (from sci.uru.ac.th/image/getimage/{id})
  - Source URLs

**Files:**
- `app/Controllers/Pages.php` → `news()`, `newsDetail()`
- `app/Views/pages/news.php`
- `app/Views/pages/news_detail.php`

---

### 5. Contact Page (`/contact`)
**Data Used:**
- Phone: `055-411096`
- Fax: `055-411096 ต่อ 1700`
- Email: `sci@uru.ac.th`
- Address (Thai): `คณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยราชภัฏอุตรดิตถ์ 27 ถ.อินใจมี ต.ท่าอิฐ อ.เมือง จ.อุตรดิตถ์ 53000`
- Facebook: `https://www.facebook.com/scienceuru`
- Website: `https://sci.uru.ac.th`
- Google Maps embed

**Files:**
- `app/Controllers/Pages.php` → `contact()`
- `app/Views/pages/contact.php`

---

### 6. Personnel Page (`/personnel`)
**Data Used:**
- Personnel data (ready for import when available)
- Department grouping

**Files:**
- `app/Controllers/Pages.php` → `personnel()`
- `app/Views/pages/personnel.php`

---

## Database Tables

### site_settings
Stores all configurable site content:
- `site_name_th`, `site_name_en`
- `university_name_th`, `university_name_en`
- `philosophy_th`, `vision_th`, `mission_th`
- `phone`, `fax`, `email`
- `address_th`, `address_en`
- `facebook`, `website`, `logo`

### departments
11 departments with Thai and English names.

### programs
13 programs (10 bachelor, 2 master, 1 doctorate).

### news
143 news articles with titles, slugs, images, and dates.

---

## JSON Data Files

| File | Purpose |
|------|---------|
| `organized_data.json` | Clean, structured data for import |
| `import_data.json` | Combined news and site info |
| `page_mapping.json` | Page-to-data mapping reference |
| `complete_site_data.json` | Full scrape results |

---

## How to Import Data

Run the import command:
```bash
cd C:\xampp\htdocs\newScience
php spark import:data
```

This will import:
- 22 site settings
- 11 departments
- 13 programs
- News articles (if not already imported)

---

## Notes

1. **Thai Content**: All major content is in Thai language
2. **Images**: News images are external links to sci.uru.ac.th
3. **Personnel**: Structure ready, waiting for data
4. **Timestamps**: Using Buddhist calendar (พ.ศ.) for Thai dates
