"""Quick script to check scraped data quality"""
import json
import os

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
data_file = os.path.join(SCRIPT_DIR, "scraped_data", "all_content.json")

with open(data_file, 'r', encoding='utf-8') as f:
    data = json.load(f)

print("=" * 60)
print("Data Quality Check")
print("=" * 60)

# Check news
news = data.get('news', [])
print(f"\nNews Articles: {len(news)}")
news_with_content = sum(1 for n in news if n.get('content') and len(n.get('content', '')) > 50)
print(f"  - With content (>50 chars): {news_with_content}")
print(f"  - Without content: {len(news) - news_with_content}")

if news:
    sample = news[0]
    print(f"\nSample News Article:")
    print(f"  Title: {sample.get('title', '')[:60]}...")
    print(f"  Content length: {len(sample.get('content', ''))}")
    print(f"  Images: {len(sample.get('images', []))}")
    print(f"  Has excerpt: {bool(sample.get('excerpt'))}")

# Check programs
programs = data.get('programs', [])
print(f"\nPrograms: {len(programs)}")
for prog in programs[:3]:
    print(f"  - {prog.get('name_th', '')}")

# Check personnel
personnel = data.get('personnel', [])
print(f"\nPersonnel: {len(personnel)}")
for person in personnel[:3]:
    print(f"  - {person.get('name_th', '')} ({person.get('position', 'N/A')})")

# Check departments
departments = data.get('departments', [])
print(f"\nDepartments: {len(departments)}")
for dept in departments:
    print(f"  - {dept.get('name_th', '')}")

print("\n" + "=" * 60)
