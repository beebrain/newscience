"""
sci.uru.ac.th News and Images Downloader
Downloads news content and images from the Faculty of Science and Technology website.

Usage:
    pip install requests beautifulsoup4 lxml
    python download_news_images.py
"""

import requests
from bs4 import BeautifulSoup
import json
import os
import re
import time
from datetime import datetime
from urllib.parse import urljoin, urlparse
import hashlib

# Configuration
BASE_URL = "https://sci.uru.ac.th"
OUTPUT_DIR = "scraped_data"
IMAGES_DIR = os.path.join(OUTPUT_DIR, "news_images")
HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
}

# Create output directories
os.makedirs(OUTPUT_DIR, exist_ok=True)
os.makedirs(IMAGES_DIR, exist_ok=True)

# Disable SSL warnings
import urllib3
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)


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


def download_image(url, save_dir, prefix=""):
    """Download an image and return the local path"""
    try:
        # Create unique filename based on URL
        url_hash = hashlib.md5(url.encode()).hexdigest()[:8]
        ext = os.path.splitext(urlparse(url).path)[1]
        if not ext or ext.lower() not in ['.jpg', '.jpeg', '.png', '.gif', '.webp']:
            ext = '.jpg'
        
        filename = f"{prefix}_{url_hash}{ext}" if prefix else f"{url_hash}{ext}"
        filepath = os.path.join(save_dir, filename)
        
        # Skip if already downloaded
        if os.path.exists(filepath):
            return filename
        
        response = requests.get(url, headers=HEADERS, timeout=30, verify=False, stream=True)
        response.raise_for_status()
        
        with open(filepath, 'wb') as f:
            for chunk in response.iter_content(chunk_size=8192):
                f.write(chunk)
        
        return filename
    except Exception as e:
        print(f"    ✗ Error downloading image {url}: {e}")
        return None


def parse_thai_date(date_str):
    """Convert Thai Buddhist year to Gregorian"""
    thai_months = {
        'มกราคม': 1, 'กุมภาพันธ์': 2, 'มีนาคม': 3, 'เมษายน': 4,
        'พฤษภาคม': 5, 'มิถุนายน': 6, 'กรกฎาคม': 7, 'สิงหาคม': 8,
        'กันยายน': 9, 'ตุลาคม': 10, 'พฤศจิกายน': 11, 'ธันวาคม': 12
    }
    
    match = re.search(r'(\d+)\s+(\S+)\s+(\d{4})', date_str)
    if match:
        day = int(match.group(1))
        month_name = match.group(2)
        year = int(match.group(3))
        
        if year > 2500:
            year -= 543
        
        month = thai_months.get(month_name, 1)
        return f"{year}-{month:02d}-{day:02d}"
    
    return None


def get_all_news_pages():
    """Get all news page URLs by navigating through pagination"""
    print("\n=== Discovering News Pages ===")
    all_ids = set()
    
    # First, get news from main page
    html = fetch_page(f"{BASE_URL}/news")
    if html:
        soup = BeautifulSoup(html, 'lxml')
        
        # Find all news links
        news_links = soup.find_all('a', href=re.compile(r'/news/\d+'))
        for link in news_links:
            match = re.search(r'/news/(\d+)', link.get('href', ''))
            if match:
                all_ids.add(int(match.group(1)))
        
        print(f"  Found {len(all_ids)} news IDs from main page")
        
        # Try to find pagination links
        page_links = soup.find_all('a', href=re.compile(r'/news\?page=\d+'))
        pages_to_fetch = set()
        
        for link in page_links:
            match = re.search(r'page=(\d+)', link.get('href', ''))
            if match:
                pages_to_fetch.add(int(match.group(1)))
        
        # Also check numbered buttons
        for page_num in range(2, 20):  # Try up to 20 pages
            page_html = fetch_page(f"{BASE_URL}/news?page={page_num}")
            if page_html:
                page_soup = BeautifulSoup(page_html, 'lxml')
                found_new = False
                
                for link in page_soup.find_all('a', href=re.compile(r'/news/\d+')):
                    match = re.search(r'/news/(\d+)', link.get('href', ''))
                    if match:
                        news_id = int(match.group(1))
                        if news_id not in all_ids:
                            found_new = True
                            all_ids.add(news_id)
                
                if not found_new:
                    print(f"  Stopped at page {page_num} (no new news found)")
                    break
                else:
                    print(f"  Page {page_num}: Total {len(all_ids)} news IDs")
                
                time.sleep(0.3)
    
    return sorted(all_ids, reverse=True)  # Sort by newest first


