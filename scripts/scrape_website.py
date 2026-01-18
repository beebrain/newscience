"""
sci.uru.ac.th Web Scraper
Scrapes all content from the Faculty of Science and Technology website
and saves to JSON for database analysis.

Usage:
    pip install requests beautifulsoup4 lxml
    python scrape_website.py
"""

import requests
from bs4 import BeautifulSoup
import json
import os
import re
import time
from datetime import datetime
from urllib.parse import urljoin, urlparse

# Configuration
BASE_URL = "https://sci.uru.ac.th"
OUTPUT_DIR = "scraped_data"
HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
}

# Create output directory
os.makedirs(OUTPUT_DIR, exist_ok=True)

def fetch_page(url, retries=3):
    """Fetch a page with retry logic"""
    for i in range(retries):
        try:
            response = requests.get(url, headers=HEADERS, timeout=30, verify=False)
            response.raise_for_status()
            return response.text
        except Exception as e:
            print(f"  Error fetching {url}: {e}")
            if i < retries - 1:
                time.sleep(2)
    return None

def parse_thai_date(date_str):
    """Convert Thai Buddhist year to Gregorian"""
    # Thai months mapping
    thai_months = {
        'มกราคม': 1, 'กุมภาพันธ์': 2, 'มีนาคม': 3, 'เมษายน': 4,
        'พฤษภาคม': 5, 'มิถุนายน': 6, 'กรกฎาคม': 7, 'สิงหาคม': 8,
        'กันยายน': 9, 'ตุลาคม': 10, 'พฤศจิกายน': 11, 'ธันวาคม': 12
    }
    
    # Pattern: day month year (e.g., "12 มกราคม 2569")
    match = re.search(r'(\d+)\s+(\S+)\s+(\d{4})', date_str)
    if match:
        day = int(match.group(1))
        month_name = match.group(2)
        year = int(match.group(3))
        
        # Convert Buddhist year to Gregorian
        if year > 2500:
            year -= 543
        
        month = thai_months.get(month_name, 1)
        return f"{year}-{month:02d}-{day:02d}"
    
    return None

def scrape_news():
    """Scrape all news articles"""
    print("\n=== Scraping News ===")
    news_list = []
    
    # Fetch news listing page
    html = fetch_page(f"{BASE_URL}/news")
    if not html:
        return news_list
    
    soup = BeautifulSoup(html, 'lxml')
    
    # Find all news links
    news_links = soup.find_all('a', href=re.compile(r'/news/\d+'))
    seen_ids = set()
    
    for link in news_links:
        href = link.get('href', '')
        match = re.search(r'/news/(\d+)', href)
        if match:
            news_id = match.group(1)
            if news_id in seen_ids:
                continue
            seen_ids.add(news_id)
            
            title = link.get_text(strip=True)
            
            # Fetch individual article
            article_url = f"{BASE_URL}/news/{news_id}"
            article_html = fetch_page(article_url)
            
            content = ""
            images = []
            date = None
            
            if article_html:
                article_soup = BeautifulSoup(article_html, 'lxml')
                
                # Find content
                content_div = article_soup.find('div', class_=re.compile(r'content|article|news'))
                if content_div:
                    content = content_div.get_text(strip=True)
                
                # Find images
                for img in article_soup.find_all('img', src=True):
                    src = img['src']
                    if 'logo' not in src.lower() and 'icon' not in src.lower():
                        if not src.startswith('http'):
                            src = urljoin(BASE_URL, src)
                        images.append(src)
                
                # Find date
                date_elem = article_soup.find(string=re.compile(r'\d+\s+\S+\s+25\d{2}'))
                if date_elem:
                    date = parse_thai_date(str(date_elem))
            
            news_item = {
                'id': int(news_id),
                'title': title,
                'content': content[:1000] if content else "",  # Limit content length
                'images': images[:5],  # Limit to 5 images
                'date': date,
                'url': article_url
            }
            news_list.append(news_item)
            print(f"  Scraped: {title[:50]}...")
            
            time.sleep(0.5)  # Be nice to the server
    
    print(f"  Total news articles: {len(news_list)}")
    return news_list

