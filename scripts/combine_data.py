"""
Combine scraped data from multiple sources into a single import file.
Uses existing news data and new site/program data.
"""

import json
import os
from datetime import datetime

OUTPUT_DIR = "scraped_data"

def main():
    print("Combining scraped data...")
    
    # Load existing news data (has titles and dates)
    news_data = []
    try:
        with open(os.path.join(OUTPUT_DIR, 'website_content.json'), 'r', encoding='utf-8') as f:
            existing = json.load(f)
            news_data = existing.get('news', [])
            print(f"  Loaded {len(news_data)} news articles from existing scrape")
    except Exception as e:
        print(f"  Error loading existing news: {e}")
    
    # Load new site/program data
    site_info = {}
    programs = []
    try:
        with open(os.path.join(OUTPUT_DIR, 'complete_content.json'), 'r', encoding='utf-8') as f:
            new_data = json.load(f)
            site_info = new_data.get('site_info', {})
            programs = new_data.get('programs', [])
            print(f"  Loaded site info and {len(programs)} programs")
    except Exception as e:
        print(f"  Error loading new data: {e}")
    
    # Create combined data structure
    combined = {
        'metadata': {
            'source': 'https://sci.uru.ac.th',
            'combined_at': datetime.now().isoformat(),
            'description': 'Faculty of Science and Technology, Uttaradit Rajabhat University'
        },
        'site_info': site_info,
        'news': news_data,
        'programs': programs,
        'departments': [
            {'name_th': 'สำนักงานคณบดี', 'name_en': 'Dean Office', 'code': 'DEAN'},
            {'name_th': 'สาขาวิชาคณิตศาสตร์ประยุกต์', 'name_en': 'Applied Mathematics', 'code': 'MATH'},
            {'name_th': 'สาขาวิชาชีววิทยา', 'name_en': 'Biology', 'code': 'BIO'},
            {'name_th': 'สาขาวิชาเคมี', 'name_en': 'Chemistry', 'code': 'CHEM'},
            {'name_th': 'สาขาวิชาเทคโนโลยีสารสนเทศ', 'name_en': 'Information Technology', 'code': 'IT'},
            {'name_th': 'สาขาวิชาวิทยาการคอมพิวเตอร์', 'name_en': 'Computer Science', 'code': 'CS'},
            {'name_th': 'สาขาวิชาวิทยาการข้อมูล', 'name_en': 'Data Science', 'code': 'DS'},
            {'name_th': 'สาขาวิชาวิทยาศาสตร์การกีฬาและการออกกำลังกาย', 'name_en': 'Sports and Exercise Science', 'code': 'SPORT'},
            {'name_th': 'สาขาวิชาวิทยาศาสตร์สิ่งแวดล้อม', 'name_en': 'Environmental Science', 'code': 'ENV'},
            {'name_th': 'สาขาวิชาสาธารณสุขศาสตร์', 'name_en': 'Public Health', 'code': 'PH'},
            {'name_th': 'สาขาวิชาอาหารและโภชนาการ', 'name_en': 'Food and Nutrition', 'code': 'FOOD'},
        ]
    }
    
    # Save combined data
    output_file = os.path.join(OUTPUT_DIR, 'import_data.json')
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(combined, f, ensure_ascii=False, indent=2)
    
    print(f"\nSaved combined data to: {output_file}")
    print(f"  News articles: {len(combined['news'])}")
    print(f"  Programs: {len(combined['programs'])}")
    print(f"  Departments: {len(combined['departments'])}")

if __name__ == "__main__":
    main()