def scrape_news_with_images():
    """Scrape all news articles and download their images"""
    print("\n=== Scraping News with Images ===")
    news_list = []
    downloaded_images = 0
    
    # Get all news IDs
    news_ids = get_all_news_pages()
    print(f"\nTotal news to scrape: {len(news_ids)}")
    
    for idx, news_id in enumerate(news_ids, 1):
        article_url = f"{BASE_URL}/news/{news_id}"
        print(f"\n[{idx}/{len(news_ids)}] Scraping news ID: {news_id}")
        
        article_html = fetch_page(article_url)
        if not article_html:
            continue
        
        article_soup = BeautifulSoup(article_html, 'lxml')
        
        # Extract title
        title = ""
        title_elem = article_soup.find('h1') or article_soup.find('h2')
        if title_elem:
            title = title_elem.get_text(strip=True)
        
        # Extract content
        content = ""
        content_selectors = [
            ('div', {'class': re.compile(r'content|article|news-content|post-content')}),
            ('article', {}),
            ('div', {'class': re.compile(r'entry|body|text')}),
        ]
        
        for tag, attrs in content_selectors:
            content_div = article_soup.find(tag, attrs)
            if content_div:
                # Get all text paragraphs
                paragraphs = content_div.find_all('p')
                if paragraphs:
                    content = '\n\n'.join(p.get_text(strip=True) for p in paragraphs if p.get_text(strip=True))
                else:
                    content = content_div.get_text(strip=True)
                break
        
        # Extract date
        date = None
        date_patterns = [
            r'\d+\s+\S+\s+25\d{2}',  # Thai Buddhist date
            r'\d{1,2}/\d{1,2}/25\d{2}',  # DD/MM/YYYY Buddhist
        ]
        
        for pattern in date_patterns:
            date_elem = article_soup.find(string=re.compile(pattern))
            if date_elem:
                date = parse_thai_date(str(date_elem))
                if date:
                    break
        
        # Extract and download images
        images_local = []
        images_original = []
        
        # Create directory for this news article's images
        news_images_dir = os.path.join(IMAGES_DIR, str(news_id))
        os.makedirs(news_images_dir, exist_ok=True)
        
        for img in article_soup.find_all('img', src=True):
            src = img['src']
            
            # Skip icons, logos, etc.
            if any(skip in src.lower() for skip in ['logo', 'icon', 'avatar', 'spinner', 'loading']):
                continue
            
            # Full URL
            if not src.startswith('http'):
                src = urljoin(BASE_URL, src)
            
            # Download image
            local_filename = download_image(src, news_images_dir, prefix=f"img")
            if local_filename:
                images_local.append(f"news_images/{news_id}/{local_filename}")
                images_original.append(src)
                downloaded_images += 1
                print(f"    ✓ Downloaded: {local_filename}")
        
        news_item = {
            'id': news_id,
            'title': title,
            'content': content,
            'date': date,
            'url': article_url,
            'images_local': images_local,
            'images_original': images_original,
            'image_count': len(images_local)
        }
        news_list.append(news_item)
        
        print(f"  Title: {title[:60]}..." if len(title) > 60 else f"  Title: {title}")
        print(f"  Date: {date}")
        print(f"  Images: {len(images_local)}")
        
        time.sleep(0.3)  # Be nice to the server
    
    print(f"\n\nTotal news articles scraped: {len(news_list)}")
    print(f"Total images downloaded: {downloaded_images}")
    
    return news_list


