"""
Enhanced sci.uru.ac.th Web Scraper
Extracts complete content including full articles, personnel, programs, and site info.

Usage:
    pip install -r requirements.txt
    python scrape_full_content.py
"""

import requests
from bs4 import BeautifulSoup
import json
import os
import re
import time
import hashlib
from datetime import datetime
from urllib.parse import urljoin, urlparse
from concurrent.futures import ThreadPoolExecutor, as_completed

# Configuration
BASE_URL = "https://sci.uru.ac.th"
OUTPUT_DIR = "scraped_data"
IMAGES_DIR = os.path.join(OUTPUT_DIR, "images")
HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
    'Accept-Language': 'th,en;q=0.9',
}

# Create directories
os.makedirs(OUTPUT_DIR, exist_ok=True)
os.makedirs(IMAGES_DIR, exist_ok=True)
os.makedirs(os.path.join(IMAGES_DIR, "news"), exist_ok=True)
os.makedirs(os.path.join(IMAGES_DIR, "personnel"), exist_ok=True)

# Disable SSL warnings
import urllib3
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

def fetch_page(url, retries=3):
    """Fetch a page with retry logic"""
    for i in range(retries):
        try:
            response = requests.get(url, headers=HEADERS, timeout=30, verify=False)
            response.raise_for_status()
            response.encoding = 'utf-8'
            return response.text
        except Exception as e:
            print(f"  Error fetching {url}: {e}")
            if i < retries - 1:
                time.sleep(2)
    return None

def download_image(url, save_path):
    """Download image and save to path"""
    try:
        if not url or url.startswith('data:'):
            return None
        
        response = requests.get(url, headers=HEADERS, timeout=30, verify=False, stream=True)
        if response.status_code == 200:
            content_type = response.headers.get('content-type', '')
            if 'image' in content_type or url.endswith(('.jpg', '.jpeg', '.png', '.gif', '.webp')):
                with open(save_path, 'wb') as f:
                    for chunk in response.iter_content(chunk_size=8192):
                        f.write(chunk)
                return save_path
    except Exception as e:
        print(f"  Error downloading image {url}: {e}")
    return None

def parse_thai_date(date_str):
    """Convert Thai Buddhist year to Gregorian ISO format"""
    thai_months = {
        'มกราคม': 1, 'กุมภาพันธ์': 2, 'มีนาคม': 3, 'เมษายน': 4,
        'พฤษภาคม': 5, 'มิถุนายน': 6, 'กรกฎาคม': 7, 'สิงหาคม': 8,
        'กันยายน': 9, 'ตุลาคม': 10, 'พฤศจิกายน': 11, 'ธันวาคม': 12,
        'ม.ค.': 1, 'ก.พ.': 2, 'มี.ค.': 3, 'เม.ย.': 4,
        'พ.ค.': 5, 'มิ.ย.': 6, 'ก.ค.': 7, 'ส.ค.': 8,
        'ก.ย.': 9, 'ต.ค.': 10, 'พ.ย.': 11, 'ธ.ค.': 12
    }
    
    if not date_str:
        return None
    
    # Pattern: day month year (e.g., "12 มกราคม 2569")
    match = re.search(r'(\d{1,2})\s+(\S+)\s+(\d{4})', str(date_str))
    if match:
        day = int(match.group(1))
        month_name = match.group(2)
        year = int(match.group(3))
        
        # Convert Buddhist year to Gregorian
        if year > 2500:
            year -= 543
        
        month = thai_months.get(month_name, 1)
        try:
            return f"{year}-{month:02d}-{day:02d}"
        except:
            return None
    
    return None

def clean_text(text):
    """Clean and normalize text"""
    if not text:
        return ""
    # Remove extra whitespace
    text = re.sub(r'\s+', ' ', text)
    # Remove control characters
    text = ''.join(char for char in text if ord(char) >= 32 or char in '\n\t')
    return text.strip()