def scrape_personnel():
    """Scrape personnel/staff information"""
    print("\n=== Scraping Personnel ===")
    personnel_list = []
    
    html = fetch_page(f"{BASE_URL}/personnel")
    if not html:
        return personnel_list
    
    soup = BeautifulSoup(html, 'lxml')
    
    # Find personnel cards/items
    person_items = soup.find_all(['div', 'article'], class_=re.compile(r'personnel|staff|person|card'))
    
    for item in person_items:
        name = ""
        position = ""
        image = ""
        department = ""
        
        # Try to extract name
        name_elem = item.find(['h2', 'h3', 'h4', 'span'], class_=re.compile(r'name|title'))
        if name_elem:
            name = name_elem.get_text(strip=True)
        
        # Try to extract position
        pos_elem = item.find(['p', 'span'], class_=re.compile(r'position|title|role'))
        if pos_elem:
            position = pos_elem.get_text(strip=True)
        
        # Try to extract image
        img = item.find('img', src=True)
        if img:
            image = urljoin(BASE_URL, img['src'])
        
        if name:
            personnel_list.append({
                'name': name,
                'position': position,
                'image': image,
                'department': department
            })
    
    print(f"  Total personnel: {len(personnel_list)}")
    return personnel_list

def scrape_curriculum():
    """Scrape curriculum/programs information"""
    print("\n=== Scraping Curriculum ===")
    curriculum_list = []
    
    html = fetch_page(f"{BASE_URL}/curriculum")
    if not html:
        return curriculum_list
    
    soup = BeautifulSoup(html, 'lxml')
    
    # Find curriculum links
    curr_links = soup.find_all('a', href=re.compile(r'/doctopic/\d+|/curriculum'))
    
    for link in curr_links:
        title = link.get_text(strip=True)
        href = link.get('href', '')
        
        if title and len(title) > 3:
            curriculum_list.append({
                'title': title,
                'url': urljoin(BASE_URL, href)
            })
    
    print(f"  Total curriculum items: {len(curriculum_list)}")
    return curriculum_list

def scrape_activities():
    """Scrape activities/gallery"""
    print("\n=== Scraping Activities ===")
    activities_list = []
    
    html = fetch_page(f"{BASE_URL}/act")
    if not html:
        return activities_list
    
    soup = BeautifulSoup(html, 'lxml')
    
    # Find activity items
    activity_items = soup.find_all(['div', 'article'], class_=re.compile(r'activity|event|gallery'))
    
    for item in activity_items:
        title = ""
        images = []
        date = None
        
        title_elem = item.find(['h2', 'h3', 'h4', 'a'])
        if title_elem:
            title = title_elem.get_text(strip=True)
        
        for img in item.find_all('img', src=True):
            images.append(urljoin(BASE_URL, img['src']))
        
        if title:
            activities_list.append({
                'title': title,
                'images': images[:5],
                'date': date
            })
    
    print(f"  Total activities: {len(activities_list)}")
    return activities_list

def scrape_links():
    """Scrape all important links from the website"""
    print("\n=== Scraping Links ===")
    links_data = {
        'internal': [],
        'external': []
    }
    
    html = fetch_page(BASE_URL)
    if not html:
        return links_data
    
    soup = BeautifulSoup(html, 'lxml')
    
    for link in soup.find_all('a', href=True):
        href = link['href']
        text = link.get_text(strip=True)
        
        if not href or href.startswith('#') or href.startswith('javascript'):
            continue
        
        full_url = urljoin(BASE_URL, href)
        parsed = urlparse(full_url)
        
        link_item = {
            'text': text,
            'url': full_url
        }
        
        if 'sci.uru.ac.th' in parsed.netloc:
            links_data['internal'].append(link_item)
        else:
            links_data['external'].append(link_item)
    
    print(f"  Internal links: {len(links_data['internal'])}")
    print(f"  External links: {len(links_data['external'])}")
    return links_data

