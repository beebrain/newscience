"""
Comprehensive sci.uru.ac.th Scraper
Analyzes all sublinks and maps content to appropriate website pages.

Usage:
    python scrape_all_pages.py
"""

import requests
from bs4 import BeautifulSoup
import json
import os
import re
import time
from datetime import datetime
from urllib.parse import urljoin, urlparse
from collections import defaultdict

# Configuration
BASE_URL = "https://sci.uru.ac.th"
OUTPUT_DIR = "scraped_data"
HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    'Accept-Language': 'th,en;q=0.9',
}

os.makedirs(OUTPUT_DIR, exist_ok=True)

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
            print(f"  Error: {e}")
            if i < retries - 1:
                time.sleep(2)
    return None

def clean_text(text):
    """Clean text content"""
    if not text:
        return ""
    text = re.sub(r'\s+', ' ', text)
    return text.strip()

def extract_page_content(url, soup):
    """Extract main content from a page"""
    content = {
        'url': url,
        'title': '',
        'content': '',
        'images': [],
        'links': []
    }
    
    # Get title
    title_elem = soup.find('h1') or soup.find('title')
    if title_elem:
        content['title'] = clean_text(title_elem.get_text())
    
    # Get main content
    main_content = soup.find('div', class_=re.compile(r'content|main|article', re.I))
    if main_content:
        content['content'] = clean_text(main_content.get_text())[:3000]
    
    # Get images
    for img in soup.find_all('img', src=True):
        src = img.get('src', '')
        if src and 'logo' not in src.lower() and 'icon' not in src.lower():
            content['images'].append(urljoin(BASE_URL, src))
    
    return content

def scrape_administration_page():
    """Scrape administration/about page for vision, mission, etc."""
    print("\n=== Scraping Administration Page ===")
    
    data = {
        'vision_th': '',
        'vision_en': '',
        'mission_th': '',
        'mission_en': '',
        'identity_th': '',
        'philosophy_th': '',
        'objectives': [],
        'history': ''
    }
    
    html = fetch_page(f"{BASE_URL}/administration")
    if not html:
        return data
    
    soup = BeautifulSoup(html, 'lxml')
    page_text = soup.get_text()
    
    # Extract Vision
    vision_patterns = [
        r'วิสัยทัศน์\s*[:\s]*([^พันธกิจปรัชญา]+)',
        r'Vision\s*[:\s]*([^Mission]+)',
    ]
    for pattern in vision_patterns:
        match = re.search(pattern, page_text, re.IGNORECASE | re.DOTALL)
        if match:
            data['vision_th'] = clean_text(match.group(1))[:500]
            break
    
    # Extract Mission
    mission_patterns = [
        r'พันธกิจ\s*[:\s]*(.+?)(?:อัตลักษณ์|เอกลักษณ์|เป้าหมาย|วัตถุประสงค์|ค่านิยม|$)',
    ]
    for pattern in mission_patterns:
        match = re.search(pattern, page_text, re.DOTALL)
        if match:
            data['mission_th'] = clean_text(match.group(1))[:1000]
            break
    
    # Extract Philosophy
    philosophy_match = re.search(r'ปรัชญา\s*[:\s]*([^วิสัยทัศน์พันธกิจ]+)', page_text)
    if philosophy_match:
        data['philosophy_th'] = clean_text(philosophy_match.group(1))[:500]
    
    # Extract Identity
    identity_match = re.search(r'อัตลักษณ์\s*[:\s]*([^เอกลักษณ์พันธกิจ]+)', page_text)
    if identity_match:
        data['identity_th'] = clean_text(identity_match.group(1))[:500]
    
    print(f"  Vision: {len(data['vision_th'])} chars")
    print(f"  Mission: {len(data['mission_th'])} chars")
    
    return data

