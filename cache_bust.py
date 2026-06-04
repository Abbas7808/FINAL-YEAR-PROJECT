"""
Cache-Buster Script for Med Nova
---------------------------------
Adds a ?v=<timestamp> query parameter to all local CSS and JS references
across both frontend HTML files and backend PHP files. This forces the
browser to fetch fresh copies instead of serving stale cached versions.

Usage: python cache_bust.py
"""
import os, re, time

VERSION = str(int(time.time()))  # unique version stamp
ROOT = os.path.dirname(os.path.abspath(__file__))

# Patterns that match local resource references (not CDN)
PATTERNS = [
    # CSS: href="css/style.css" or href="assets/css/style.css" (with optional old ?v=...)
    (r'(href\s*=\s*["\'])([^"\']*?(?:style|theme)\.css)(\?[^"\']*)?(["\'])',
     lambda m: f'{m.group(1)}{m.group(2)}?v={VERSION}{m.group(4)}'),
    # JS: src="js/app.js" or src="assets/js/theme.js" (with optional old ?v=...)
    (r'(src\s*=\s*["\'])([^"\']*?(?:app|theme|main)\.js)(\?[^"\']*)?(["\'])',
     lambda m: f'{m.group(1)}{m.group(2)}?v={VERSION}{m.group(4)}'),
]

count = 0
for dirpath, _dirs, filenames in os.walk(ROOT):
    # Skip hidden dirs, node_modules, etc.
    if any(skip in dirpath for skip in ['.git', 'node_modules', '__pycache__']):
        continue
    for fname in filenames:
        if not fname.endswith(('.html', '.php')):
            continue
        fpath = os.path.join(dirpath, fname)
        with open(fpath, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
        
        new_content = content
        for pattern, repl in PATTERNS:
            new_content = re.sub(pattern, repl, new_content)
        
        if new_content != content:
            with open(fpath, 'w', encoding='utf-8') as f:
                f.write(new_content)
            rel = os.path.relpath(fpath, ROOT)
            print(f"  [OK] Updated: {rel}")
            count += 1

print(f"\n{'='*40}")
print(f"  Cache-busted {count} files with v={VERSION}")
print(f"{'='*40}")
print(f"\nNow do a HARD REFRESH in Chrome:")
print(f"  Ctrl + Shift + R  (or Ctrl + F5)")
print(f"\nAll your latest CSS/JS changes will now show up!")
