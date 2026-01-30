"""
Download images for academic programs from Unsplash
ดาวน์โหลดภาพประกอบสำหรับหลักสูตรจาก Unsplash
"""

import os
import sys
import io
import requests
from pathlib import Path
import json

# Fix Windows console encoding
if sys.platform == 'win32':
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')
    sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8')

# Unsplash API (using demo access key - replace with your own for production)
UNSPLASH_ACCESS_KEY = "YOUR_ACCESS_KEY"  # Get from https://unsplash.com/developers
UNSPLASH_API_URL = "https://api.unsplash.com/search/photos"

# Program image mappings
PROGRAM_IMAGE_KEYWORDS = {
    'คณิตศาสตร์': ['mathematics', 'math', 'formula', 'equation', 'calculus'],
    'ชีววิทยา': ['biology', 'laboratory', 'microscope', 'cells', 'nature'],
    'เคมี': ['chemistry', 'laboratory', 'test tubes', 'chemical reaction', 'molecules'],
    'เทคโนโลยีสารสนเทศ': ['information technology', 'computer network', 'digital', 'technology'],
    'วิทยาการคอมพิวเตอร์': ['computer science', 'coding', 'programming', 'software development'],
    'วิทยาการข้อมูล': ['data science', 'data analysis', 'big data', 'analytics'],
    'วิทยาศาสตร์การกีฬา': ['sports science', 'athlete', 'fitness', 'exercise', 'sports'],
    'วิทยาศาสตร์สิ่งแวดล้อม': ['environmental science', 'nature', 'ecology', 'sustainability'],
    'สาธารณสุขศาสตร์': ['public health', 'healthcare', 'medical', 'hospital'],
    'อาหารและโภชนาการ': ['food nutrition', 'healthy food', 'diet', 'nutrition'],
    'วิทยาศาสตร์ประยุกต์': ['applied science', 'research', 'laboratory', 'innovation'],
    'วิศวกรรม': ['engineering', 'construction', 'technology', 'innovation'],
}

# Fallback images if Unsplash fails (using Unsplash Source API - no key required)
FALLBACK_IMAGES = {
    'คณิตศาสตร์': 'https://source.unsplash.com/800x600/?mathematics,formula,equation',
    'ชีววิทยา': 'https://source.unsplash.com/800x600/?biology,laboratory,microscope',
    'เคมี': 'https://source.unsplash.com/800x600/?chemistry,laboratory,test-tubes',
    'เทคโนโลยีสารสนเทศ': 'https://source.unsplash.com/800x600/?information-technology,computer-network,digital',
    'วิทยาการคอมพิวเตอร์': 'https://source.unsplash.com/800x600/?computer-science,coding,programming',
    'วิทยาการข้อมูล': 'https://source.unsplash.com/800x600/?data-science,analytics,big-data',
    'วิทยาศาสตร์การกีฬา': 'https://source.unsplash.com/800x600/?sports-science,athlete,fitness',
    'วิทยาศาสตร์สิ่งแวดล้อม': 'https://source.unsplash.com/800x600/?environmental-science,nature,ecology',
    'สาธารณสุขศาสตร์': 'https://source.unsplash.com/800x600/?public-health,healthcare,medical',
    'อาหารและโภชนาการ': 'https://source.unsplash.com/800x600/?food-nutrition,healthy-food,diet',
    'สิ่งแวดล้อม': 'https://source.unsplash.com/800x600/?environment,nature,sustainability',
}

# Default fallback
DEFAULT_FALLBACK = 'https://source.unsplash.com/800x600/?education,university,academic'

SCRIPT_DIR = Path(__file__).parent
IMAGES_DIR = SCRIPT_DIR / 'scraped_data' / 'images' / 'programs'
IMAGES_DIR.mkdir(parents=True, exist_ok=True)

