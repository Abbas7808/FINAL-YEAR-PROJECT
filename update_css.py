import os
import re

css_path = 'frontend/css/style.css'
with open(css_path, 'r', encoding='utf-8') as f:
    css_content = f.read()

modern_overrides = """
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

:root {
  --blue-900: #0F172A;
  --blue-700: #1D4ED8;
  --accent: #2563EB;
  --muted: #64748B;
  --soft-bg: #F8FAFC;
  --card-bg: #FFFFFF;
  --radius: 16px;
  --font-sans: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
}

body {
  font-family: var(--font-sans) !important;
  color: #334155;
  background-color: var(--soft-bg);
  -webkit-font-smoothing: antialiased;
}

h1, h2, h3, h4, h5, h6 {
  font-weight: 700;
  letter-spacing: -0.025em;
  color: var(--blue-900);
}

.btn {
  border-radius: 12px !important;
  font-weight: 600 !important;
  padding: 0.65rem 1.5rem !important;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
  border: none;
}
.btn-primary {
  background-color: var(--accent) !important;
  color: white !important;
  box-shadow: 0 4px 14px 0 rgba(37, 99, 235, 0.3) !important;
}
.btn-primary:hover {
  background-color: var(--blue-700) !important;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(37, 99, 235, 0.25) !important;
}
.btn-outline-primary {
  background-color: transparent !important;
  border: 2px solid var(--accent) !important;
  color: var(--accent) !important;
}
.btn-outline-primary:hover {
  background-color: var(--accent) !important;
  color: white !important;
}
.rounded-pill { border-radius: 9999px !important; }

.navbar {
  background: rgba(255, 255, 255, 0.85) !important;
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border-bottom: 1px solid rgba(226, 232, 240, 0.6);
  padding: 1rem 0 !important;
}
.navbar-brand .brand-text { font-weight: 800; letter-spacing: -0.5px; }

.form-control, .form-select {
  border-radius: 12px;
  border: 1px solid #E2E8F0;
  padding: 0.75rem 1rem;
  background-color: #F8FAFC;
  box-shadow: none !important;
  transition: all 0.2s;
}
.form-control:focus, .form-select:focus {
  background-color: #FFFFFF;
  border-color: var(--accent);
  box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1) !important;
}

.card, .service-pro-card, .doctor-pro-card, .appointment-card, .bd-card {
  border: 1px solid rgba(226, 232, 240, 0.8) !important;
  border-radius: var(--radius) !important;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05) !important;
  background: var(--card-bg) !important;
  transition: transform 0.3s ease, box-shadow 0.3s ease !important;
}
.card:hover, .service-pro-card:hover, .doctor-pro-card:hover {
  transform: translateY(-8px) !important;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.04) !important;
}

.hero-blob-bg {
  width: min(500px, 90vw);
  height: min(500px, 90vw);
  background: radial-gradient(circle, #DBEAFE 0%, transparent 60%);
}
.bg-soft { background-color: #F8FAFC !important; }
"""

if ':root {' in css_content:
    css_content = re.sub(r':root\s*\{.*?\}(?=\s*body)', '', css_content, flags=re.DOTALL)
    css_content = re.sub(r'body\s*\{.*?\}(?=\s*\/\* NAVBAR)', '', css_content, flags=re.DOTALL)
    
new_css = modern_overrides + "\n/* --- ORIGINAL CSS BELOW --- */\n" + css_content
with open(css_path, 'w', encoding='utf-8') as f:
    f.write(new_css)
print("Updated style.css with modern Figma UI tokens!")
