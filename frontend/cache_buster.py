import os
import glob

# Paths to search
search_paths = [
    '../frontend/*.html',
    '../backend/**/*.php'
]

# Old and new cache busters
old_v = 'v=1775113900'
new_v = 'v=1775114100'

for path_pattern in search_paths:
    for filepath in glob.glob(path_pattern, recursive=True):
        try:
            with open(filepath, 'r', encoding='utf-8') as f:
                content = f.read()
            
            if old_v in content:
                content = content.replace(old_v, new_v)
                with open(filepath, 'w', encoding='utf-8') as f:
                    f.write(content)
                print(f"Updated {filepath}")
        except Exception as e:
            print(f"Error updating {filepath}: {e}")