def download_image(url, filename):
    """Download image from URL"""
    try:
        response = requests.get(url, timeout=10, stream=True)
        response.raise_for_status()
        
        filepath = IMAGES_DIR / filename
        with open(filepath, 'wb') as f:
            for chunk in response.iter_content(chunk_size=8192):
                f.write(chunk)
        
        return str(filepath.relative_to(SCRIPT_DIR.parent)).replace('\\', '/')
    except Exception as e:
        print(f"  ❌ Error downloading {url}: {e}")
        return None

def get_unsplash_image(keywords):
    """Get image from Unsplash API"""
    if not UNSPLASH_ACCESS_KEY or UNSPLASH_ACCESS_KEY == "YOUR_ACCESS_KEY":
        # Use fallback direct Unsplash URLs
        return None
    
    try:
        query = ' '.join(keywords[:2])  # Use first 2 keywords
        params = {
            'query': query,
            'per_page': 1,
            'orientation': 'landscape'
        }
        headers = {
            'Authorization': f'Client-ID {UNSPLASH_ACCESS_KEY}'
        }
        
        response = requests.get(UNSPLASH_API_URL, params=params, headers=headers, timeout=10)
        response.raise_for_status()
        
        data = response.json()
        if data.get('results') and len(data['results']) > 0:
            return data['results'][0]['urls']['regular']
    except Exception as e:
        print(f"  ⚠️  Unsplash API error: {e}")
    
    return None

def get_program_keywords(program_name):
    """Get keywords for program"""
    program_name_lower = program_name.lower()
    
    for key, keywords in PROGRAM_IMAGE_KEYWORDS.items():
        if key in program_name:
            return keywords
    
    # Default keywords
    return ['education', 'university', 'academic']

def download_program_images():
    """Download images for all programs"""
    print("=" * 60)
    print("Download Program Images")
    print("=" * 60)
    
    # Load programs from JSON
    data_file = SCRIPT_DIR / 'scraped_data' / 'all_content.json'
    
    if not data_file.exists():
        print(f"❌ Data file not found: {data_file}")
        return
    
    with open(data_file, 'r', encoding='utf-8') as f:
        data = json.load(f)
    
    programs = data.get('programs', [])
    
    if not programs:
        print("❌ No programs found in data")
        return
    
    print(f"\n📚 Found {len(programs)} programs\n")
    
    downloaded = 0
    skipped = 0
    
    for program in programs:
        program_name = program.get('name_th', '') or program.get('name_en', '')
        if not program_name:
            continue
        
        print(f"Processing: {program_name}")
        
        # Check if image already exists
        safe_name = program_name.replace('/', '-').replace('\\', '-')
        image_filename = f"{safe_name}.jpg"
        image_path = IMAGES_DIR / image_filename
        
        if image_path.exists():
            print(f"  ⊘ Image already exists, skipping")
            skipped += 1
            continue
        
        # Get keywords for image search
        keywords = get_program_keywords(program_name)
        
        # Try to get image from Unsplash
        image_url = get_unsplash_image(keywords)
        
        # If Unsplash fails, use fallback
        if not image_url:
            # Use fallback based on program name
            for key, fallback_url in FALLBACK_IMAGES.items():
                if key in program_name:
                    image_url = fallback_url
                    break
            
            # Default fallback
            if not image_url:
                image_url = DEFAULT_FALLBACK
        
        # Download image
        downloaded_path = download_image(image_url, image_filename)
        
        if downloaded_path:
            # Update program data with image path
            program['image'] = downloaded_path
            program['image_url'] = image_url
            print(f"  ✓ Downloaded: {downloaded_path}")
            downloaded += 1
        else:
            print(f"  ❌ Failed to download image")
            skipped += 1
    
    # Save updated data
    data['programs'] = programs
    with open(data_file, 'w', encoding='utf-8') as f:
        json.dump(data, f, ensure_ascii=False, indent=2)
    
    print(f"\n{'=' * 60}")
    print("Summary")
    print(f"{'=' * 60}")
    print(f"✓ Downloaded: {downloaded}")
    print(f"⊘ Skipped: {skipped}")
    print(f"📁 Images saved to: {IMAGES_DIR}")
    print(f"{'=' * 60}\n")

if __name__ == '__main__':
    download_program_images()