def scrape_personnel_page():
    """Scrape personnel/staff information"""
    print("\n=== Scraping Personnel Page ===")
    
    personnel = {
        'executives': [],  # Dean, Vice Deans
        'staff': [],       # Administrative staff
        'faculty': {}      # Faculty by department
    }
    
    html = fetch_page(f"{BASE_URL}/personnel")
    if not html:
        return personnel
    
    soup = BeautifulSoup(html, 'lxml')
    
    # Find all personnel sections
    sections = soup.find_all(['div', 'section'], class_=re.compile(r'personnel|staff|team', re.I))
    
    # Try to find individual personnel cards
    cards = soup.find_all(['div', 'article'], class_=re.compile(r'card|person|member|profile', re.I))
    
    for card in cards:
        person = extract_person_from_card(card)
        if person and person.get('name'):
            # Categorize by position
            position = person.get('position', '').lower()
            if 'คณบดี' in position or 'dean' in position.lower():
                personnel['executives'].append(person)
            else:
                personnel['staff'].append(person)
    
    # Also try to scrape from table format
    tables = soup.find_all('table')
    for table in tables:
        rows = table.find_all('tr')
        for row in rows:
            cells = row.find_all(['td', 'th'])
            if len(cells) >= 2:
                name = clean_text(cells[0].get_text())
                position = clean_text(cells[1].get_text()) if len(cells) > 1 else ''
                if name and len(name) > 3:
                    personnel['staff'].append({
                        'name': name,
                        'position': position,
                        'image': '',
                        'email': '',
                        'phone': ''
                    })
    
    print(f"  Executives: {len(personnel['executives'])}")
    print(f"  Staff: {len(personnel['staff'])}")
    
    return personnel

def extract_person_from_card(card):
    """Extract person info from a card element"""
    person = {
        'name': '',
        'title': '',
        'position': '',
        'department': '',
        'email': '',
        'phone': '',
        'image': '',
        'education': []
    }
    
    # Get image
    img = card.find('img', src=True)
    if img:
        src = img.get('src', '')
        if 'logo' not in src.lower():
            person['image'] = urljoin(BASE_URL, src)
    
    # Get name from headings
    name_elem = card.find(['h2', 'h3', 'h4', 'h5'])
    if name_elem:
        full_name = clean_text(name_elem.get_text())
        # Parse Thai academic titles
        titles = ['ศ.ดร.', 'รศ.ดร.', 'ผศ.ดร.', 'อ.ดร.', 'ศ.', 'รศ.', 'ผศ.', 'อ.', 'ดร.', 
                  'นาย', 'นาง', 'นางสาว', 'Mr.', 'Mrs.', 'Ms.', 'Dr.', 'Prof.']
        for t in titles:
            if full_name.startswith(t):
                person['title'] = t
                full_name = full_name[len(t):].strip()
                break
        person['name'] = full_name
    
    # Get position
    pos_elem = card.find(['p', 'span', 'div'], class_=re.compile(r'position|title|role', re.I))
    if pos_elem:
        person['position'] = clean_text(pos_elem.get_text())
    
    # Get email
    email_link = card.find('a', href=re.compile(r'mailto:'))
    if email_link:
        person['email'] = email_link.get('href', '').replace('mailto:', '').strip()
    
    return person

