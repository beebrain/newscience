"""
Complete Content Scraper for sci.uru.ac.th
ดึงข้อมูลทั้งหมดจากเว็บไซต์ sci.uru.ac.th เพื่อใช้สร้างเว็บใหม่

Usage:
    pip install requests beautifulsoup4 lxml
    python scrape_all_content.py
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
NEWS_IMAGES_DIR = os.path.join(IMAGES_DIR, "news")
PERSONNEL_IMAGES_DIR = os.path.join(IMAGES_DIR, "personnel")
PROGRAM_IMAGES_DIR = os.path.join(IMAGES_DIR, "programs")
HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
}

# Create output directories
os.makedirs(OUTPUT_DIR, exist_ok=True)
os.makedirs(IMAGES_DIR, exist_ok=True)
os.makedirs(NEWS_IMAGES_DIR, exist_ok=True)
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
            # Extract filename from URL
            parsed_url = urlparse(image_url)
            filename = os.path.basename(parsed_url.path)
            
            # If no extension, try to get from content-type or use default
            if not filename or '.' not in filename:
                # Try to get extension from URL or use .jpg as default
                if 'getimage' in image_url.lower():
                    # Extract ID from getimage URL
                    match = re.search(r'getimage/(\d+)', image_url)
                    if match:
                        filename = f"news_{match.group(1)}.jpg"
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
                
                # Check if it's actually an image
                content_type = response.headers.get('content-type', '')
                if 'image' not in content_type and not image_url.lower().endswith(('.jpg', '.jpeg', '.png', '.gif', '.webp')):
                    return None
                
                # Save image
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
    # Remove special characters, keep Thai and English
    slug = re.sub(r'[^\w\s-]', '', text)
    slug = re.sub(r'[-\s]+', '-', slug)
    return slug.lower().strip('-')

def parse_thai_date(date_str):
    """Convert Thai Buddhist year date to Gregorian ISO format"""
    thai_months = {
        'มกราคม': 1, 'กุมภาพันธ์': 2, 'มีนาคม': 3, 'เมษายน': 4,
        'พฤษภาคม': 5, 'มิถุนายน': 6, 'กรกฎาคม': 7, 'สิงหาคม': 8,
        'กันยายน': 9, 'ตุลาคม': 10, 'พฤศจิกายน': 11, 'ธันวาคม': 12
    }
    
    # Pattern: "12 มกราคม 2569" or "12 ม.ค. 2569"
    patterns = [
        r'(\d+)\s+(\S+)\s+(\d{4})',  # Full month name
        r'(\d{1,2})/(\d{1,2})/(\d{4})',  # DD/MM/YYYY
    ]
    
    try:
        for pattern in patterns:
            match = re.search(pattern, date_str)
            if match:
                if '/' in date_str:
                    day, month, year = match.groups()
                    try:
                        day, month, year = int(day), int(month), int(year)
                    except ValueError:
                        continue
                else:
                    try:
                        day = int(match.group(1))
                        month_name = match.group(2)
                        year = int(match.group(3))
                        month = thai_months.get(month_name, 1)
                    except (ValueError, IndexError):
                        continue
                
                # Convert Buddhist year to Gregorian
                if year > 2500:
                    year -= 543
                
                return f"{year}-{month:02d}-{day:02d}"
    except Exception as e:
        print(f"    Date parsing error: {e}")
    
    return None

def scrape_news(years_back=2):
    """Scrape all news articles from the last N years"""
    print(f"\n=== Scraping News Articles (Last {years_back} years) ===")
    news_list = []
    
    # Calculate date threshold (N years ago)
    threshold_date = datetime.now() - timedelta(days=years_back * 365)
    
    # Try different news page URLs
    news_urls = [
        f"{BASE_URL}/news",
        f"{BASE_URL}/index.php/news",
        f"{BASE_URL}/news.php"
    ]
    
    news_page_url = None
    for url in news_urls:
        html = fetch_page(url)
        if html:
            news_page_url = url
            break
    
    if not news_page_url:
        print("  Could not find news page")
        return news_list
    
    soup = BeautifulSoup(html, 'lxml')
    
    # Find all news links - try multiple patterns
    news_links = []
    
    # Pattern 1: /news/123
    links1 = soup.find_all('a', href=re.compile(r'/news/\d+'))
    news_links.extend(links1)
    
    # Pattern 2: news.php?id=123
    links2 = soup.find_all('a', href=re.compile(r'news\.php\?id=\d+'))
    news_links.extend(links2)
    
    # Pattern 3: Any link with "news" in href
    links3 = soup.find_all('a', href=re.compile(r'news', re.I))
    news_links.extend(links3)
    
    seen_urls = set()
    
    # Filter and prepare news links
    valid_links = []
    for link in news_links[:100]:  # Increase to 100 articles
        href = link.get('href', '')
        if not href:
            continue
        
        # Normalize URL
        if href.startswith('/'):
            full_url = urljoin(BASE_URL, href)
        elif href.startswith('http'):
            full_url = href
        else:
            full_url = urljoin(news_page_url, href)
        
        if full_url in seen_urls:
            continue
        seen_urls.add(full_url)
        
        title = link.get_text(strip=True)
        if not title or len(title) < 5:
            continue
        
        valid_links.append((full_url, title))
    
    # Scrape with progress bar
    print(f"  Found {len(valid_links)} news articles to check")
    print(f"  Filtering news from last {years_back} years (since {threshold_date.strftime('%Y-%m-%d')})")
    
    skipped_old = 0
    total_images_downloaded = 0
    with tqdm(total=len(valid_links), desc="  Scraping news", unit="article") as pbar:
        for full_url, title in valid_links:
            pbar.set_postfix(title=title[:30] + "..." if len(title) > 30 else title)
            
            # Fetch article page
            article_html = fetch_page(full_url, silent=True)
            if not article_html:
                pbar.update(1)
                continue
            
            article_soup = BeautifulSoup(article_html, 'lxml')
            
            # Extract content - try multiple strategies
            content = ""
            content_selectors = [
                ('div', {'class': re.compile(r'content|article|news-content|post-content|entry-content|detail', re.I)}),
                ('div', {'id': re.compile(r'content|article|news|post|detail', re.I)}),
                ('article', {}),
                ('main', {}),
                ('div', {'class': re.compile(r'body|main|text', re.I)}),
                ('div', {'class': re.compile(r'news|post', re.I)}),
            ]
            
            for tag, attrs in content_selectors:
                content_div = article_soup.find(tag, attrs)
                if content_div:
                    # Remove unwanted elements
                    for unwanted in content_div.find_all(['script', 'style', 'nav', 'header', 'footer', 'aside', 'form']):
                        unwanted.decompose()
                    
                    # Get text content
                    text_content = content_div.get_text(separator='\n', strip=True)
                    
                    # Clean up text
                    text_content = re.sub(r'\n{3,}', '\n\n', text_content)  # Remove excessive newlines
                    text_content = re.sub(r'[ \t]+', ' ', text_content)  # Normalize spaces
                    
                    if len(text_content) > 100:  # Got meaningful content
                        content = text_content
                        break
            
            # If still no content, try to get all text from body excluding navigation
            if not content or len(content) < 50:
                body = article_soup.find('body')
                if body:
                    # Remove navigation, header, footer
                    for unwanted in body.find_all(['nav', 'header', 'footer', 'aside', 'script', 'style']):
                        unwanted.decompose()
                    
                    # Try to find the main content area by looking for largest text block
                    all_divs = body.find_all('div')
                    largest_content = ""
                    for div in all_divs:
                        text = div.get_text(separator=' ', strip=True)
                        if len(text) > len(largest_content) and len(text) > 200:
                            # Check if it's not navigation or menu
                            if not any(skip in div.get('class', []) for skip in ['nav', 'menu', 'sidebar', 'header', 'footer']):
                                largest_content = text
                    
                    if largest_content:
                        content = largest_content
            
            # Extract images - prioritize news images
            images = []
            seen_urls = set()
            img_tags = article_soup.find_all('img')
            
            for img in img_tags:
                src = img.get('src', '') or img.get('data-src', '')
                if not src:
                    continue
                
                # Skip logos, icons, and UI elements
                if any(skip in src.lower() for skip in ['logo', 'icon', 'avatar', 'button', 'arrow', 'social', 'fb.png', 'news.png']):
                    continue
                
                # Normalize URL
                if src.startswith('/'):
                    src = urljoin(BASE_URL, src)
                elif not src.startswith('http'):
                    src = urljoin(full_url, src)
                
                # Skip duplicates
                if src in seen_urls:
                    continue
                seen_urls.add(src)
                
                # Prioritize getimage URLs (actual news images)
                if 'getimage' in src or 'upload' in src or 'news' in src.lower():
                    images.insert(0, src)
                else:
                    images.append(src)
            
            # Limit to 10 images
            images = images[:10]
            
            # Download images
            downloaded_images = []
            if images:
                for img_url in images[:5]:  # Download max 5 images per article
                    local_path = download_image(img_url, NEWS_IMAGES_DIR)
                    if local_path:
                        # Store relative path from project root
                        rel_path = os.path.relpath(local_path, SCRIPT_DIR).replace('\\', '/')
                        downloaded_images.append(rel_path)
                        total_images_downloaded += 1
                    time.sleep(0.2)  # Be polite
            
            # Extract date
            date_str = None
            published_date = None
            try:
                date_elem = article_soup.find(string=re.compile(r'\d{1,2}\s+[ก-๙]+\s+\d{4}'))
                if date_elem:
                    date_str = parse_thai_date(str(date_elem))
                    if date_str:
                        try:
                            published_date = datetime.strptime(date_str, '%Y-%m-%d')
                        except:
                            pass
            except:
                pass
            
            # If no date found, try to extract from URL or use current date
            if not published_date:
                # Try to extract date from URL (e.g., /news/723 might have date info)
                # For now, assume recent if no date found
                published_date = datetime.now()
                date_str = published_date.strftime('%Y-%m-%d')
            
            # Filter by date - only include news from last N years
            if published_date < threshold_date:
                skipped_old += 1
                pbar.update(1)
                continue  # Skip old news
            
            # Extract excerpt (first 200 chars)
            excerpt = content[:200].strip() if content else title
            
            news_item = {
                'title': title,
                'slug': create_slug(title),
                'content': content,
                'excerpt': excerpt,
                'url': full_url,
                'images': downloaded_images,  # Local paths to downloaded images
                'original_images': images[:5],  # Keep original URLs for reference
                'featured_image': downloaded_images[0] if downloaded_images else None,
                'published_at': date_str,
                'status': 'published'
            }
            
            news_list.append(news_item)
            pbar.update(1)
            time.sleep(0.3)  # Be polite
    
    print(f"\n  ✓ Scraped {len(news_list)} news articles successfully")
    if skipped_old > 0:
        print(f"  ⊘ Skipped {skipped_old} articles older than {years_back} years")
    if total_images_downloaded > 0:
        print(f"  📷 Downloaded {total_images_downloaded} news images")
    return news_list

def scrape_programs():
    """Scrape academic programs"""
    print("\n=== Scraping Academic Programs ===")
    programs = []
    
    # Try different program page URLs
    program_urls = [
        f"{BASE_URL}/programs",
        f"{BASE_URL}/academics",
        f"{BASE_URL}/curriculum",
        f"{BASE_URL}/index.php/programs"
    ]
    
    for url in program_urls:
        html = fetch_page(url)
        if not html:
            continue
        
        soup = BeautifulSoup(html, 'lxml')
        
        # Find program listings
        program_links = soup.find_all('a', href=re.compile(r'program|curriculum|academic', re.I))
        
        seen_programs = set()
        
        for link in program_links:
            title = link.get_text(strip=True)
            if not title or len(title) < 5:
                continue
            
            # Skip if already seen
            if title in seen_programs:
                continue
            seen_programs.add(title)
            
            href = link.get('href', '')
            if href.startswith('/'):
                program_url = urljoin(BASE_URL, href)
            else:
                program_url = urljoin(url, href)
            
            print(f"  Found program: {title}")
            
            # Determine level from context
            level = 'bachelor'
            if 'โท' in title or 'master' in title.lower():
                level = 'master'
            elif 'เอก' in title or 'doctor' in title.lower():
                level = 'doctorate'
            
            # Extract degree abbreviation
            degree_th = 'วท.บ.'
            if level == 'master':
                degree_th = 'วท.ม.'
            elif level == 'doctorate':
                degree_th = 'ปร.ด.'
            
            program = {
                'name_th': title,
                'name_en': '',
                'degree_th': degree_th,
                'level': level,
                'url': program_url,
                'description': '',
                'status': 'active'
            }
            
            programs.append(program)
        
        if programs:
            break
    
    print(f"  ✓ Found {len(programs)} programs")
    return programs

def scrape_personnel():
    """Scrape personnel/staff information"""
    print("\n=== Scraping Personnel ===")
    personnel = []
    
    # Try different personnel page URLs
    personnel_urls = [
        f"{BASE_URL}/personnel",
        f"{BASE_URL}/staff",
        f"{BASE_URL}/faculty",
        f"{BASE_URL}/index.php/personnel",
        f"{BASE_URL}/personnel.php",
        f"{BASE_URL}/about/personnel",
    ]
    
    for url in personnel_urls:
        html = fetch_page(url)
        if not html:
            continue
        
        soup = BeautifulSoup(html, 'lxml')
        
        # Strategy 1: Find personnel in tables
        tables = soup.find_all('table')
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
                            src = img.get('src', '')
                            if src:
                                image_url = urljoin(BASE_URL, src) if src.startswith('/') else urljoin(url, src)
                        
                        # Extract email
                        email = ''
                        email_link = row.find('a', href=re.compile(r'mailto:'))
                        if email_link:
                            email = email_link.get('href', '').replace('mailto:', '')
                        
                        person = {
                            'name_th': name,
                            'name_en': '',
                            'position': position,
                            'email': email,
                            'image': image_url,
                            'department': '',
                            'status': 'active'
                        }
                        personnel.append(person)
        
        # Strategy 2: Find personnel in divs/cards
        if not personnel:
            personnel_items = soup.find_all(['div', 'li'], class_=re.compile(r'person|staff|faculty|member|card|item', re.I))
            
            for item in personnel_items[:200]:  # Limit to 200
                # Find name
                name_elem = item.find(['h1', 'h2', 'h3', 'h4', 'h5', 'strong', 'b'])
                if not name_elem:
                    # Try finding in first text node
                    text_nodes = item.find_all(string=True, recursive=False)
                    if text_nodes:
                        name_elem = text_nodes[0].parent if text_nodes else None
                
                if not name_elem:
                    continue
                
                name = name_elem.get_text(strip=True)
                if not name or len(name) < 3:
                    continue
                
                # Skip headers
                if any(skip in name for skip in ['ชื่อ', 'Name', 'บุคลากร', 'Personnel', 'Staff']):
                    continue
                
                # Extract position
                position = ''
                position_patterns = [
                    r'คณบดี|รองคณบดี|อาจารย์|professor|dean|lecturer',
                    r'ผศ\.|รศ\.|ศ\.|ดร\.|อ\.|อ\.ดร\.'
                ]
                
                item_text = item.get_text()
                for pattern in position_patterns:
                    match = re.search(pattern, item_text, re.I)
                    if match:
                        position = match.group(0)
                        break
                
                # Extract image
                img = item.find('img')
                image_url = ''
                local_image_path = None
                if img:
                    src = img.get('src', '') or img.get('data-src', '')
                    if src:
                        image_url = urljoin(BASE_URL, src) if src.startswith('/') else urljoin(url, src)
                        # Download image
                        if image_url:
                            local_path = download_image(image_url, PERSONNEL_IMAGES_DIR)
                            if local_path:
                                local_image_path = os.path.relpath(local_path, SCRIPT_DIR).replace('\\', '/')
                            time.sleep(0.2)
                
                # Extract email
                email = ''
                email_link = item.find('a', href=re.compile(r'mailto:'))
                if email_link:
                    email = email_link.get('href', '').replace('mailto:', '')
                else:
                    # Try to find email in text
                    email_match = re.search(r'[\w\.-]+@[\w\.-]+\.\w+', item_text)
                    if email_match:
                        email = email_match.group(0)
                
                person = {
                    'name_th': name,
                    'name_en': '',
                    'position': position,
                    'email': email,
                    'image': local_image_path if local_image_path else image_url,
                    'original_image_url': image_url if image_url else '',
                    'department': '',
                    'status': 'active'
                }
                personnel.append(person)
        
        if personnel:
            print(f"  Found {len(personnel)} personnel from {url}")
            break
    
    # Count downloaded images
    personnel_images = sum(1 for p in personnel if p.get('image') and not p.get('image', '').startswith('http'))
    print(f"  ✓ Found {len(personnel)} personnel")
    if personnel_images > 0:
        print(f"  📷 Downloaded {personnel_images} personnel images")
    return personnel

def scrape_departments():
    """Scrape department information"""
    print("\n=== Scraping Departments ===")
    departments = []
    
    # Try different department page URLs
    dept_urls = [
        f"{BASE_URL}/departments",
        f"{BASE_URL}/about",
        f"{BASE_URL}/index.php/departments",
        f"{BASE_URL}/department",
        f"{BASE_URL}/curriculum",  # Sometimes departments are listed here
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
                # Still add if URL suggests it's a department
                href = link.get('href', '')
                if 'department' in href.lower() or 'dept' in href.lower():
                    pass  # OK to add
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
            
            print(f"  Found department: {name}")
            
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
                'status': 'active'
            }
            
            departments.append(dept)
        
        if departments:
            break
    
    # If no departments found, try to extract from programs page
    if not departments:
        html = fetch_page(f"{BASE_URL}/curriculum")
        if html:
            soup = BeautifulSoup(html, 'lxml')
            # Look for department names in program listings
            program_text = soup.get_text()
            dept_patterns = [
                r'สาขาวิชา([ก-๙\w\s]+)',
                r'ภาควิชา([ก-๙\w\s]+)',
            ]
            
            for pattern in dept_patterns:
                matches = re.findall(pattern, program_text)
                for match in matches[:10]:  # Limit to 10
                    dept_name = match.strip()
                    if dept_name and len(dept_name) > 2:
                        departments.append({
                            'name_th': dept_name,
                            'name_en': '',
                            'url': f"{BASE_URL}/curriculum",
                            'description': '',
                            'status': 'active'
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
    
    # Extract address (look for common Thai address patterns)
    address_patterns = [
        r'ถ\.\s*[^\n]+',
        r'อ\.\s*[^\n]+',
        r'จ\.\s*[^\n]+',
    ]
    
    for pattern in address_patterns:
        match = re.search(pattern, contact_text)
        if match:
            settings['address_th'] = match.group(0)
            break
    
    print(f"  ✓ Extracted {len(settings)} settings")
    return settings

def main():
    """Main scraping function"""
    print("=" * 60)
    print("sci.uru.ac.th Content Scraper")
    print("=" * 60)
    print(f"Base URL: {BASE_URL}")
    print(f"Output Directory: {OUTPUT_DIR}")
    print()
    
    all_data = {
        'scraped_at': datetime.now().isoformat(),
        'base_url': BASE_URL,
        'news': [],
        'programs': [],
        'personnel': [],
        'departments': [],
        'site_settings': {}
    }
    
    # Scrape all content with progress
    print("\n" + "=" * 60)
    print("Starting Data Scraping...")
    print("=" * 60)
    
    # Scrape news from last 2 years
    all_data['news'] = scrape_news(years_back=2)
    all_data['programs'] = scrape_programs()
    all_data['personnel'] = scrape_personnel()
    all_data['departments'] = scrape_departments()
    all_data['site_settings'] = scrape_site_settings()
    
    # Save to JSON
    output_file = os.path.join(OUTPUT_DIR, 'all_content.json')
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(all_data, f, ensure_ascii=False, indent=2)
    
    # Count total images downloaded
    total_news_images = sum(len(n.get('images', [])) for n in all_data['news'])
    total_personnel_images = sum(1 for p in all_data['personnel'] if p.get('image') and not p.get('image', '').startswith('http'))
    
    print("\n" + "=" * 60)
    print("Scraping Complete!")
    print("=" * 60)
    print(f"\nSummary:")
    print(f"  News Articles: {len(all_data['news'])}")
    print(f"  Programs: {len(all_data['programs'])}")
    print(f"  Personnel: {len(all_data['personnel'])}")
    print(f"  Departments: {len(all_data['departments'])}")
    print(f"\nImages Downloaded:")
    print(f"  News Images: {total_news_images}")
    print(f"  Personnel Images: {total_personnel_images}")
    print(f"  Total Images: {total_news_images + total_personnel_images}")
    print(f"\nData saved to: {output_file}")
    print(f"Images saved to: {IMAGES_DIR}")
    print()

if __name__ == "__main__":
    main()
