"""
Non-News Data Scraper for sci.uru.ac.th
ดึงข้อมูลที่ไม่เกี่ยวกับข่าว: หลักสูตร, บุคลากร, หน่วยงาน, ข้อมูลเว็บไซต์

Usage:
    pip install requests beautifulsoup4 lxml tqdm
    python scrape_non_news_data.py
"""

import requests
from bs4 import BeautifulSoup
import json
import os
import re
import time
from datetime import datetime, timedelta
from urllib.parse import urljoin, urlparse
import urllib3
import sys

# Try to import tqdm for progress bar, fallback to simple progress
try:
    from tqdm import tqdm
    HAS_TQDM = True
except ImportError:
    HAS_TQDM = False
    # Simple progress indicator
    class tqdm:
        def __init__(self, iterable=None, total=None, desc=None, unit=None):
            self.iterable = iterable
            self.total = total or (len(iterable) if iterable else 0)
            self.desc = desc or ""
            self.unit = unit or "it"
            self.current = 0
            self.start_time = time.time()
        
        def __enter__(self):
            return self
        
        def __exit__(self, *args):
            self.close()
        
        def __iter__(self):
            if self.iterable:
                for item in self.iterable:
                    yield item
                    self.update(1)
            else:
                return iter([])
        
        def update(self, n=1):
            self.current += n
            elapsed = time.time() - self.start_time
            if self.total > 0:
                percent = (self.current / self.total) * 100
                bar_length = 30
                filled = int(bar_length * self.current / self.total)
                bar = '█' * filled + '░' * (bar_length - filled)
                rate = self.current / elapsed if elapsed > 0 else 0
                eta = (self.total - self.current) / rate if rate > 0 else 0
                print(f"\r{self.desc} [{bar}] {self.current}/{self.total} ({percent:.1f}%) | "
                      f"Rate: {rate:.1f} {self.unit}/s | ETA: {eta:.0f}s", end='', flush=True)
            else:
                print(f"\r{self.desc} {self.current} {self.unit}", end='', flush=True)
        
        def close(self):
            print()  # New line after progress

# Fix encoding for Windows console
if sys.platform == 'win32':
    import io
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')
    sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8')

# Disable SSL warnings
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

# Configuration
BASE_URL = "https://sci.uru.ac.th"
SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
OUTPUT_DIR = os.path.join(SCRIPT_DIR, "scraped_data")
IMAGES_DIR = os.path.join(OUTPUT_DIR, "images")
PERSONNEL_IMAGES_DIR = os.path.join(IMAGES_DIR, "personnel")
PROGRAM_IMAGES_DIR = os.path.join(IMAGES_DIR, "programs")
HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
}

# Create output directories
os.makedirs(OUTPUT_DIR, exist_ok=True)
os.makedirs(IMAGES_DIR, exist_ok=True)
os.makedirs(PERSONNEL_IMAGES_DIR, exist_ok=True)
os.makedirs(PROGRAM_IMAGES_DIR, exist_ok=True)

def fetch_page(url, retries=3, delay=1, silent=False):
    """Fetch a page with retry logic"""
    for i in range(retries):
        try:
            if not silent:
                print(f"  Fetching: {url}")
            response = requests.get(url, headers=HEADERS, timeout=30, verify=False)
            response.raise_for_status()
            response.encoding = 'utf-8'
            return response.text
        except Exception as e:
            if not silent:
                print(f"  Error fetching {url}: {e}")
            if i < retries - 1:
                time.sleep(delay * (i + 1))
    return None

def download_image(image_url, save_dir, filename=None, retries=2):
    """Download image from URL and save to directory"""
    if not image_url or image_url.startswith('data:'):
        return None
    
    try:
        # Generate filename if not provided
        if not filename:
            parsed_url = urlparse(image_url)
            filename = os.path.basename(parsed_url.path)
            
            if not filename or '.' not in filename:
                if 'getimage' in image_url.lower():
                    match = re.search(r'getimage/(\d+)', image_url)
                    if match:
                        filename = f"item_{match.group(1)}.jpg"
                    else:
                        filename = f"image_{int(time.time())}.jpg"
                else:
                    filename = f"image_{int(time.time())}.jpg"
        
        # Clean filename
        filename = re.sub(r'[^\w\.-]', '_', filename)
        if len(filename) > 200:
            filename = filename[:200]
        
        save_path = os.path.join(save_dir, filename)
        
        # Skip if file already exists
        if os.path.exists(save_path):
            return save_path
        
        # Download image
        for i in range(retries):
            try:
                response = requests.get(image_url, headers=HEADERS, timeout=30, verify=False, stream=True)
                response.raise_for_status()
                
                content_type = response.headers.get('content-type', '')
                if 'image' not in content_type and not image_url.lower().endswith(('.jpg', '.jpeg', '.png', '.gif', '.webp')):
                    return None
                
                with open(save_path, 'wb') as f:
                    for chunk in response.iter_content(chunk_size=8192):
                        f.write(chunk)
                
                return save_path
            except Exception as e:
                if i < retries - 1:
                    time.sleep(1)
                else:
                    return None
    except Exception as e:
        return None
    
    return None