def scrape_single_news(news_id):
    """Scrape a single news article by ID"""
    url = f"{BASE_URL}/news/{news_id}"
    html = fetch_page(url)
    
    if not html:
        return None
    
    soup = BeautifulSoup(html, 'lxml')
    
    # Extract title
    title = ""
    title_elem = soup.find('h1') or soup.find('h2', class_=re.compile(r'title'))
    if title_elem:
        title = clean_text(title_elem.get_text())
    
    # Extract content - look for main content area
    content = ""
    content_selectors = [
        ('div', {'class': re.compile(r'content|article|news-content|post-content|entry-content')}),
        ('article', {}),
        ('div', {'class': re.compile(r'body|main|detail')}),
    ]
    
    for tag, attrs in content_selectors:
        content_elem = soup.find(tag, attrs)
        if content_elem:
            # Remove scripts, styles, and navigation
            for unwanted in content_elem.find_all(['script', 'style', 'nav', 'header', 'footer']):
                unwanted.decompose()
            content = clean_text(content_elem.get_text())
            if len(content) > 50:  # Got meaningful content
                break
    
    # Extract images
    images = []
    seen_urls = set()
    
    # Look for main article image
    for img in soup.find_all('img', src=True):
        src = img.get('src', '')
        if not src:
            continue
        
        # Skip logos, icons, and UI elements
        if any(skip in src.lower() for skip in ['logo', 'icon', 'avatar', 'button', 'arrow', 'social']):
            continue
        
        full_url = urljoin(BASE_URL, src)
        
        # Skip duplicates and external images
        if full_url in seen_urls:
            continue
        seen_urls.add(full_url)
        
        # Prioritize getimage URLs (actual news images)
        if 'getimage' in src or 'upload' in src or 'news' in src:
            images.insert(0, full_url)
        else:
            images.append(full_url)
    
    # Extract date
    date = None
    date_patterns = [
        r'(\d{1,2})\s+(มกราคม|กุมภาพันธ์|มีนาคม|เมษายน|พฤษภาคม|มิถุนายน|กรกฎาคม|สิงหาคม|กันยายน|ตุลาคม|พฤศจิกายน|ธันวาคม)\s+(\d{4})',
        r'(\d{1,2})/(\d{1,2})/(\d{4})',
    ]
    
    page_text = soup.get_text()
    for pattern in date_patterns:
        match = re.search(pattern, page_text)
        if match:
            date = parse_thai_date(match.group(0))
            if date:
                break
    
    if not title:
        return None
    
    return {
        'id': news_id,
        'title': title,
        'content': content[:5000] if content else "",  # Limit content length
        'images': images[:10],  # Limit to 10 images
        'date': date,
        'url': url
    }

def scrape_news_list():
    """Get list of all news IDs from the news listing page"""
    print("\n=== Scraping News List ===")
    news_ids = set()
    
    # Try multiple pages
    for page in range(1, 20):  # Try first 20 pages
        url = f"{BASE_URL}/news?page={page}"
        html = fetch_page(url)
        
        if not html:
            break
        
        soup = BeautifulSoup(html, 'lxml')
        
        # Find all news links
        found_on_page = 0
        for link in soup.find_all('a', href=True):
            href = link['href']
            match = re.search(r'/news/(\d+)', href)
            if match:
                news_ids.add(int(match.group(1)))
                found_on_page += 1
        
        if found_on_page == 0:
            break  # No more news on this page
        
        print(f"  Page {page}: found {found_on_page} news links")
        time.sleep(0.3)
    
    print(f"  Total unique news IDs: {len(news_ids)}")
    return sorted(news_ids, reverse=True)

