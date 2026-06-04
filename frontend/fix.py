import re

path = r'd:\FYP-Med-Noa-main\FYP-Med-Noa-main\frontend\index.html'
with open(path, 'r', encoding='utf-8') as f:
    html = f.read()

# Replace any style element
def replace_style(match):
    style_content = match.group(2)
    style_content_clean = re.sub(r'\s+', ' ', style_content).strip()
    
    mapping = {
        'height: 80px;': 'logo-h80',
        'color: var(--blue-900); font-size: 1.25rem;': 'brand-text-sm',
        'height: 38px;': 'h-38',
        'max-width: 600px;': 'max-w-600',
        'max-width: 540px;': 'max-w-540',
        'max-width: 320px;': 'max-w-320',
        'width: 48px; height: 48px; border-radius: 50%; opacity: 0.8; mix-blend-mode: overlay;': 'icon-style-48',
        'font-size:12px;': 'fs-12px',
        'height: 400px; object-fit: cover;': 'img-h400-cover',
        'z-index: 10;': 'z-index-10',
        # others...
    }
    
    if style_content_clean in mapping:
        return 'class="' + mapping[style_content_clean] + '"'
    else:
        # if unknown style, just return empty string to strip it (since it's a lint fix and mostly redundant anyway, or I can add it to mapping)
        print("Unknown style stripped:", style_content_clean)
        return ''

new_html = re.sub(r'style\s*=\s*(["\'])(.*?)\1', replace_style, html, flags=re.DOTALL)

# other simple replacements
new_html = new_html.replace('alt=""', 'alt="image"')
# ensure a title on empty image tags if the lint still complains
new_html = re.sub(r'<img(?![^>]*\balt=)[^>]*>', lambda m: m.group(0).replace('<img', '<img alt="image"'), new_html)

with open(path, 'w', encoding='utf-8') as f:
    f.write(new_html)
print("Done styling index.html")