def scrape_curriculum_pages():
    """Scrape all curriculum/program pages"""
    print("\n=== Scraping Curriculum Pages ===")
    
    programs = {
        'bachelor': [],
        'master': [],
        'doctorate': []
    }
    
    # Program URLs with their details
    program_urls = [
        ('/doctopic/237', 'คณิตศาสตร์ประยุกต์', 'Applied Mathematics', 'bachelor'),
        ('/doctopic/236', 'ชีววิทยา', 'Biology', 'bachelor'),
        ('/doctopic/235', 'เคมี', 'Chemistry', 'bachelor'),
        ('/doctopic/234', 'เทคโนโลยีสารสนเทศ', 'Information Technology', 'bachelor'),
        ('/doctopic/233', 'วิทยาการคอมพิวเตอร์', 'Computer Science', 'bachelor'),
        ('/doctopic/232', 'วิทยาการข้อมูล', 'Data Science', 'bachelor'),
        ('/doctopic/231', 'วิทยาศาสตร์การกีฬาและการออกกำลังกาย', 'Sports and Exercise Science', 'bachelor'),
        ('/doctopic/230', 'วิทยาศาสตร์สิ่งแวดล้อม', 'Environmental Science', 'bachelor'),
        ('/doctopic/229', 'สาธารณสุขศาสตร์', 'Public Health', 'bachelor'),
        ('/doctopic/228', 'อาหารและโภชนาการ', 'Food and Nutrition', 'bachelor'),
    ]
    
    for url_path, name_th, name_en, level in program_urls:
        url = f"{BASE_URL}{url_path}"
        html = fetch_page(url)
        
        program = {
            'name_th': name_th,
            'name_en': name_en,
            'degree_th': 'วิทยาศาสตรบัณฑิต (วท.บ.)',
            'degree_en': 'Bachelor of Science (B.Sc.)',
            'level': level,
            'department_th': f'สาขาวิชา{name_th}',
            'department_en': f'Department of {name_en}',
            'description': '',
            'objectives': [],
            'career_opportunities': [],
            'curriculum_structure': '',
            'url': url,
            'facebook': '',
            'website': ''
        }
        
        if html:
            soup = BeautifulSoup(html, 'lxml')
            
            # Extract description
            content_div = soup.find('div', class_=re.compile(r'content|main'))
            if content_div:
                program['description'] = clean_text(content_div.get_text())[:2000]
            
            # Look for Facebook link
            fb_link = soup.find('a', href=re.compile(r'facebook\.com'))
            if fb_link:
                program['facebook'] = fb_link.get('href', '')
        
        programs[level].append(program)
        print(f"  [OK] {name_th}")
        time.sleep(0.3)
    
    # Add graduate programs
    programs['master'].append({
        'name_th': 'วิทยาศาสตร์ประยุกต์',
        'name_en': 'Applied Science',
        'degree_th': 'วิทยาศาสตรมหาบัณฑิต (วท.ม.)',
        'degree_en': 'Master of Science (M.Sc.)',
        'level': 'master',
        'description': 'หลักสูตรระดับปริญญาโท สาขาวิทยาศาสตร์ประยุกต์',
        'url': f'{BASE_URL}/curriculum'
    })
    
    programs['master'].append({
        'name_th': 'วิศวกรรมคอมพิวเตอร์และปัญญาประดิษฐ์',
        'name_en': 'Computer Engineering and Artificial Intelligence',
        'degree_th': 'วิศวกรรมศาสตรมหาบัณฑิต (วศ.ม.)',
        'degree_en': 'Master of Engineering (M.Eng.)',
        'level': 'master',
        'description': 'หลักสูตรวิศวกรรมศาสตรมหาบัณฑิต สาขาวิชาวิศวกรรมคอมพิวเตอร์และปัญญาประดิษฐ์',
        'url': f'{BASE_URL}/curriculum'
    })
    
    programs['doctorate'].append({
        'name_th': 'วิทยาศาสตร์ประยุกต์',
        'name_en': 'Applied Science',
        'degree_th': 'ปรัชญาดุษฎีบัณฑิต (ปร.ด.)',
        'degree_en': 'Doctor of Philosophy (Ph.D.)',
        'level': 'doctorate',
        'description': 'หลักสูตรระดับปริญญาเอก สาขาวิทยาศาสตร์ประยุกต์',
        'url': f'{BASE_URL}/curriculum'
    })
    
    print(f"\n  Bachelor: {len(programs['bachelor'])}")
    print(f"  Master: {len(programs['master'])}")
    print(f"  Doctorate: {len(programs['doctorate'])}")
    
    return programs

def scrape_all_links():
    """Scrape all links from the homepage"""
    print("\n=== Scraping All Links ===")
    
    links = {
        'navigation': [],
        'quick_links': [],
        'services': [],
        'external': [],
        'documents': []
    }
    
    html = fetch_page(BASE_URL)
    if not html:
        return links
    
    soup = BeautifulSoup(html, 'lxml')
    
    for link in soup.find_all('a', href=True):
        href = link.get('href', '')
        text = clean_text(link.get_text())
        
        if not href or href.startswith('#') or href.startswith('javascript'):
            continue
        
        full_url = urljoin(BASE_URL, href)
        
        link_data = {
            'text': text,
            'url': full_url
        }
        
        # Categorize links
        if 'sci.uru.ac.th' in full_url:
            if '/doctopic/' in href or '/docs/' in href:
                links['documents'].append(link_data)
            elif '/personnel' in href or '/curriculum' in href or '/news' in href:
                links['navigation'].append(link_data)
            else:
                links['quick_links'].append(link_data)
        else:
            links['external'].append(link_data)
    
    # Remove duplicates
    for key in links:
        seen = set()
        unique = []
        for item in links[key]:
            if item['url'] not in seen:
                seen.add(item['url'])
                unique.append(item)
        links[key] = unique
    
    print(f"  Navigation: {len(links['navigation'])}")
    print(f"  Quick Links: {len(links['quick_links'])}")
    print(f"  Documents: {len(links['documents'])}")
    print(f"  External: {len(links['external'])}")
    
    return links

def scrape_downloads():
    """Scrape download/document links"""
    print("\n=== Scraping Downloads ===")
    
    downloads = []
    
    html = fetch_page(f"{BASE_URL}/download")
    if not html:
        return downloads
    
    soup = BeautifulSoup(html, 'lxml')
    
    # Find document links
    for link in soup.find_all('a', href=re.compile(r'\.(pdf|doc|docx|xls|xlsx)$', re.I)):
        href = link.get('href', '')
        text = clean_text(link.get_text())
        
        downloads.append({
            'title': text or 'Document',
            'url': urljoin(BASE_URL, href),
            'type': href.split('.')[-1].lower()
        })
    
    print(f"  Total downloads: {len(downloads)}")
    return downloads