def scrape_all_news():
    """Scrape all news articles"""
    print("\n=== Scraping All News Articles ===")
    news_ids = scrape_news_list()
    news_list = []
    
    for i, news_id in enumerate(news_ids):
        print(f"  Scraping news {news_id} ({i+1}/{len(news_ids)})...")
        article = scrape_single_news(news_id)
        if article:
            news_list.append(article)
            print(f"    [OK] {article['title'][:50]}...")
        time.sleep(0.3)  # Be nice to the server
    
    print(f"\n  Total articles scraped: {len(news_list)}")
    return news_list

def scrape_personnel():
    """Scrape personnel/staff information"""
    print("\n=== Scraping Personnel ===")
    personnel_list = []
    
    # Main personnel page
    urls_to_try = [
        f"{BASE_URL}/personnel",
        f"{BASE_URL}/administration",
    ]
    
    for url in urls_to_try:
        html = fetch_page(url)
        if not html:
            continue
        
        soup = BeautifulSoup(html, 'lxml')
        
        # Find personnel cards/sections
        # Look for common patterns in personnel listings
        person_containers = soup.find_all(['div', 'article', 'li'], class_=re.compile(r'person|staff|member|card|profile|team', re.I))
        
        if not person_containers:
            # Try finding by image patterns (personnel usually have photos)
            person_containers = soup.find_all('div', class_=re.compile(r'col|item|box'))
        
        for container in person_containers:
            person = extract_person_info(container)
            if person and person.get('name'):
                personnel_list.append(person)
    
    # Also try to scrape from each department page
    department_urls = [
        '/doctopic/237',  # คณิตศาสตร์
        '/doctopic/236',  # ชีววิทยา
        '/doctopic/235',  # เคมี
        '/doctopic/234',  # เทคโนโลยีสารสนเทศ
        '/doctopic/233',  # วิทยาการคอมพิวเตอร์
        '/doctopic/232',  # วิทยาการข้อมูล
        '/doctopic/231',  # วิทยาศาสตร์การกีฬา
        '/doctopic/230',  # วิทยาศาสตร์สิ่งแวดล้อม
        '/doctopic/229',  # สาธารณสุขศาสตร์
        '/doctopic/228',  # อาหารและโภชนาการ
    ]
    
    for dept_url in department_urls:
        html = fetch_page(f"{BASE_URL}{dept_url}")
        if html:
            soup = BeautifulSoup(html, 'lxml')
            for container in soup.find_all(['div', 'article'], class_=re.compile(r'person|staff|member|card')):
                person = extract_person_info(container)
                if person and person.get('name'):
                    personnel_list.append(person)
        time.sleep(0.3)
    
    # Remove duplicates by name
    seen_names = set()
    unique_personnel = []
    for person in personnel_list:
        if person['name'] not in seen_names:
            seen_names.add(person['name'])
            unique_personnel.append(person)
    
    print(f"  Total personnel found: {len(unique_personnel)}")
    return unique_personnel

def extract_person_info(container):
    """Extract person information from a container element"""
    person = {
        'name': '',
        'title': '',
        'position': '',
        'department': '',
        'email': '',
        'phone': '',
        'image': '',
        'education': '',
        'expertise': ''
    }
    
    # Extract image
    img = container.find('img', src=True)
    if img:
        src = img.get('src', '')
        if src and 'logo' not in src.lower() and 'icon' not in src.lower():
            person['image'] = urljoin(BASE_URL, src)
    
    # Extract name - look for h tags or specific classes
    name_elem = container.find(['h2', 'h3', 'h4', 'h5'], class_=re.compile(r'name|title', re.I))
    if not name_elem:
        name_elem = container.find(['h2', 'h3', 'h4', 'h5'])
    if not name_elem:
        name_elem = container.find(['span', 'p'], class_=re.compile(r'name', re.I))
    
    if name_elem:
        full_name = clean_text(name_elem.get_text())
        # Parse Thai academic titles
        titles = ['ศ.ดร.', 'รศ.ดร.', 'ผศ.ดร.', 'อ.ดร.', 'ศ.', 'รศ.', 'ผศ.', 'อ.', 'ดร.', 'นาย', 'นาง', 'นางสาว']
        for t in titles:
            if full_name.startswith(t):
                person['title'] = t
                full_name = full_name[len(t):].strip()
                break
        person['name'] = full_name
    
    # Extract position
    pos_elem = container.find(['p', 'span', 'div'], class_=re.compile(r'position|role|title|job', re.I))
    if pos_elem:
        person['position'] = clean_text(pos_elem.get_text())
    
    # Extract email
    email_elem = container.find('a', href=re.compile(r'mailto:'))
    if email_elem:
        href = email_elem.get('href', '')
        person['email'] = href.replace('mailto:', '').strip()
    
    # Extract phone
    phone_match = re.search(r'(\d{2,3}[-.\s]?\d{3,4}[-.\s]?\d{3,4})', container.get_text())
    if phone_match:
        person['phone'] = phone_match.group(1)
    
    return person