def scrape_activities_with_images():
    """Scrape activities/gallery and download images"""
    print("\n=== Scraping Activities with Images ===")
    activities_list = []
    downloaded_images = 0
    
    # Get activities from main page
    html = fetch_page(f"{BASE_URL}/act")
    if not html:
        return activities_list
    
    soup = BeautifulSoup(html, 'lxml')
    
    # Find activity links
    activity_links = soup.find_all('a', href=re.compile(r'/act/\d+'))
    activity_ids = set()
    
    for link in activity_links:
        match = re.search(r'/act/(\d+)', link.get('href', ''))
        if match:
            activity_ids.add(int(match.group(1)))
    
    # Also check pagination
    for page_num in range(2, 10):
        page_html = fetch_page(f"{BASE_URL}/act?page={page_num}")
        if page_html:
            page_soup = BeautifulSoup(page_html, 'lxml')
            found_new = False
            
            for link in page_soup.find_all('a', href=re.compile(r'/act/\d+')):
                match = re.search(r'/act/(\d+)', link.get('href', ''))
                if match:
                    act_id = int(match.group(1))
                    if act_id not in activity_ids:
                        found_new = True
                        activity_ids.add(act_id)
            
            if not found_new:
                break
            
            time.sleep(0.3)
    
    print(f"Found {len(activity_ids)} activities")
    activity_ids = sorted(activity_ids, reverse=True)
    
    # Create activities images directory
    activities_images_dir = os.path.join(OUTPUT_DIR, "activity_images")
    os.makedirs(activities_images_dir, exist_ok=True)
    
    for idx, act_id in enumerate(activity_ids, 1):
        act_url = f"{BASE_URL}/act/{act_id}"
        print(f"\n[{idx}/{len(activity_ids)}] Scraping activity ID: {act_id}")
        
        act_html = fetch_page(act_url)
        if not act_html:
            continue
        
        act_soup = BeautifulSoup(act_html, 'lxml')
        
        # Extract title
        title = ""
        title_elem = act_soup.find('h1') or act_soup.find('h2')
        if title_elem:
            title = title_elem.get_text(strip=True)
        
        # Extract date
        date = None
        date_elem = act_soup.find(string=re.compile(r'\d+\s+\S+\s+25\d{2}'))
        if date_elem:
            date = parse_thai_date(str(date_elem))
        
        # Extract and download images
        images_local = []
        images_original = []
        
        act_images_dir = os.path.join(activities_images_dir, str(act_id))
        os.makedirs(act_images_dir, exist_ok=True)
        
        for img in act_soup.find_all('img', src=True):
            src = img['src']
            
            if any(skip in src.lower() for skip in ['logo', 'icon', 'avatar', 'spinner', 'loading']):
                continue
            
            if not src.startswith('http'):
                src = urljoin(BASE_URL, src)
            
            local_filename = download_image(src, act_images_dir, prefix=f"img")
            if local_filename:
                images_local.append(f"activity_images/{act_id}/{local_filename}")
                images_original.append(src)
                downloaded_images += 1
        
        activity_item = {
            'id': act_id,
            'title': title,
            'date': date,
            'url': act_url,
            'images_local': images_local,
            'images_original': images_original,
            'image_count': len(images_local)
        }
        activities_list.append(activity_item)
        
        print(f"  Title: {title[:60]}..." if len(title) > 60 else f"  Title: {title}")
        print(f"  Images: {len(images_local)}")
        
        time.sleep(0.3)
    
    print(f"\nTotal activities scraped: {len(activities_list)}")
    print(f"Total activity images downloaded: {downloaded_images}")
    
    return activities_list


def main():
    print("=" * 60)
    print("sci.uru.ac.th News & Images Downloader")
    print(f"Started at: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print("=" * 60)
    
    # Scrape and download
    news_data = scrape_news_with_images()
    activities_data = scrape_activities_with_images()
    
    # Combine all data
    data = {
        'metadata': {
            'source': BASE_URL,
            'scraped_at': datetime.now().isoformat(),
            'description': 'News and activities from Faculty of Science and Technology, Uttaradit Rajabhat University'
        },
        'statistics': {
            'total_news': len(news_data),
            'total_activities': len(activities_data),
            'total_news_images': sum(n['image_count'] for n in news_data),
            'total_activity_images': sum(a['image_count'] for a in activities_data)
        },
        'news': news_data,
        'activities': activities_data
    }
    
    # Save to JSON
    output_file = os.path.join(OUTPUT_DIR, 'news_and_images.json')
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(data, f, ensure_ascii=False, indent=2)
    
    print("\n" + "=" * 60)
    print("DOWNLOAD COMPLETE")
    print("=" * 60)
    print(f"News articles: {data['statistics']['total_news']}")
    print(f"News images: {data['statistics']['total_news_images']}")
    print(f"Activities: {data['statistics']['total_activities']}")  
    print(f"Activity images: {data['statistics']['total_activity_images']}")
    print(f"\nData saved to: {output_file}")
    print(f"Images saved to: {IMAGES_DIR}")
    print(f"Completed at: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")


if __name__ == "__main__":
    main()
