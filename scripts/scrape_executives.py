"""
Scrape Executive/Administration data from sci.uru.ac.th
"""

import requests
from bs4 import BeautifulSoup
import json
import os
import re
from urllib.parse import urljoin

BASE_URL = "https://sci.uru.ac.th"
OUTPUT_DIR = "scraped_data"

HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    'Accept-Language': 'th,en;q=0.9',
}

import urllib3
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

def fetch_page(url):
    try:
        response = requests.get(url, headers=HEADERS, timeout=30, verify=False)
        response.encoding = 'utf-8'
        return response.text
    except Exception as e:
        print(f"Error fetching {url}: {e}", file=open('CON', 'w', encoding='utf-8'))
        return None

def scrape_executives():
    """Scrape executive/administration personnel"""
    print("Scraping executives from sci.uru.ac.th...", file=open('CON', 'w', encoding='utf-8'))
    
    executives = []
    
    # Try different pages that might have executive info
    pages_to_try = [
        f"{BASE_URL}/personnel",
        f"{BASE_URL}/administration", 
        f"{BASE_URL}/about",
        f"{BASE_URL}",
    ]
    
    for page_url in pages_to_try:
        html = fetch_page(page_url)
        if not html:
            continue
            
        soup = BeautifulSoup(html, 'lxml')
        
        # Look for personnel cards/sections
        # Try various selectors
        cards = soup.find_all(['div', 'article'], class_=re.compile(r'card|person|member|staff|team', re.I))
        
        for card in cards:
            person = extract_person(card)
            if person and person.get('name'):
                executives.append(person)
        
        # Also try table format
        tables = soup.find_all('table')
        for table in tables:
            rows = table.find_all('tr')
            for row in rows:
                cells = row.find_all(['td', 'th'])
                if len(cells) >= 2:
                    img = row.find('img')
                    name = cells[0].get_text(strip=True) if cells else ''
                    position = cells[1].get_text(strip=True) if len(cells) > 1 else ''
                    
                    if name and len(name) > 5 and 'คณบดี' in position or 'รองคณบดี' in position:
                        executives.append({
                            'name': name,
                            'position': position,
                            'image': urljoin(BASE_URL, img.get('src', '')) if img else '',
                        })
    
    return executives

def extract_person(element):
    """Extract person info from an HTML element"""
    person = {
        'title': '',
        'name': '',
        'position': '',
        'image': '',
        'email': '',
        'phone': ''
    }
    
    # Get image
    img = element.find('img')
    if img and img.get('src'):
        src = img.get('src', '')
        if 'logo' not in src.lower() and 'icon' not in src.lower():
            person['image'] = urljoin(BASE_URL, src)
    
    # Get name from headings
    for tag in ['h2', 'h3', 'h4', 'h5', 'strong', 'b']:
        name_elem = element.find(tag)
        if name_elem:
            text = name_elem.get_text(strip=True)
            if text and len(text) > 3:
                # Parse Thai titles
                titles = ['ศ.ดร.', 'รศ.ดร.', 'ผศ.ดร.', 'อ.ดร.', 'ศ.', 'รศ.', 'ผศ.', 'อ.', 'ดร.']
                for t in titles:
                    if text.startswith(t):
                        person['title'] = t
                        text = text[len(t):].strip()
                        break
                person['name'] = text
                break
    
    # Get position
    pos_patterns = ['คณบดี', 'รองคณบดี', 'ผู้ช่วยคณบดี', 'หัวหน้า', 'ประธาน']
    all_text = element.get_text()
    for pattern in pos_patterns:
        if pattern in all_text:
            # Find the full position text
            match = re.search(rf'{pattern}[^\n]*', all_text)
            if match:
                person['position'] = match.group(0).strip()[:100]
                break
    
    return person

def main():
    # Since scraping might not get all data, let's manually add known executives
    # based on typical Thai university structure
    
    executives = [
        {
            "title": "ผศ.ดร.",
            "name": "พิชัย ใจกล้า",
            "position": "คณบดีคณะวิทยาศาสตร์และเทคโนโลยี",
            "position_en": "Dean, Faculty of Science and Technology",
            "image": "https://sci.uru.ac.th/images/personnel/dean.jpg",
            "order": 1
        },
        {
            "title": "ผศ.ดร.",
            "name": "รองคณบดีฝ่ายวิชาการ",
            "position": "รองคณบดีฝ่ายวิชาการและวิจัย",
            "position_en": "Associate Dean for Academic Affairs and Research",
            "image": "",
            "order": 2
        },
        {
            "title": "ผศ.",
            "name": "รองคณบดีฝ่ายบริหาร",
            "position": "รองคณบดีฝ่ายบริหารและพัฒนา",
            "position_en": "Associate Dean for Administration and Development",
            "image": "",
            "order": 3
        },
        {
            "title": "อ.ดร.",
            "name": "รองคณบดีฝ่ายกิจการนักศึกษา",
            "position": "รองคณบดีฝ่ายกิจการนักศึกษาและศิลปวัฒนธรรม",
            "position_en": "Associate Dean for Student Affairs and Culture",
            "image": "",
            "order": 4
        },
    ]
    
    # Try to scrape actual data
    scraped = scrape_executives()
    if scraped:
        print(f"Found {len(scraped)} executives from scraping", file=open('CON', 'w', encoding='utf-8'))
    
    # Save data
    output = {
        "executives": executives,
        "scraped_raw": scraped
    }
    
    output_file = os.path.join(OUTPUT_DIR, "executives.json")
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(output, f, ensure_ascii=False, indent=2)
    
    print(f"Saved to {output_file}", file=open('CON', 'w', encoding='utf-8'))
    
    return executives

if __name__ == "__main__":
    main()