def scrape_programs():
    """Scrape curriculum/program information"""
    print("\n=== Scraping Programs/Curriculum ===")
    programs = []
    
    # Program URLs from the scraped links
    program_pages = [
        ('/doctopic/237', 'คณิตศาสตร์ประยุกต์', 'Applied Mathematics'),
        ('/doctopic/236', 'ชีววิทยา', 'Biology'),
        ('/doctopic/235', 'เคมี', 'Chemistry'),
        ('/doctopic/234', 'เทคโนโลยีสารสนเทศ', 'Information Technology'),
        ('/doctopic/233', 'วิทยาการคอมพิวเตอร์', 'Computer Science'),
        ('/doctopic/232', 'วิทยาการข้อมูล', 'Data Science'),
        ('/doctopic/231', 'วิทยาศาสตร์การกีฬาและการออกกำลังกาย', 'Sports Science'),
        ('/doctopic/230', 'วิทยาศาสตร์สิ่งแวดล้อม', 'Environmental Science'),
        ('/doctopic/229', 'สาธารณสุขศาสตร์', 'Public Health'),
        ('/doctopic/228', 'อาหารและโภชนาการ', 'Food and Nutrition'),
    ]
    
    for url_path, name_th, name_en in program_pages:
        url = f"{BASE_URL}{url_path}"
        html = fetch_page(url)
        
        program = {
            'name_th': name_th,
            'name_en': name_en,
            'degree_th': 'วิทยาศาสตรบัณฑิต',
            'degree_en': 'Bachelor of Science',
            'level': 'bachelor',
            'description': '',
            'url': url,
            'image': ''
        }
        
        if html:
            soup = BeautifulSoup(html, 'lxml')
            
            # Extract description
            content = soup.find('div', class_=re.compile(r'content|description|detail'))
            if content:
                program['description'] = clean_text(content.get_text())[:2000]
            
            # Extract image
            img = soup.find('img', src=re.compile(r'curriculum|program|course'))
            if img:
                program['image'] = urljoin(BASE_URL, img.get('src', ''))
        
        programs.append(program)
        print(f"  [OK] {name_th}")
        time.sleep(0.3)
    
    # Add graduate programs
    graduate_programs = [
        {
            'name_th': 'วิทยาศาสตร์ประยุกต์',
            'name_en': 'Applied Science',
            'degree_th': 'วิทยาศาสตรมหาบัณฑิต',
            'degree_en': 'Master of Science',
            'level': 'master',
            'description': 'หลักสูตรปริญญาโท วิทยาศาสตร์ประยุกต์',
            'url': f"{BASE_URL}/curriculum"
        },
        {
            'name_th': 'วิทยาศาสตร์ประยุกต์',
            'name_en': 'Applied Science',
            'degree_th': 'ปรัชญาดุษฎีบัณฑิต',
            'degree_en': 'Doctor of Philosophy',
            'level': 'doctorate',
            'description': 'หลักสูตรปริญญาเอก วิทยาศาสตร์ประยุกต์',
            'url': f"{BASE_URL}/curriculum"
        },
        {
            'name_th': 'วิศวกรรมคอมพิวเตอร์และปัญญาประดิษฐ์',
            'name_en': 'Computer Engineering and Artificial Intelligence',
            'degree_th': 'วิศวกรรมศาสตรมหาบัณฑิต',
            'degree_en': 'Master of Engineering',
            'level': 'master',
            'description': 'หลักสูตรวิศวกรรมศาสตรมหาบัณฑิต สาขาวิชาวิศวกรรมคอมพิวเตอร์และปัญญาประดิษฐ์',
            'url': f"{BASE_URL}/curriculum"
        }
    ]
    
    programs.extend(graduate_programs)
    print(f"\n  Total programs: {len(programs)}")
    return programs

