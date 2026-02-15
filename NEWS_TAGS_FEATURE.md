# ฟีเจอร์ Tags ข่าว (1 ข่าวมีได้หลายชนิดข่าว)

## สรุป
- **เดิม**: ข่าวมี field `category` แบบเดียวต่อข่าว (general / student_activity / research_grant)
- **ใหม่**: ใช้ระบบ **Tags** แบบ many-to-many — 1 ข่าวสามารถมีได้หลาย tag (หลายชนิดข่าว)

## Database
1. **add_news_tags.sql**  
   - สร้างตาราง `news_tags` (id, name, slug, sort_order)  
   - สร้างตาราง pivot `news_news_tags` (news_id, news_tag_id)  
   - ใส่ tag เริ่มต้น: ข่าวทั่วไป, กิจกรรมนักศึกษา, วิจัย/ทุนวิจัย  

2. **migrate_news_category_to_tags.sql** (ถ้ามีคอลัมน์ `news.category` อยู่แล้ว)  
   - คัดลอก category เดิมเข้า `news_news_tags` เพื่อให้ข่าวเก่ายังโผล่ตาม tag ได้  

**วิธีรัน**
```bash
mysql -u root -p newscience < database/add_news_tags.sql
# ถ้า news มีคอลัมน์ category แล้ว และต้องการย้ายข้อมูล:
mysql -u root -p newscience < database/migrate_news_category_to_tags.sql
```

## Backend
- **NewsTagModel** — จัดการ tags และ pivot (getAllOrdered, getTagsByNewsId, setTagsForNews)
- **NewsModel** — เพิ่ม getPublishedByTag(slug, limit, offset)
- **Api**
  - GET `/api/news` — แต่ละข่าวมี field `tags` เป็น array ของ { id, name, slug }
  - GET `/api/news/(:num)` — มี `tags` เช่นกัน
  - GET `/api/news/category/(:segment)` — กรองตาม tag slug (หรือ category เดิมถ้ายังมี) และใส่ `tags` ในแต่ละข่าว
  - GET `/api/news-tags` — รายการ tag ทั้งหมด
- **Admin News (create/edit)** — มีช่อง "ชนิดข่าว (Tags)" เป็น checkbox หลายตัว (เลือกได้หลาย tag)

## Frontend (Ajax)
- **api.js**: `UniversityAPI.News.getByCategory(slug)`, `UniversityAPI.NewsTags.list()`
- หน้า Home ที่โหลดข่าวตาม category ยังใช้ `fetch(api/news/category/${category})` ได้เหมือนเดิม — ถ้ารัน migration แล้ว API จะกรองตาม tag และส่ง `tags[]` มาด้วย

## การแสดงผลหลาย tag ต่อข่าว
ใน API แต่ละข่าวจะมี `tags: [ { id, name, slug }, ... ]`  
ฝั่ง View สามารถแสดงเป็นป้าย tag ใต้หัวข่าวได้ เช่น:
```html
<span class="card__category"><?= esc($tag['name']) ?></span>
```
หรือใน JavaScript (เช่น home.php):
```javascript
(article.tags || []).forEach(tag => {
  html += `<span class="card__tag">${tag.name}</span>`;
});
```
