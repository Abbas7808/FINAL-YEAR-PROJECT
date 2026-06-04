import re

index_path = 'frontend/index.html'
sos_path = 'frontend/emergency_sos.html'
triage_path = 'frontend/emergency_triage.html'

with open(index_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Extract head
head_match = re.search(r'(<!DOCTYPE html>.*?<body>)', content, re.DOTALL)
head_html = head_match.group(1) if head_match else "<!DOCTYPE html><html><body>"

# Update title in head
head_html = re.sub(r'<title>.*?</title>', '<title>Med Nova - Emergency & SOS</title>', head_html)

# Add tailwind script
head_html = head_html.replace('</head>', '    <script src="https://cdn.tailwindcss.com"></script>\n</head>')

# Extract nav
nav_match = re.search(r'(<!-- NAVBAR -->.*?</nav>)', content, re.DOTALL)
nav_html = nav_match.group(1) if nav_match else ""

# Extract footer
footer_match = re.search(r'(<!-- FOOTER -->.*?</html>)', content, re.DOTALL)
footer_html = footer_match.group(1) if footer_match else "</body></html>"

# Add link to emergency pages in the navbar
nav_html = nav_html.replace('<!-- CTA GROUP -->', '') # Ensure we don't break existing

nav_items = nav_html.split('</ul>')
if len(nav_items) > 1:
    emergency_links = """
        <li class="nav-item text-danger fw-bold"><a class="nav-link text-danger" href="emergency_sos.html">SOS Alerts</a></li>
        <li class="nav-item text-warning fw-bold"><a class="nav-link text-warning" href="emergency_triage.html">Fast Triage</a></li>
    """
    nav_html = nav_items[0] + emergency_links + '</ul>' + nav_items[1]


sos_body = """
    <!-- EMERGENCY SOS -->
    <section class="py-20 bg-red-50 min-h-[80vh] flex flex-col justify-center items-center">
        <div class="container mx-auto px-4 max-w-4xl text-center">
            <div class="bg-white rounded-3xl shadow-xl p-8 md:p-16 border-2 border-red-100 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-2 bg-red-600"></div>
                <div class="w-24 h-24 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-truck-medical text-4xl"></i>
                </div>
                <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4 tracking-tight">Active Emergency SOS</h1>
                <p class="text-xl text-gray-600 mb-10 max-w-2xl mx-auto">Pressing this button will immediately broadcast your medical profile and precise GPS coordinates to the 3 nearest connected hospitals in Kohat.</p>
                
                <button onclick="alert('SOS Broadcasted to leading hospitals!')" class="group relative inline-flex items-center justify-center px-12 py-6 text-2xl font-bold text-white transition-all duration-200 bg-red-600 font-pj rounded-full hover:bg-red-700 hover:shadow-2xl hover:shadow-red-600/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-600 shadow-xl shadow-red-500/20 active:scale-95">
                    BROADCAST SOS NOW
                </button>
                
                <div class="mt-12 pt-8 border-t border-gray-100 flex flex-col md:flex-row gap-6 justify-center text-left">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center text-gray-700"><i class="fas fa-location-dot"></i></div>
                        <div>
                            <p class="font-bold text-gray-900">Current Location</p>
                            <p class="text-sm text-gray-500">Detecting GPS coords...</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center text-gray-700"><i class="fas fa-shield-heart"></i></div>
                        <div>
                            <p class="font-bold text-gray-900">Profile Attached</p>
                            <p class="text-sm text-gray-500">Blood type & allergies included</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
"""

triage_body = """
    <!-- EMERGENCY TRIAGE -->
    <section class="py-16 bg-gray-50 min-h-screen">
        <div class="container mx-auto px-4 max-w-5xl">
            <div class="text-center mb-12">
                <span class="text-blue-600 font-bold uppercase tracking-wider text-sm mb-2 block">Fast Assessment</span>
                <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">Emergency Digital Triage</h1>
                <p class="text-lg text-gray-500 mt-3 max-w-2xl mx-auto">Select the severity of your symptoms to be automatically routed to the right medical team priority queue.</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <!-- RED Triage -->
                <div class="bg-white rounded-3xl p-8 shadow-lg border border-red-100 hover:-translate-y-2 transition-transform cursor-pointer relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-red-50 rounded-bl-full flex items-center justify-center"><i class="fas fa-triangle-exclamation text-red-500 text-xl ml-2 mb-2"></i></div>
                    <div class="w-4 h-4 rounded-full bg-red-500 mb-6 group-hover:scale-150 transition-transform"></div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Critical (Red)</h3>
                    <ul class="text-gray-600 space-y-2 mb-6 text-sm">
                        <li><i class="fas fa-check text-red-400 mr-2"></i>Severe bleeding</li>
                        <li><i class="fas fa-check text-red-400 mr-2"></i>Heart attack signs</li>
                        <li><i class="fas fa-check text-red-400 mr-2"></i>Loss of consciousness</li>
                    </ul>
                    <a href="emergency_sos.html" class="block text-center w-full py-3 bg-red-50 text-red-600 font-bold rounded-xl hover:bg-red-600 hover:text-white transition-colors">Select Critical</a>
                </div>
                
                <!-- YELLOW Triage -->
                <div class="bg-white rounded-3xl p-8 shadow-lg border border-yellow-100 hover:-translate-y-2 transition-transform cursor-pointer relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-yellow-50 rounded-bl-full flex items-center justify-center"><i class="fas fa-clock text-yellow-500 text-xl ml-2 mb-2"></i></div>
                    <div class="w-4 h-4 rounded-full bg-yellow-500 mb-6 group-hover:scale-150 transition-transform"></div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Urgent (Yellow)</h3>
                    <ul class="text-gray-600 space-y-2 mb-6 text-sm">
                        <li><i class="fas fa-check text-yellow-400 mr-2"></i>Severe pain</li>
                        <li><i class="fas fa-check text-yellow-400 mr-2"></i>Deep lacerations</li>
                        <li><i class="fas fa-check text-yellow-400 mr-2"></i>High fever</li>
                    </ul>
                    <button onclick="alert('Priority Queue Joined: Urgent')" class="w-full py-3 bg-yellow-50 text-yellow-600 font-bold rounded-xl hover:bg-yellow-500 hover:text-white transition-colors">Join Priority Queue</button>
                </div>
                
                <!-- GREEN Triage -->
                <div class="bg-white rounded-3xl p-8 shadow-lg border border-green-100 hover:-translate-y-2 transition-transform cursor-pointer relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-green-50 rounded-bl-full flex items-center justify-center"><i class="fas fa-stethoscope text-green-500 text-xl ml-2 mb-2"></i></div>
                    <div class="w-4 h-4 rounded-full bg-green-500 mb-6 group-hover:scale-150 transition-transform"></div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Standard (Green)</h3>
                    <ul class="text-gray-600 space-y-2 mb-6 text-sm">
                        <li><i class="fas fa-check text-green-400 mr-2"></i>Minor injuries</li>
                        <li><i class="fas fa-check text-green-400 mr-2"></i>Cold / Flu</li>
                        <li><i class="fas fa-check text-green-400 mr-2"></i>Standard consults</li>
                    </ul>
                    <a href="appointment.html" class="block text-center w-full py-3 bg-green-50 text-green-600 font-bold rounded-xl hover:bg-green-600 hover:text-white transition-colors">Book Standard Visit</a>
                </div>
            </div>
            
            <div class="mt-12 bg-white rounded-2xl p-6 border flex items-center gap-4 text-gray-600">
                <i class="fas fa-circle-info text-2xl text-blue-500"></i>
                <p class="text-sm">This digital triage system assists in sorting patient priority. Standard booking is connected directly to hospital reception algorithms for maximum efficiency.</p>
            </div>
        </div>
    </section>
"""

with open(sos_path, 'w', encoding='utf-8') as f:
    f.write(head_html + nav_html + sos_body + footer_html)

with open(triage_path, 'w', encoding='utf-8') as f:
    f.write(head_html + nav_html + triage_body + footer_html)

print("Generated Emergency pages successfully!")