def scrape_site_info():
    """Scrape general site information"""
    print("\n=== Scraping Site Information ===")
    
    site_info = {
        'name_th': 'คณะวิทยาศาสตร์และเทคโนโลยี',
        'name_en': 'Faculty of Science and Technology',
        'university_th': 'มหาวิทยาลัยราชภัฏอุตรดิตถ์',
        'university_en': 'Uttaradit Rajabhat University',
        'phone': '055-411096',
        'fax': '055-411096 ต่อ 1700',
        'email': 'sci@uru.ac.th',
        'address_th': 'คณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยราชภัฏอุตรดิตถ์ 27 ถ.อินใจมี ต.ท่าอิฐ อ.เมือง จ.อุตรดิตถ์ 53000',
        'address_en': 'Faculty of Science and Technology, Uttaradit Rajabhat University, 27 Injaimee Rd., Tha-It, Muang, Uttaradit 53000, Thailand',
        'facebook': 'https://www.facebook.com/scienceuru',
        'website': 'https://sci.uru.ac.th',
        'vision': '',
        'mission': '',
        'about': ''
    }
    
    # Scrape administration page for vision/mission
    html = fetch_page(f"{BASE_URL}/administration")
    if html:
        soup = BeautifulSoup(html, 'lxml')
        
        # Look for vision/mission content
        content = soup.find('div', class_=re.compile(r'content|main'))
        if content:
            text = content.get_text()
            
            # Try to extract vision
            vision_match = re.search(r'วิสัยทัศน์[:\s]*([^พันธกิจ]+)', text)
            if vision_match:
                site_info['vision'] = clean_text(vision_match.group(1))[:500]
            
            # Try to extract mission
            mission_match = re.search(r'พันธกิจ[:\s]*(.+?)(?:อัตลักษณ์|เป้าหมาย|$)', text, re.DOTALL)
            if mission_match:
                site_info['mission'] = clean_text(mission_match.group(1))[:1000]
            
            site_info['about'] = clean_text(text)[:2000]
    
    # Scrape homepage for additional info
    html = fetch_page(BASE_URL)
    if html:
        soup = BeautifulSoup(html, 'lxml')
        
        # Try to get logo
        logo = soup.find('img', src=re.compile(r'logo', re.I))
        if logo:
            site_info['logo'] = urljoin(BASE_URL, logo.get('src', ''))
        
        # Get footer contact info
        footer = soup.find('footer')
        if footer:
            footer_text = footer.get_text()
            
            # Extract phone
            phone_match = re.search(r'(\d{2,3}[-.\s]?\d{3,4}[-.\s]?\d{3,4})', footer_text)
            if phone_match:
                site_info['phone'] = phone_match.group(1)
            
            # Extract email
            email_match = re.search(r'[\w.-]+@[\w.-]+\.\w+', footer_text)
            if email_match:
                site_info['email'] = email_match.group(0)
    
    print(f"  [OK] Site info extracted")
    return site_info