def analyze_database_structure(data):
    """Analyze scraped data and suggest database structure"""
    print("\n=== Analyzing Database Structure ===")
    
    db_structure = {
        'tables': [],
        'relationships': []
    }
    
    # News table
    if data.get('news'):
        db_structure['tables'].append({
            'name': 'news',
            'columns': [
                {'name': 'id', 'type': 'INT', 'primary': True, 'auto_increment': True},
                {'name': 'title', 'type': 'VARCHAR(500)', 'nullable': False},
                {'name': 'slug', 'type': 'VARCHAR(500)', 'unique': True},
                {'name': 'content', 'type': 'TEXT', 'nullable': True},
                {'name': 'excerpt', 'type': 'VARCHAR(1000)', 'nullable': True},
                {'name': 'featured_image', 'type': 'VARCHAR(255)', 'nullable': True},
                {'name': 'status', 'type': "ENUM('draft','published')", 'default': 'draft'},
                {'name': 'author_id', 'type': 'INT', 'nullable': True, 'foreign_key': 'users.id'},
                {'name': 'published_at', 'type': 'DATETIME', 'nullable': True},
                {'name': 'created_at', 'type': 'DATETIME', 'default': 'CURRENT_TIMESTAMP'},
                {'name': 'updated_at', 'type': 'DATETIME', 'on_update': 'CURRENT_TIMESTAMP'}
            ],
            'sample_count': len(data['news'])
        })
        
        # News images table (for multiple images)
        db_structure['tables'].append({
            'name': 'news_images',
            'columns': [
                {'name': 'id', 'type': 'INT', 'primary': True, 'auto_increment': True},
                {'name': 'news_id', 'type': 'INT', 'foreign_key': 'news.id'},
                {'name': 'image_path', 'type': 'VARCHAR(255)', 'nullable': False},
                {'name': 'caption', 'type': 'VARCHAR(500)', 'nullable': True},
                {'name': 'sort_order', 'type': 'INT', 'default': 0},
                {'name': 'created_at', 'type': 'DATETIME', 'default': 'CURRENT_TIMESTAMP'}
            ]
        })
        
        db_structure['relationships'].append({
            'from': 'news_images.news_id',
            'to': 'news.id',
            'type': 'many-to-one',
            'on_delete': 'CASCADE'
        })
    
    # Personnel table
    if data.get('personnel'):
        db_structure['tables'].append({
            'name': 'personnel',
            'columns': [
                {'name': 'id', 'type': 'INT', 'primary': True, 'auto_increment': True},
                {'name': 'title', 'type': 'VARCHAR(50)', 'nullable': True},
                {'name': 'first_name', 'type': 'VARCHAR(100)', 'nullable': False},
                {'name': 'last_name', 'type': 'VARCHAR(100)', 'nullable': False},
                {'name': 'position', 'type': 'VARCHAR(255)', 'nullable': True},
                {'name': 'department_id', 'type': 'INT', 'foreign_key': 'departments.id'},
                {'name': 'email', 'type': 'VARCHAR(255)', 'nullable': True},
                {'name': 'phone', 'type': 'VARCHAR(50)', 'nullable': True},
                {'name': 'image', 'type': 'VARCHAR(255)', 'nullable': True},
                {'name': 'bio', 'type': 'TEXT', 'nullable': True},
                {'name': 'sort_order', 'type': 'INT', 'default': 0}
            ],
            'sample_count': len(data['personnel'])
        })
    
    # Departments table
    db_structure['tables'].append({
        'name': 'departments',
        'columns': [
            {'name': 'id', 'type': 'INT', 'primary': True, 'auto_increment': True},
            {'name': 'name_th', 'type': 'VARCHAR(255)', 'nullable': False},
            {'name': 'name_en', 'type': 'VARCHAR(255)', 'nullable': True},
            {'name': 'description', 'type': 'TEXT', 'nullable': True},
            {'name': 'image', 'type': 'VARCHAR(255)', 'nullable': True}
        ]
    })
    
    # Curriculum/Programs table
    if data.get('curriculum'):
        db_structure['tables'].append({
            'name': 'programs',
            'columns': [
                {'name': 'id', 'type': 'INT', 'primary': True, 'auto_increment': True},
                {'name': 'name_th', 'type': 'VARCHAR(255)', 'nullable': False},
                {'name': 'name_en', 'type': 'VARCHAR(255)', 'nullable': True},
                {'name': 'degree_level', 'type': "ENUM('bachelor','master','doctorate')"},
                {'name': 'department_id', 'type': 'INT', 'foreign_key': 'departments.id'},
                {'name': 'description', 'type': 'TEXT', 'nullable': True},
                {'name': 'url', 'type': 'VARCHAR(500)', 'nullable': True}
            ],
            'sample_count': len(data['curriculum'])
        })
    
    # Activities table
    if data.get('activities'):
        db_structure['tables'].append({
            'name': 'activities',
            'columns': [
                {'name': 'id', 'type': 'INT', 'primary': True, 'auto_increment': True},
                {'name': 'title', 'type': 'VARCHAR(500)', 'nullable': False},
                {'name': 'description', 'type': 'TEXT', 'nullable': True},
                {'name': 'activity_date', 'type': 'DATE', 'nullable': True},
                {'name': 'location', 'type': 'VARCHAR(255)', 'nullable': True}
            ],
            'sample_count': len(data['activities'])
        })
        
        # Activity images
        db_structure['tables'].append({
            'name': 'activity_images',
            'columns': [
                {'name': 'id', 'type': 'INT', 'primary': True, 'auto_increment': True},
                {'name': 'activity_id', 'type': 'INT', 'foreign_key': 'activities.id'},
                {'name': 'image_path', 'type': 'VARCHAR(255)', 'nullable': False},
                {'name': 'caption', 'type': 'VARCHAR(500)', 'nullable': True},
                {'name': 'sort_order', 'type': 'INT', 'default': 0}
            ]
        })
    
    return db_structure

