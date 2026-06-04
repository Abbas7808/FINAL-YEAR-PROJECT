import os
import re

html_files = [f for f in os.listdir('frontend') if f.endswith('.html')]

emergency_links = """
                    <li class="nav-item text-danger fw-bold"><a class="nav-link text-danger" href="emergency_sos.html">SOS Alerts</a></li>
                    <li class="nav-item text-warning fw-bold"><a class="nav-link text-warning" href="emergency_triage.html">Fast Triage</a></li>
"""

for file in html_files:
    path = os.path.join('frontend', file)
    with open(path, 'r', encoding='utf-8') as f:
        content = f.read()

    # Skip if already added
    if 'emergency_sos.html' in content:
        continue

    # Find the end of the ul elements in navbar
    # Look for </ul> that comes right before the CTA buttons
    # In index.html: it's `<ul class="navbar-nav mx-auto ..."> ... </ul>`
    # Replace the last `</ul>` inside the navbar collapse area.
    
    # We can match `<ul class="navbar-nav` and insert before closing `</ul>`
    # Better yet, search for `<li class="nav-item"><a class="nav-link" href="diet_plan.html">Diet Plan</a></li>` or just replace `</ul>`
    
    nav_match = re.search(r'(<ul[^>]*navbar-nav[^>]*>.*?)(</ul>)', content, re.DOTALL)
    if nav_match:
        new_nav = nav_match.group(1) + emergency_links + "                </ul>"
        content = content.replace(nav_match.group(0), new_nav)
        
        with open(path, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Updated {file}")