def create_slug(text):
    """Create URL-friendly slug from Thai text"""
    slug = re.sub(r'[^\w\s-]', '', text)
    slug = re.sub(r'[-\s]+', '-', slug)
    return slug.lower().strip('-')

def scrape_programs():
    """Scrape academic programs with detailed information"""
    print("\n=== Scraping Academic Programs ===")
    programs = []
    
    # Try different program page URLs
    program_urls = [
        f"{BASE_URL}/curriculum",
        f"{BASE_URL}/programs",
        f"{BASE_URL}/academics",
    ]
    
    program_page_url = None
    for url in program_urls:
        html = fetch_page(url)
        if html:
            program_page_url = url
            break
    
    if not program_page_url:
        print("  Could not find program page")
        return programs
    
    soup = BeautifulSoup(html, 'lxml')
    
    # Find program listings
    program_links = soup.find_all('a', href=re.compile(r'program|curriculum|academic', re.I))
    
    seen_programs = set()
    valid_programs = []
    
    for link in program_links:
        title = link.get_text(strip=True)
        if not title or len(title) < 5:
            continue
        
        # Skip navigation items
        if any(skip in title for skip in ['หลักสูตร', 'Curriculum', 'หน้าแรก', 'Home']):
            continue
        
        if title in seen_programs:
            continue
        seen_programs.add(title)
        
        href = link.get('href', '')
        if href.startswith('/'):
            program_url = urljoin(BASE_URL, href)
        else:
            program_url = urljoin(program_page_url, href)
        
        valid_programs.append((program_url, title))
    
    print(f"  Found {len(valid_programs)} programs to scrape")
    
    with tqdm(total=len(valid_programs), desc="  Scraping programs", unit="program") as pbar:
        for program_url, title in valid_programs:
            pbar.set_postfix(name=title[:30] + "..." if len(title) > 30 else title)
            
            # Fetch program detail page
            program_html = fetch_page(program_url, silent=True)
            description = ""
            images = []
            
            if program_html:
                program_soup = BeautifulSoup(program_html, 'lxml')
                
                # Extract description
                desc_selectors = [
                    ('div', {'class': re.compile(r'content|description|detail|about', re.I)}),
                    ('div', {'id': re.compile(r'content|description', re.I)}),
                    ('article', {}),
                    ('main', {}),
                ]
                
                for tag, attrs in desc_selectors:
                    desc_div = program_soup.find(tag, attrs)
                    if desc_div:
                        for unwanted in desc_div.find_all(['script', 'style', 'nav', 'header', 'footer']):
                            unwanted.decompose()
                        description = desc_div.get_text(separator='\n', strip=True)
                        if len(description) > 50:
                            break
                
                # Extract images
                img_tags = program_soup.find_all('img')
                for img in img_tags:
                    src = img.get('src', '') or img.get('data-src', '')
                    if src and not any(skip in src.lower() for skip in ['logo', 'icon', 'button']):
                        if src.startswith('/'):
                            src = urljoin(BASE_URL, src)
                        elif not src.startswith('http'):
                            src = urljoin(program_url, src)
                        images.append(src)
            
            # Determine level from context
            level = 'bachelor'
            if 'โท' in title or 'master' in title.lower() or 'มหาบัณฑิต' in title:
                level = 'master'
            elif 'เอก' in title or 'doctor' in title.lower() or 'ดุษฎีบัณฑิต' in title:
                level = 'doctorate'
            
            # Extract degree abbreviation
            degree_th = 'วท.บ.'
            if level == 'master':
                if 'วิศวกรรม' in title:
                    degree_th = 'วศ.ม.'
                else:
                    degree_th = 'วท.ม.'
            elif level == 'doctorate':
                degree_th = 'ปร.ด.'
            
            # Extract program name (remove degree prefix)
            name_th = title
            if 'วิทยาศาสตรบัณฑิต' in title:
                name_th = title.replace('วิทยาศาสตรบัณฑิต', '').strip()
            elif 'สาธารณสุขศาสตรบัณฑิต' in title:
                name_th = title.replace('สาธารณสุขศาสตรบัณฑิต', '').strip()
            elif 'วิศวกรรมศาสตรมหาบัณฑิต' in title:
                name_th = title.replace('วิศวกรรมศาสตรมหาบัณฑิต', '').strip()
            
            # Download program images
            downloaded_images = []
            if images:
                for img_url in images[:3]:  # Max 3 images per program
                    local_path = download_image(img_url, PROGRAM_IMAGES_DIR)
                    if local_path:
                        rel_path = os.path.relpath(local_path, SCRIPT_DIR).replace('\\', '/')
                        downloaded_images.append(rel_path)
                    time.sleep(0.2)
            
            program = {
                'name_th': name_th if name_th else title,
                'name_en': '',
                'degree_th': degree_th,
                'degree_en': '',
                'level': level,
                'url': program_url,
                'description': description[:1000] if description else '',
                'description_en': '',
                'credits': '',
                'duration': 4 if level == 'bachelor' else (2 if level == 'master' else 3),
                'image': downloaded_images[0] if downloaded_images else None,
                'images': downloaded_images,
                'status': 'active',
                'sort_order': len(programs) + 1
            }
            
            programs.append(program)
            pbar.update(1)
            time.sleep(0.3)
    
    print(f"\n  ✓ Scraped {len(programs)} programs successfully")
    return programs