def scrape_activities():
    """Scrape activities/gallery"""
    print("\n=== Scraping Activities ===")
    
    activities = []
    
    html = fetch_page(f"{BASE_URL}/act")
    if not html:
        return activities
    
    soup = BeautifulSoup(html, 'lxml')
    
    # Find activity links
    for link in soup.find_all('a', href=re.compile(r'/act/\d+')):
        href = link.get('href', '')
        text = clean_text(link.get_text())
        
        match = re.search(r'/act/(\d+)', href)
        if match:
            act_id = match.group(1)
            
            # Get activity details
            act_url = f"{BASE_URL}/act/{act_id}"
            act_html = fetch_page(act_url)
            
            activity = {
                'id': act_id,
                'title': text,
                'url': act_url,
                'images': [],
                'date': None
            }
            
            if act_html:
                act_soup = BeautifulSoup(act_html, 'lxml')
                
                # Get title
                title_elem = act_soup.find(['h1', 'h2'])
                if title_elem:
                    activity['title'] = clean_text(title_elem.get_text())
                
                # Get images
                for img in act_soup.find_all('img', src=True):
                    src = img.get('src', '')
                    if 'logo' not in src.lower() and 'icon' not in src.lower():
                        activity['images'].append(urljoin(BASE_URL, src))
            
            if activity['title']:
                activities.append(activity)
                print(f"  [OK] {activity['title'][:40]}...")
            
            time.sleep(0.2)
            
            if len(activities) >= 20:  # Limit
                break
    
    print(f"\n  Total activities: {len(activities)}")
    return activities

def analyze_and_map_data(data):
    """Analyze scraped data and map to website pages"""
    print("\n=== Analyzing Data & Creating Page Mapping ===")
    
    mapping = {
        'pages': {
            'home': {
                'description': 'Homepage with hero, news, programs, stats',
                'data_sources': ['site_info', 'news', 'programs', 'stats'],
                'content': {
                    'hero': {
                        'title': data['site_info'].get('name_th', ''),
                        'subtitle': data['site_info'].get('university_th', ''),
                        'description': data['about'].get('vision_th', '')[:200] if data['about'].get('vision_th') else ''
                    },
                    'featured_news': data['news'][:6] if data.get('news') else [],
                    'featured_programs': data['programs']['bachelor'][:6] if data.get('programs') else [],
                    'stats': {
                        'programs': len(data.get('programs', {}).get('bachelor', [])) + len(data.get('programs', {}).get('master', [])) + len(data.get('programs', {}).get('doctorate', [])),
                        'departments': 11,
                        'years': 89
                    }
                }
            },
            'about': {
                'description': 'About page with vision, mission, history',
                'data_sources': ['about', 'personnel'],
                'content': {
                    'vision': data['about'].get('vision_th', ''),
                    'mission': data['about'].get('mission_th', ''),
                    'philosophy': data['about'].get('philosophy_th', ''),
                    'identity': data['about'].get('identity_th', ''),
                    'executives': data['personnel'].get('executives', [])
                }
            },
            'academics': {
                'description': 'Academic programs page',
                'data_sources': ['programs'],
                'content': {
                    'bachelor_programs': data['programs'].get('bachelor', []),
                    'master_programs': data['programs'].get('master', []),
                    'doctorate_programs': data['programs'].get('doctorate', [])
                }
            },
            'news': {
                'description': 'News listing page',
                'data_sources': ['news'],
                'content': {
                    'all_news': data.get('news', [])
                }
            },
            'contact': {
                'description': 'Contact information page',
                'data_sources': ['site_info'],
                'content': {
                    'phone': data['site_info'].get('phone', ''),
                    'email': data['site_info'].get('email', ''),
                    'address': data['site_info'].get('address_th', ''),
                    'facebook': data['site_info'].get('facebook', ''),
                    'map_location': 'Uttaradit Rajabhat University'
                }
            },
            'research': {
                'description': 'Research and journal information',
                'data_sources': ['links'],
                'content': {
                    'journal_links': [l for l in data['links'].get('external', []) if 'journal' in l.get('text', '').lower()]
                }
            },
            'activities': {
                'description': 'Activities and gallery',
                'data_sources': ['activities'],
                'content': {
                    'recent_activities': data.get('activities', [])
                }
            }
        },
        'site_settings': {
            'site_name_th': data['site_info'].get('name_th', ''),
            'site_name_en': data['site_info'].get('name_en', ''),
            'university_name_th': data['site_info'].get('university_th', ''),
            'university_name_en': data['site_info'].get('university_en', ''),
            'phone': data['site_info'].get('phone', ''),
            'email': data['site_info'].get('email', ''),
            'address_th': data['site_info'].get('address_th', ''),
            'address_en': data['site_info'].get('address_en', ''),
            'facebook': data['site_info'].get('facebook', ''),
            'logo': data['site_info'].get('logo', ''),
            'vision_th': data['about'].get('vision_th', ''),
            'mission_th': data['about'].get('mission_th', ''),
            'philosophy_th': data['about'].get('philosophy_th', '')
        }
    }
    
    return mapping