def main():
    print("=" * 60)
    print("sci.uru.ac.th Web Scraper")
    print(f"Started at: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print("=" * 60)
    
    # Disable SSL warnings
    import urllib3
    urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)
    
    # Scrape all sections
    data = {
        'metadata': {
            'source': BASE_URL,
            'scraped_at': datetime.now().isoformat(),
            'description': 'Faculty of Science and Technology, Uttaradit Rajabhat University'
        },
        'news': scrape_news(),
        'personnel': scrape_personnel(),
        'curriculum': scrape_curriculum(),
        'activities': scrape_activities(),
        'links': scrape_links()
    }
    
    # Save raw data to JSON
    output_file = os.path.join(OUTPUT_DIR, 'website_content.json')
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(data, f, ensure_ascii=False, indent=2)
    print(f"\n✓ Saved website content to: {output_file}")
    
    # Analyze and save database structure
    db_structure = analyze_database_structure(data)
    db_file = os.path.join(OUTPUT_DIR, 'database_structure.json')
    with open(db_file, 'w', encoding='utf-8') as f:
        json.dump(db_structure, f, ensure_ascii=False, indent=2)
    print(f"✓ Saved database structure to: {db_file}")
    
    # Generate SQL schema
    sql_file = os.path.join(OUTPUT_DIR, 'schema.sql')
    with open(sql_file, 'w', encoding='utf-8') as f:
        f.write("-- Generated Database Schema from sci.uru.ac.th\n")
        f.write(f"-- Generated at: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n\n")
        
        for table in db_structure['tables']:
            f.write(f"-- Table: {table['name']}\n")
            if table.get('sample_count'):
                f.write(f"-- Sample data count: {table['sample_count']}\n")
            f.write(f"CREATE TABLE IF NOT EXISTS `{table['name']}` (\n")
            
            columns = []
            for col in table['columns']:
                col_def = f"  `{col['name']}` {col['type']}"
                if col.get('primary'):
                    col_def += " PRIMARY KEY"
                if col.get('auto_increment'):
                    col_def += " AUTO_INCREMENT"
                if col.get('unique'):
                    col_def += " UNIQUE"
                if col.get('nullable') == False:
                    col_def += " NOT NULL"
                if col.get('default'):
                    col_def += f" DEFAULT {col['default']}"
                columns.append(col_def)
            
            f.write(",\n".join(columns))
            f.write("\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n")
    
    print(f"✓ Saved SQL schema to: {sql_file}")
    
    # Summary
    print("\n" + "=" * 60)
    print("SUMMARY")
    print("=" * 60)
    print(f"News articles: {len(data['news'])}")
    print(f"Personnel: {len(data['personnel'])}")
    print(f"Curriculum: {len(data['curriculum'])}")
    print(f"Activities: {len(data['activities'])}")
    print(f"Database tables: {len(db_structure['tables'])}")
    print(f"\nCompleted at: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")

if __name__ == "__main__":
    main()