def scrape_personnel():
    """Scrape personnel/staff information with detailed data"""
    print("\n=== Scraping Personnel ===")
    personnel = []
    
    # Try different personnel page URLs
    personnel_urls = [
        f"{BASE_URL}/personnel",
        f"{BASE_URL}/staff",
        f"{BASE_URL}/faculty",
        f"{BASE_URL}/personnel.php",
    ]
    
    personnel_page_url = None
    for url in personnel_urls:
        html = fetch_page(url)
        if html:
            personnel_page_url = url
            break
    
    if not personnel_page_url:
        print("  Could not find personnel page")
        return personnel
    
    soup = BeautifulSoup(html, 'lxml')
    
    # Strategy 1: Find personnel in tables
    tables = soup.find_all('table')
    table_personnel = []
    
    for table in tables:
        rows = table.find_all('tr')
        for row in rows:
            cells = row.find_all(['td', 'th'])
            if len(cells) >= 2:
                name = cells[0].get_text(strip=True)
                if name and len(name) > 2 and not any(skip in name for skip in ['ชื่อ', 'Name', 'ตำแหน่ง', 'Position']):
                    position = cells[1].get_text(strip=True) if len(cells) > 1 else ''
                    
                    # Extract image from row
                    img = row.find('img')
                    image_url = ''
                    if img:
                        src = img.get('src', '') or img.get('data-src', '')
                        if src:
                            image_url = urljoin(BASE_URL, src) if src.startswith('/') else urljoin(personnel_page_url, src)
                    
                    # Extract email
                    email = ''
                    email_link = row.find('a', href=re.compile(r'mailto:'))
                    if email_link:
                        email = email_link.get('href', '').replace('mailto:', '')
                    
                    table_personnel.append({
                        'name_th': name,
                        'position': position,
                        'email': email,
                        'image_url': image_url
                    })
    
    # Strategy 2: Find personnel in divs/cards
    if not table_personnel:
        personnel_items = soup.find_all(['div', 'li'], class_=re.compile(r'person|staff|faculty|member|card|item', re.I))
        
        for item in personnel_items[:200]:
            name_elem = item.find(['h1', 'h2', 'h3', 'h4', 'h5', 'strong', 'b'])
            if not name_elem:
                text_nodes = item.find_all(string=True, recursive=False)
                if text_nodes:
                    name_elem = text_nodes[0].parent if text_nodes else None
            
            if not name_elem:
                continue
            
            name = name_elem.get_text(strip=True)
            if not name or len(name) < 3:
                continue
            
            if any(skip in name for skip in ['ชื่อ', 'Name', 'บุคลากร', 'Personnel', 'Staff']):
                continue
            
            # Extract position
            position = ''
            item_text = item.get_text()
            position_patterns = [
                r'คณบดี|รองคณบดี|อาจารย์|professor|dean|lecturer',
                r'ผศ\.|รศ\.|ศ\.|ดร\.|อ\.|อ\.ดร\.'
            ]
            
            for pattern in position_patterns:
                match = re.search(pattern, item_text, re.I)
                if match:
                    position = match.group(0)
                    break
            
            # Extract image
            img = item.find('img')
            image_url = ''
            if img:
                src = img.get('src', '') or img.get('data-src', '')
                if src:
                    image_url = urljoin(BASE_URL, src) if src.startswith('/') else urljoin(personnel_page_url, src)
            
            # Extract email
            email = ''
            email_link = item.find('a', href=re.compile(r'mailto:'))
            if email_link:
                email = email_link.get('href', '').replace('mailto:', '')
            else:
                email_match = re.search(r'[\w\.-]+@[\w\.-]+\.\w+', item_text)
                if email_match:
                    email = email_match.group(0)
            
            table_personnel.append({
                'name_th': name,
                'position': position,
                'email': email,
                'image_url': image_url
            })
    
    print(f"  Found {len(table_personnel)} personnel to process")
    
    with tqdm(total=len(table_personnel), desc="  Processing personnel", unit="person") as pbar:
        for person_data in table_personnel:
            name = person_data['name_th']
            pbar.set_postfix(name=name[:25] + "..." if len(name) > 25 else name)
            
            # Download image
            local_image_path = None
            if person_data['image_url']:
                local_path = download_image(person_data['image_url'], PERSONNEL_IMAGES_DIR)
                if local_path:
                    local_image_path = os.path.relpath(local_path, SCRIPT_DIR).replace('\\', '/')
                time.sleep(0.2)
            
            # Determine department (can be enhanced)
            department = ''
            if 'คณบดี' in person_data['position']:
                department = 'สำนักงานคณบดี'
            elif 'รองคณบดี' in person_data['position']:
                department = 'สำนักงานคณบดี'
            
            person = {
                'name_th': person_data['name_th'],
                'name_en': '',
                'position': person_data['position'],
                'email': person_data['email'],
                'image': local_image_path if local_image_path else person_data['image_url'],
                'original_image_url': person_data['image_url'] if person_data['image_url'] else '',
                'department': department,
                'department_id': None,
                'status': 'active'
            }
            
            personnel.append(person)
            pbar.update(1)
            time.sleep(0.2)
    
    print(f"\n  ✓ Processed {len(personnel)} personnel")
    personnel_images = sum(1 for p in personnel if p.get('image') and not p.get('image', '').startswith('http'))
    if personnel_images > 0:
        print(f"  📷 Downloaded {personnel_images} personnel images")
    return personnel