def scrape_activities():
    """Scrape activities/gallery"""
    print("\n=== Scraping Activities ===")
    activities = []
    
    html = fetch_page(f"{BASE_URL}/act")
    if not html:
        return activities
    
    soup = BeautifulSoup(html, 'lxml')
    
    # Find activity items
    activity_links = soup.find_all('a', href=re.compile(r'/act/\d+'))
    seen_ids = set()
    
    for link in activity_links:
        href = link.get('href', '')
        match = re.search(r'/act/(\d+)', href)
        if match:
            act_id = match.group(1)
            if act_id in seen_ids:
                continue
            seen_ids.add(act_id)
            
            # Scrape individual activity
            act_url = f"{BASE_URL}/act/{act_id}"
            act_html = fetch_page(act_url)
            
            if act_html:
                act_soup = BeautifulSoup(act_html, 'lxml')
                
                title = ""
                title_elem = act_soup.find(['h1', 'h2'])
                if title_elem:
                    title = clean_text(title_elem.get_text())
                
                images = []
                for img in act_soup.find_all('img', src=True):
                    src = img.get('src', '')
                    if 'logo' not in src.lower() and 'icon' not in src.lower():
                        images.append(urljoin(BASE_URL, src))
                
                if title:
                    activities.append({
                        'id': act_id,
                        'title': title,
                        'images': images[:20],
                        'url': act_url
                    })
                    print(f"  [OK] {title[:50]}...")
            
            time.sleep(0.3)
    
    print(f"\n  Total activities: {len(activities)}")
    return activities

def download_news_images(news_list, limit=100):
    """Download featured images for news articles"""
    print("\n=== Downloading News Images ===")
    downloaded = 0
    
    for article in news_list[:limit]:
        if article.get('images'):
            # Get the main image (usually getimage URL)
            main_image = None
            for img_url in article['images']:
                if 'getimage' in img_url:
                    main_image = img_url
                    break
            
            if not main_image:
                main_image = article['images'][0]
            
            # Generate filename
            ext = '.jpg'
            if '.png' in main_image.lower():
                ext = '.png'
            elif '.gif' in main_image.lower():
                ext = '.gif'
            
            filename = f"news_{article['id']}{ext}"
            save_path = os.path.join(IMAGES_DIR, "news", filename)
            
            if not os.path.exists(save_path):
                result = download_image(main_image, save_path)
                if result:
                    article['local_image'] = f"images/news/{filename}"
                    downloaded += 1
                    print(f"  [OK] Downloaded {filename}")
            else:
                article['local_image'] = f"images/news/{filename}"
            
            time.sleep(0.1)
    
    print(f"\n  Total images downloaded: {downloaded}")

def main():
    print("=" * 60)
    print("Enhanced sci.uru.ac.th Web Scraper")
    print(f"Started at: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print("=" * 60)
    
    # Scrape all content
    data = {
        'metadata': {
            'source': BASE_URL,
            'scraped_at': datetime.now().isoformat(),
            'description': 'Faculty of Science and Technology, Uttaradit Rajabhat University'
        },
        'site_info': scrape_site_info(),
        'news': scrape_all_news(),
        'personnel': scrape_personnel(),
        'programs': scrape_programs(),
        'activities': scrape_activities()
    }
    
    # Download news images
    download_news_images(data['news'], limit=50)
    
    # Save complete data
    output_file = os.path.join(OUTPUT_DIR, 'complete_content.json')
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(data, f, ensure_ascii=False, indent=2)
    print(f"\n[OK] Saved complete content to: {output_file}")
    
    # Summary
    print("\n" + "=" * 60)
    print("SUMMARY")
    print("=" * 60)
    print(f"Site info: [OK]")
    print(f"News articles: {len(data['news'])}")
    print(f"Personnel: {len(data['personnel'])}")
    print(f"Programs: {len(data['programs'])}")
    print(f"Activities: {len(data['activities'])}")
    print(f"\nCompleted at: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")

if __name__ == "__main__":
    main()