def main():
    print("=" * 60)
    print("Comprehensive sci.uru.ac.th Scraper")
    print(f"Started at: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print("=" * 60)
    
    # Load existing news data
    news_data = []
    try:
        with open(os.path.join(OUTPUT_DIR, 'website_content.json'), 'r', encoding='utf-8') as f:
            existing = json.load(f)
            news_data = existing.get('news', [])
            print(f"\nLoaded {len(news_data)} existing news articles")
    except:
        pass
    
    # Scrape all sections
    site_info = {
        'name_th': 'คณะวิทยาศาสตร์และเทคโนโลยี',
        'name_en': 'Faculty of Science and Technology',
        'university_th': 'มหาวิทยาลัยราชภัฏอุตรดิตถ์',
        'university_en': 'Uttaradit Rajabhat University',
        'phone': '055-411096',
        'fax': '055-411096 ต่อ 1700',
        'email': 'sci@uru.ac.th',
        'address_th': 'คณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยราชภัฏอุตรดิตถ์ 27 ถ.อินใจมี ต.ท่าอิฐ อ.เมือง จ.อุตรดิตถ์ 53000',
        'address_en': 'Faculty of Science and Technology, Uttaradit Rajabhat University, 27 Injaimee Rd., Tha-It, Muang, Uttaradit 53000',
        'facebook': 'https://www.facebook.com/scienceuru',
        'website': 'https://sci.uru.ac.th',
        'logo': 'https://sci.uru.ac.th/images/logo250.png'
    }
    
    # Scrape all data
    about_data = scrape_administration_page()
    personnel_data = scrape_personnel_page()
    programs_data = scrape_curriculum_pages()
    links_data = scrape_all_links()
    downloads_data = scrape_downloads()
    activities_data = scrape_activities()
    
    # Combine all data
    all_data = {
        'metadata': {
            'source': BASE_URL,
            'scraped_at': datetime.now().isoformat(),
            'description': 'Complete data from Faculty of Science and Technology, URU'
        },
        'site_info': site_info,
        'about': about_data,
        'news': news_data,
        'personnel': personnel_data,
        'programs': programs_data,
        'links': links_data,
        'downloads': downloads_data,
        'activities': activities_data
    }
    
    # Analyze and create page mapping
    page_mapping = analyze_and_map_data(all_data)
    
    # Save complete data
    output_file = os.path.join(OUTPUT_DIR, 'complete_site_data.json')
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(all_data, f, ensure_ascii=False, indent=2)
    print(f"\n[OK] Saved complete data to: {output_file}")
    
    # Save page mapping
    mapping_file = os.path.join(OUTPUT_DIR, 'page_mapping.json')
    with open(mapping_file, 'w', encoding='utf-8') as f:
        json.dump(page_mapping, f, ensure_ascii=False, indent=2)
    print(f"[OK] Saved page mapping to: {mapping_file}")
    
    # Summary
    print("\n" + "=" * 60)
    print("SUMMARY")
    print("=" * 60)
    print(f"Site Info: Complete")
    print(f"About/Vision/Mission: {'Found' if about_data.get('vision_th') else 'Not found'}")
    print(f"News: {len(news_data)} articles")
    print(f"Personnel: {len(personnel_data.get('executives', [])) + len(personnel_data.get('staff', []))} people")
    print(f"Programs: {len(programs_data.get('bachelor', []))} bachelor, {len(programs_data.get('master', []))} master, {len(programs_data.get('doctorate', []))} doctorate")
    print(f"Activities: {len(activities_data)}")
    print(f"Links: {sum(len(v) for v in links_data.values())}")
    print(f"Downloads: {len(downloads_data)}")
    
    print("\n" + "=" * 60)
    print("PAGE MAPPING")
    print("=" * 60)
    for page, info in page_mapping['pages'].items():
        print(f"  {page}: {info['description']}")
    
    print(f"\nCompleted at: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")

if __name__ == "__main__":
    main()