def scrape_departments():
    """Scrape department information"""
    print("\n=== Scraping Departments ===")
    departments = []
    
    # Try different department page URLs
    dept_urls = [
        f"{BASE_URL}/curriculum",
        f"{BASE_URL}/about",
        f"{BASE_URL}/departments",
    ]
    
    for url in dept_urls:
        html = fetch_page(url)
        if not html:
            continue
        
        soup = BeautifulSoup(html, 'lxml')
        
        # Strategy 1: Find department links
        dept_links = soup.find_all('a', href=re.compile(r'department|dept|สาขา', re.I))
        
        # Strategy 2: Find in navigation menu
        nav_links = soup.find_all(['nav', 'ul', 'div'], class_=re.compile(r'menu|nav|list', re.I))
        for nav in nav_links:
            links = nav.find_all('a', href=True)
            dept_links.extend(links)
        
        seen_depts = set()
        
        for link in dept_links:
            name = link.get_text(strip=True)
            if not name or len(name) < 3:
                continue
            
            # Skip common navigation items
            if any(skip in name for skip in ['หน้าแรก', 'Home', 'เกี่ยวกับ', 'About', 'ติดต่อ', 'Contact']):
                continue
            
            # Check if it looks like a department name
            dept_keywords = ['สาขา', 'ภาควิชา', 'department', 'program', 'หลักสูตร']
            if not any(keyword in name.lower() or keyword in link.get('href', '').lower() for keyword in dept_keywords):
                href = link.get('href', '')
                if 'department' in href.lower() or 'dept' in href.lower():
                    pass
                else:
                    continue
            
            if name in seen_depts:
                continue
            seen_depts.add(name)
            
            href = link.get('href', '')
            if href.startswith('/'):
                dept_url = urljoin(BASE_URL, href)
            else:
                dept_url = urljoin(url, href)
            
            # Try to get description from parent element
            description = ''
            parent = link.parent
            if parent:
                desc_text = parent.get_text(strip=True)
                if len(desc_text) > len(name) + 10:
                    description = desc_text.replace(name, '').strip()
            
            dept = {
                'name_th': name,
                'name_en': '',
                'url': dept_url,
                'description': description[:500] if description else '',
                'description_en': '',
                'image': None,
                'status': 'active',
                'sort_order': len(departments) + 1
            }
            
            departments.append(dept)
        
        if departments:
            break
    
    # If no departments found, extract from programs
    if not departments:
        html = fetch_page(f"{BASE_URL}/curriculum")
        if html:
            soup = BeautifulSoup(html, 'lxml')
            program_text = soup.get_text()
            dept_patterns = [
                r'สาขาวิชา([ก-๙\w\s]+)',
                r'ภาควิชา([ก-๙\w\s]+)',
            ]
            
            for pattern in dept_patterns:
                matches = re.findall(pattern, program_text)
                for match in matches[:10]:
                    dept_name = match.strip()
                    if dept_name and len(dept_name) > 2:
                        departments.append({
                            'name_th': dept_name,
                            'name_en': '',
                            'url': f"{BASE_URL}/curriculum",
                            'description': '',
                            'description_en': '',
                            'image': None,
                            'status': 'active',
                            'sort_order': len(departments) + 1
                        })
    
    print(f"  ✓ Found {len(departments)} departments")
    return departments

def scrape_site_settings():
    """Scrape site settings and general information"""
    print("\n=== Scraping Site Settings ===")
    settings = {}
    
    html = fetch_page(BASE_URL)
    if not html:
        return settings
    
    soup = BeautifulSoup(html, 'lxml')
    
    # Extract site name
    title_tag = soup.find('title')
    if title_tag:
        settings['site_name_th'] = title_tag.get_text(strip=True)
        settings['site_name_en'] = title_tag.get_text(strip=True)
    
    # Extract meta description
    meta_desc = soup.find('meta', attrs={'name': 'description'})
    if meta_desc:
        settings['meta_description'] = meta_desc.get('content', '')
    
    # Try to find contact info
    contact_text = soup.get_text()
    
    # Extract phone
    phone_match = re.search(r'0\d{1,2}[- ]?\d{3}[- ]?\d{4}', contact_text)
    if phone_match:
        settings['phone'] = phone_match.group(0)
    
    # Extract email
    email_match = re.search(r'[\w\.-]+@[\w\.-]+\.\w+', contact_text)
    if email_match:
        settings['email'] = email_match.group(0)
    
    # Extract address
    address_patterns = [
        r'ถ\.\s*[^\n]+',
        r'อ\.\s*[^\n]+',
        r'จ\.\s*[^\n]+',
    ]
    
    address_parts = []
    for pattern in address_patterns:
        match = re.search(pattern, contact_text)
        if match:
            address_parts.append(match.group(0))
    
    if address_parts:
        settings['address_th'] = ' '.join(address_parts)
    
    # Extract university name
    uni_match = re.search(r'มหาวิทยาลัย[^\n]+', contact_text)
    if uni_match:
        settings['university_name_th'] = uni_match.group(0).strip()
    
    print(f"  ✓ Extracted {len(settings)} settings")
    return settings

def main():
    """Main scraping function"""
    print("=" * 60)
    print("sci.uru.ac.th Non-News Data Scraper")
    print("=" * 60)
    print(f"Base URL: {BASE_URL}")
    print(f"Output Directory: {OUTPUT_DIR}")
    print()
    
    all_data = {
        'scraped_at': datetime.now().isoformat(),
        'base_url': BASE_URL,
        'programs': [],
        'personnel': [],
        'departments': [],
        'site_settings': {}
    }
    
    # Scrape all non-news content
    print("\n" + "=" * 60)
    print("Starting Data Scraping (Non-News)...")
    print("=" * 60)
    
    all_data['programs'] = scrape_programs()
    all_data['personnel'] = scrape_personnel()
    all_data['departments'] = scrape_departments()
    all_data['site_settings'] = scrape_site_settings()
    
    # Count total images downloaded
    total_program_images = sum(len(p.get('images', [])) for p in all_data['programs'])
    total_personnel_images = sum(1 for p in all_data['personnel'] if p.get('image') and not p.get('image', '').startswith('http'))
    
    # Save to JSON
    output_file = os.path.join(OUTPUT_DIR, 'non_news_data.json')
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(all_data, f, ensure_ascii=False, indent=2)
    
    print("\n" + "=" * 60)
    print("Scraping Complete!")
    print("=" * 60)
    print(f"\nSummary:")
    print(f"  Programs: {len(all_data['programs'])}")
    print(f"  Personnel: {len(all_data['personnel'])}")
    print(f"  Departments: {len(all_data['departments'])}")
    print(f"\nImages Downloaded:")
    print(f"  Program Images: {total_program_images}")
    print(f"  Personnel Images: {total_personnel_images}")
    print(f"  Total Images: {total_program_images + total_personnel_images}")
    print(f"\nData saved to: {output_file}")
    print(f"Images saved to: {IMAGES_DIR}")
    print()

if __name__ == "__main__":
    main()
