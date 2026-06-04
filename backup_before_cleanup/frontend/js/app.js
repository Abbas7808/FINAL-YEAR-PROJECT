/**
 * ==========================================================================
 * MED NOVA - FRONTEND CORE LOGIC (app.js)
 * ==========================================================================
 * This file handles all dynamic interactions, AJAX API calls, and 
 * UI enhancements for the Med Nova platform.
 * 
 * TABLE OF CONTENTS:
 * 1.  API SERVICE LAYER (Asynchronous fetch handlers)
 * 2.  GLOBAL UI ENHANCEMENTS (Scroll effects, nav animations)
 * 3.  REGIONAL DATA SYNC (Regions, Hospitals, Doctors)
 * 4.  BLOG & NEWS ENGINE (Dynamic content generation)
 * 5.  FORM HANDLERS (Appointments, Contact, Blood Donation)
 * 6.  DIET & AI SCAN INTERFACES
 * ==========================================================================
 */

const getBaseApiUrl = () => {
    if (window.location.hostname === 'fyp.nexsoft.site' || window.location.hostname === 'www.fyp.nexsoft.site') {
        return 'https://fyp.nexsoft.site/backend/index.php?route=api';
    }
    const path = window.location.pathname;
    const frontendIndex = path.indexOf('/frontend/');
    if (frontendIndex !== -1) {
        const projectFolder = path.substring(0, frontendIndex);
        return `${window.location.origin}${projectFolder}/backend/index.php?route=api`;
    }
    return '../backend/index.php?route=api';
};
const API_URL = getBaseApiUrl(); 

/* 1. API SERVICE LAYER - Unified AJAX Handlers */

const api = {
    getRegions: async () => {
        try {
            const response = await fetch(`${API_URL}/regions`);
            return await response.json();
        } catch (error) {
            console.error("Error fetching regions:", error);
            return [];
        }
    },
    getHospitals: async (regionId = '', type = '') => {
        try {
            let url = `${API_URL}/hospitals`;
            const separator = url.includes('?') ? '&' : '?';
            const params = new URLSearchParams();
            if (regionId) params.append('region_id', regionId);
            if (type && type !== 'all') params.append('type', type);
            const queryString = params.toString();
            if (queryString) url += separator + queryString;
            const response = await fetch(url);
            return await response.json();
        } catch (error) {
            console.error("Error fetching hospitals:", error);
            return [];
        }
    },
    getDoctors: async (hospitalId = '') => {
        try {
            let url = `${API_URL}/doctors`;
            const separator = url.includes('?') ? '&' : '?';
            if (hospitalId) url += separator + `hospital_id=${hospitalId}`;
            const response = await fetch(url);
            return await response.json();
        } catch (error) {
            console.error("Error fetching doctors:", error);
            return [];
        }
    },
    getServices: async () => {
        try {
            const response = await fetch(`${API_URL}/services`);
            return await response.json();
        } catch (error) {
            console.error("Error fetching services:", error);
            return [];
        }
    },
    bookAppointment: async (data) => {
        const response = await fetch(`${API_URL}/appointment`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        return await response.json();
    },
    sendMessage: async (data) => {
        const response = await fetch(`${API_URL}/contact`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        return await response.json();
    },
    getHospital: async (identifier) => {
        try {
            let query = '';
            if (String(identifier).includes('@')) {
                query = `email=${identifier}`;
            } else if (!isNaN(identifier)) {
                query = `id=${identifier}`;
            } else {
                query = `slug=${identifier}`;
            }
            let url = `${API_URL}/hospitals`;
            const separator = url.includes('?') ? '&' : '?';
            const response = await fetch(`${url}${separator}${query}`);
            return await response.json();
        } catch (error) {
            console.error("Error fetching hospital:", error);
            return null;
        }
    }
};

$(document).ready(function () {
    console.log('Document ready, initializing Med-Nova app...');

    // --- GLOBAL UI ENHANCEMENTS (animations + interactions) ---
    (function initUiEnhancements() {
        const $nav = $('nav.navbar').first();

        const normalizeNavbar = () => {
            const nav = document.querySelector('nav.navbar');
            if (!nav) return;

            nav.querySelectorAll('.nav-dot').forEach((dot) => dot.remove());

            nav.querySelectorAll('a[href="#hospitalLoginModal"]').forEach((link) => {
                link.setAttribute('href', '../backend/?route=auth/login');
                link.removeAttribute('data-bs-toggle');
                link.removeAttribute('data-bs-target');
            });

            const smartDropdown = nav.querySelector('#smartFeaturesDropdown');
            const smartMenu = smartDropdown ? smartDropdown.closest('.dropdown')?.querySelector('.dropdown-menu') : null;
            const donateLink = nav.querySelector('a.nav-link[href="blood-donation.html"]');
            const fastTriageHref = 'emergency_triage.html';

            const urgentItem = nav.querySelector('#urgentResponseDropdown')?.closest('.nav-item');
            if (urgentItem) urgentItem.remove();

            if (smartMenu) {
                if (donateLink && !smartMenu.querySelector('a[href="blood-donation.html"]')) {
                    const donateItem = donateLink.closest('.nav-item');
                    if (donateItem) donateItem.remove();

                    const donateItemWrapper = document.createElement('li');
                    donateItemWrapper.innerHTML = '<a class="dropdown-item" href="blood-donation.html"><i class="fas fa-tint text-danger me-2"></i>Donate Blood</a>';
                    smartMenu.appendChild(donateItemWrapper);
                }

                if (!smartMenu.querySelector(`a[href="${fastTriageHref}"]`)) {
                    const divider = document.createElement('li');
                    divider.innerHTML = '<hr class="dropdown-divider opacity-50">';
                    smartMenu.appendChild(divider);

                    const triageItem = document.createElement('li');
                    triageItem.innerHTML = '<a class="dropdown-item" href="emergency_triage.html"><i class="fas fa-stethoscope text-primary me-2"></i>Fast Triage</a>';
                    smartMenu.appendChild(triageItem);
                }
            }
        };

        normalizeNavbar();

        // Navbar subtle blur/shadow on scroll
        const updateNav = () => {
            if (!$nav.length) return;
            const scrolled = window.scrollY > 8;
            $nav.toggleClass('mn-nav-scrolled', scrolled);
        };
        updateNav();
        window.addEventListener('scroll', updateNav, { passive: true });

        // Back-to-top button (injected once)
        if (!document.getElementById('mn-back-to-top')) {
            const btn = document.createElement('button');
            btn.id = 'mn-back-to-top';
            btn.type = 'button';
            btn.className = 'mn-back-to-top';
            btn.setAttribute('aria-label', 'Back to top');
            btn.innerHTML = '<i class="fas fa-arrow-up"></i>';
            document.body.appendChild(btn);

            const toggleBtn = () => {
                btn.classList.toggle('is-visible', window.scrollY > 500);
            };
            toggleBtn();
            window.addEventListener('scroll', toggleBtn, { passive: true });
            btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
        }

        // Smooth scrolling for in-page anchors
        document.addEventListener('click', (e) => {
            const a = e.target.closest && e.target.closest('a[href^="#"]');
            if (!a) return;
            const href = a.getAttribute('href');
            if (!href || href === '#' || a.hasAttribute('data-bs-toggle')) return;
            const el = document.querySelector(href);
            if (!el) return;
            e.preventDefault();
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        // Button ripple (only for real buttons/btn-like elements)
        document.addEventListener('click', (e) => {
            const target = e.target.closest && e.target.closest('.btn, .ripple-effect');
            if (!target) return;
            const rect = target.getBoundingClientRect();

            // Ensure the ripple can position correctly
            const computed = window.getComputedStyle(target);
            if (computed.position === 'static') target.style.position = 'relative';
            target.style.overflow = 'hidden';

            const ripple = document.createElement('span');
            ripple.className = 'mn-ripple';
            const size = Math.max(rect.width, rect.height);
            ripple.style.width = ripple.style.height = `${size}px`;
            ripple.style.left = `${e.clientX - rect.left - size / 2}px`;
            ripple.style.top = `${e.clientY - rect.top - size / 2}px`;
            target.appendChild(ripple);
            ripple.addEventListener('animationend', () => ripple.remove());
        }, { passive: true });

        // Scroll-reveal: auto-apply to common components
        const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (!prefersReducedMotion && 'IntersectionObserver' in window) {
            const selectors = [
                '.internal-hero .container',
                '.hero-redesign .col-lg-6',
                '.stat-card-blue',
                '.service-pro-card',
                '.doctor-pro-card',
                '.doctor-card-pro',
                '.blog-pro-card',
                '.appointment-card',
                '.bd-card',
                '.card.shadow-sm',
                '.card.shadow-lg'
            ];

            const nodes = Array.from(document.querySelectorAll(selectors.join(',')));
            nodes.forEach((n, i) => {
                if (n.classList.contains('mn-reveal')) return;
                n.classList.add('mn-reveal');
                if (i % 3 === 0) n.classList.add('mn-reveal--up');
                if (i % 3 === 1) n.classList.add('mn-reveal--scale');
                if (i % 3 === 2) n.classList.add('mn-reveal--right');
            });

            const io = new IntersectionObserver((entries) => {
                for (const entry of entries) {
                    if (!entry.isIntersecting) continue;
                    entry.target.classList.add('is-visible');
                    io.unobserve(entry.target);
                }
            }, { threshold: 0.12, rootMargin: '0px 0px -10% 0px' });

            nodes.forEach(n => io.observe(n));
        }
    })();
    // --- SHARED HELPER FUNCTIONS ---
    function loadRegions(selectSelector) {
        console.log('Loading regions for selector:', selectSelector);
        console.log('Element found:', $(selectSelector).length);
        return $.getJSON(`${API_URL}/regions`, function (data) {
            console.log('Regions data received:', data);
            const regionSelect = $(selectSelector);
            regionSelect.empty().append(new Option('Select Region', ''));
            data.forEach(r => {
                regionSelect.append(new Option(r.name, r.id));
                console.log('Added region:', r.name, r.id);
            });
            console.log('Regions loaded into select, total options:', regionSelect.find('option').length);
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.error('Failed to load regions:', textStatus, errorThrown, jqXHR.responseText);
        });
    }

    async function onRegionChange(regionId, hospSelectSelector, deptSelectSelector, docSelectSelector) {
        console.log('Region changed to:', regionId, 'for selector:', hospSelectSelector);
        const hospSelect = $(hospSelectSelector);
        hospSelect.empty().append(new Option('Select Hospital', '')).prop('disabled', true);
        if (deptSelectSelector) $(deptSelectSelector).empty().append(new Option('Select Hospital First', '')).prop('disabled', true);
        if (docSelectSelector) $(docSelectSelector).empty().append(new Option('Select Department First', '')).prop('disabled', true);

        if (regionId) {
            console.log('Loading hospitals for region:', regionId);
            hospSelect.prop('disabled', false).append(new Option('Loading...', ''));
            try {
                const hospitals = await $.getJSON(`${API_URL}/hospitals&region_id=${regionId}`);
                console.log('Hospitals loaded:', hospitals);
                hospSelect.empty().append(new Option('Select Hospital', ''));
                hospitals.forEach(h => {
                    hospSelect.append(new Option(h.name, h.id));
                });
            } catch (e) {
                console.error("Error loading hospitals:", e);
                hospSelect.empty().append(new Option('Error loading hospitals', ''));
            }
        }
    }

    async function onHospitalChange(hospitalId, deptSelectSelector, docSelectSelector) {
        console.log('Hospital changed to:', hospitalId, 'for selector:', deptSelectSelector);
        if (deptSelectSelector) {
            const deptSelect = $(deptSelectSelector);
            deptSelect.empty().append(new Option('Select Department', '')).prop('disabled', true);
            if (docSelectSelector) $(docSelectSelector).empty().append(new Option('Select Department First', '')).prop('disabled', true);

            if (hospitalId) {
                console.log('Loading departments for hospital:', hospitalId);
                deptSelect.prop('disabled', false).append(new Option('Loading...', ''));
                try {
                    const departments = await $.getJSON(`${API_URL}/departments&hospital_id=${hospitalId}`);
                    console.log('Departments loaded:', departments);
                    deptSelect.empty().append(new Option('Select Department', ''));
                    departments.forEach(d => {
                        deptSelect.append(new Option(d.name, d.id));
                    });
                } catch (e) {
                    console.error('Error loading departments:', e);
                    deptSelect.empty().append(new Option('Error loading departments', ''));
                }
            }
        }
    }

    async function onDepartmentChange(deptId, docSelectSelector) {
        console.log('Department changed to:', deptId, 'for selector:', docSelectSelector);
        if (docSelectSelector) {
            const docSelect = $(docSelectSelector);
            docSelect.empty().append(new Option('Select Doctor', '')).prop('disabled', true);

            if (deptId) {
                console.log('Loading doctors for department:', deptId);
                docSelect.prop('disabled', false).append(new Option('Loading...', ''));
                try {
                    const hospitalId = $(docSelectSelector).closest('form').find('select[name="hospital"]').val();
                    console.log('Hospital ID for doctors:', hospitalId);
                    const doctors = await $.getJSON(`${API_URL}/doctors&hospital_id=${hospitalId}`);
                    console.log('Doctors loaded:', doctors);
                    const deptName = $(docSelectSelector).closest('form').find('select[name="department"] option:selected').text();
                    console.log('Filtering by department name:', deptName);
                    const filtered = doctors.filter(d => d.specialty === deptName || d.specialization === deptName);
                    console.log('Filtered doctors:', filtered);

                    docSelect.empty().append(new Option('Select Doctor', ''));
                    if (filtered.length > 0) {
                        filtered.forEach(d => {
                            docSelect.append(new Option(d.name, d.id));
                        });
                    } else {
                        docSelect.append(new Option(`No doctors in ${deptName}`, ''));
                    }
                } catch (e) {
                    console.error('Error loading doctors:', e);
                    docSelect.empty().append(new Option('Error loading doctors', ''));
                }
            }
        }
    }

    // --- HOME PAGE LOGIC ---
    if ($('#home-doctors-grid').length) {
        api.getDoctors().then(doctors => {
            const topDoctors = doctors.slice(0, 3);
            // Local fallback images
            const localDoctorImgs = [
                'assets/doctor-fatima.jpg',
                'assets/doctor-male-1.jpg',
                'assets/doctor-male-2.jpg',
                'assets/doctor-female-1.jpg'
            ];
            const doctorsHtml = topDoctors.map((doc, idx) => {
                const fallback = localDoctorImgs[idx % localDoctorImgs.length];
                return `
                <div class="col-md-4">
                    <div class="doctor-pro-card">
                        <div class="doctor-img-box">
                            <img src="${doc.image || fallback}" alt="${doc.name}" onerror="this.src='${fallback}'">
                        </div>
                        <h4 class="doctor-name">${doc.name}</h4>
                        <div class="doctor-role">${doc.specialty}</div>
                    </div>
                </div>
            `}).join('');
            $('#home-doctors-grid').html(doctorsHtml || '<div class="col-12 text-center py-3 text-muted">No doctors found.</div>');
        });
    }

    // --- DOCTORS/FACILITIES PAGE LOGIC ---
    if ($('#main-list-container').length) {
        let allFacilities = [];
        let currentType = 'hospital';
        let currentRegion = '';

        api.getRegions().then(regions => {
            const regionSelect = $('#region-filter');
            regions.forEach(r => {
                regionSelect.append(new Option(r.name, r.id));
            });
        });

        const loadFacilities = async () => {
            $('#main-list-container').html('<div class="text-center p-5"><div class="spinner-border text-primary"></div></div>');
            allFacilities = await api.getHospitals(currentRegion, currentType);
            renderFacilities(allFacilities);
        };

        // Hospital / clinic image pools
        const hospitalImgs = [
            'assets/hospital-1.png',
            'assets/hospital-2.png',
            'assets/hospital-3.png',
            'assets/images/hospital_placeholder.png'
        ];
        const clinicImgs = [
            'assets/images/hospital_placeholder.png',
            'assets/hospital-3.png',
            'assets/hospital-1.png'
        ];
        const doctorImgs = [
            'assets/doctor-fatima.jpg',
            'assets/doctor-male-1.jpg',
            'assets/doctor-male-2.jpg',
            'assets/doctor-female-1.jpg',
            'assets/default-doctor.jpg'
        ];

        function pickImg(pool, seed) {
            return pool[Math.abs(seed) % pool.length];
        }

        const renderFacilities = (facilities) => {
            const searchQuery = $('#universal-search').val().toLowerCase();
            const filtered = facilities.filter(f => {
                return f.name.toLowerCase().includes(searchQuery) || (f.address || '').toLowerCase().includes(searchQuery);
            });
            $('#results-count').text(`${filtered.length} Facilities Found`);
            const html = filtered.map((f, idx) => {
                const isClinic = (f.type || '').toLowerCase().includes('clinic');
                const pool = isClinic ? clinicImgs : hospitalImgs;
                const hasRealImg = f.image && !f.image.includes('placeholder');
                const imgSrc = hasRealImg ? f.image : pickImg(pool, f.id || idx);
                const fallbackSrc = isClinic ? clinicImgs[0] : hospitalImgs[0];
                const badge = isClinic
                    ? `<span class="badge bg-success-subtle text-success rounded-pill ms-auto"><i class="fas fa-clinic-medical me-1"></i>Clinic</span>`
                    : `<span class="badge bg-primary-subtle text-primary rounded-pill ms-auto"><i class="fas fa-hospital me-1"></i>Hospital</span>`;
                return `
                <div class="facility-card rounded-3 bg-white mb-2 shadow-sm border-0 overflow-hidden" style="cursor:pointer; transition: transform .15s, box-shadow .15s;" data-id="${f.id}" onclick="showFacilityDetails(${f.id})"
                     onmouseenter="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 20px rgba(0,0,0,.10)'"
                     onmouseleave="this.style.transform='';this.style.boxShadow=''"
                >
                    <div class="d-flex align-items-stretch">
                        <div style="width:90px; min-height:90px; flex-shrink:0; overflow:hidden;">
                            <img src="${imgSrc}" alt="${f.name}" onerror="this.src='${fallbackSrc}'"
                                 style="width:100%; height:100%; object-fit:cover; object-position:center;">
                        </div>
                        <div class="p-3 d-flex flex-column justify-content-center flex-grow-1">
                            <div class="d-flex align-items-start gap-2">
                                <h6 class="mb-1 fw-bold text-dark flex-grow-1">${f.name}</h6>
                                ${badge}
                            </div>
                            <div class="small text-muted"><i class="fas fa-map-marker-alt me-1"></i>${f.address || 'Kohat'}</div>
                        </div>
                    </div>
                </div>
            `}).join('');
            $('#main-list-container').html(html || '<div class="text-center text-muted py-5">No facilities found.</div>');
        };

        window.showFacilityDetails = async (id) => {
            const facility = allFacilities.find(f => f.id == id);
            if (!facility) return;

            // Highlight selected card
            $('.facility-card').css('border-left', '');
            $(`.facility-card[data-id="${id}"]`).css('border-left', '4px solid var(--bs-primary)');

            const isClinic = (facility.type || '').toLowerCase().includes('clinic');
            const facilityImgPool = isClinic ? clinicImgs : hospitalImgs;
            const hasRealImg = facility.image && !facility.image.includes('placeholder');
            const facilityImg = hasRealImg ? facility.image : pickImg(facilityImgPool, facility.id || id);
            const facilityFallback = facilityImgPool[0];

            $('#detail-panel').html('<div class="text-center p-5"><div class="spinner-border text-primary"></div></div>');
            const doctors = await api.getDoctors(id);

            const doctorsHtml = doctors.length > 0 ? doctors.map((d, i) => {
                const hasDocImg = d.image && !d.image.includes('default-doctor');
                const docImg = hasDocImg ? d.image : pickImg(doctorImgs, d.id || i);
                const docFallback = 'assets/default-doctor.jpg';
                return `
                <div class="d-flex align-items-center gap-3 mb-3 p-2 rounded-3" style="background:#f8faff; transition: background .15s;"
                     onmouseenter="this.style.background='#e8f0fe'" onmouseleave="this.style.background='#f8faff'">
                    <div style="width:52px; height:52px; border-radius:50%; overflow:hidden; flex-shrink:0; border:2px solid #e3eeff;">
                        <img src="${docImg}" alt="${d.name}" onerror="this.src='${docFallback}'"
                             style="width:100%; height:100%; object-fit:cover; object-position:top;">
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold small text-dark">${d.name}</div>
                        <div class="text-muted" style="font-size:0.78rem;">${d.specialty || d.specialization || 'General Physician'}</div>
                    </div>
                    <a href="appointment.html?doctor=${encodeURIComponent(d.name)}&hospital=${encodeURIComponent(facility.name)}" class="btn btn-sm btn-primary rounded-pill px-3">Book</a>
                </div>
            `}).join('') : '<div class="text-center text-muted small py-3"><i class="fas fa-user-md fa-2x mb-2 d-block opacity-50"></i>No doctors listed yet.</div>';

            $('#detail-panel').html(`
                <div class="bg-white rounded-4 shadow overflow-hidden">
                    <div style="height:180px; overflow:hidden; position:relative;">
                        <img src="${facilityImg}" alt="${facility.name}" onerror="this.src='${facilityFallback}'"
                             style="width:100%; height:100%; object-fit:cover; object-position:center;">
                        <div style="position:absolute; inset:0; background:linear-gradient(to top, rgba(0,0,0,.85) 0%, rgba(0,0,0,.4) 50%, transparent 80%); display:flex; align-items:flex-end; padding:16px;">
                            <div style="color:#ffffff; text-shadow: 0 2px 8px rgba(0,0,0,.9), 0 1px 3px rgba(0,0,0,.7);">
                                <h5 class="fw-bold mb-1" style="color:#ffffff !important;">${facility.name}</h5>
                                <div class="small" style="color:#ffffff !important;"><i class="fas fa-map-marker-alt me-1"></i>${facility.address || 'Kohat'}</div>
                            </div>
                        </div>
                    </div>
                    <div class="p-4">
                        <h6 class="fw-bold text-muted small mb-3"><i class="fas fa-user-md me-2"></i>Available Doctors (${doctors.length})</h6>
                        <div style="max-height: 380px; overflow-y: auto; padding-right:4px;">${doctorsHtml}</div>
                    </div>
                </div>
            `).hide().fadeIn(300);
        };

        $('#region-filter').change(function () { currentRegion = $(this).val(); loadFacilities(); });
        $('#facility-tabs button').click(function () {
            $('#facility-tabs button').removeClass('active');
            $(this).addClass('active');
            currentType = $(this).data('type');
            loadFacilities();
        });
        $('#universal-search').on('input', function () { renderFacilities(allFacilities); });
        $('#reset-filters').on('click', function () {
            currentRegion = '';
            $('#region-filter').val('');
            $('#universal-search').val('');
            $('#facility-tabs button').removeClass('active');
            $('#facility-tabs button[data-type="hospital"]').addClass('active');
            currentType = 'hospital';
            loadFacilities();
        });
        loadFacilities();
    }

    // --- APPOINTMENT FORM LOGIC ---
    if ($('#step-2-form').length) {
        console.log('Appointment form found, loading regions...');
        
        async function loadDoctorsForHospital(hospitalId) {
            const docGroup = $('#doctor-selection-group');
            const docSelect = $('#step-2-form select[name="doctor"]');
            docSelect.empty().append(new Option('Select Doctor', '')).prop('disabled', true);
            docGroup.hide();

            if (hospitalId) {
                docSelect.prop('disabled', false).append(new Option('Loading doctors...', ''));
                try {
                    const doctors = await $.getJSON(`${API_URL}/doctors&hospital_id=${hospitalId}`);
                    docSelect.empty().append(new Option('Select Doctor (Optional)', ''));
                    if (doctors && doctors.length > 0) {
                        doctors.forEach(d => {
                            docSelect.append(new Option(d.name + ' (' + d.specialty + ')', d.id));
                        });
                        docGroup.show();

                        // Check if doctor query param exists
                        const urlParams = new URLSearchParams(window.location.search);
                        const docParam = urlParams.get('doctor');
                        if (docParam) {
                            docSelect.find('option').each(function() {
                                if ($(this).text().toLowerCase().includes(docParam.toLowerCase())) {
                                    docSelect.val($(this).val());
                                }
                            });
                        }
                    } else {
                        docSelect.empty().append(new Option('No registered doctors for this hospital', ''));
                        docGroup.show();
                    }
                } catch (e) {
                    console.error('Error loading doctors:', e);
                    docSelect.empty().append(new Option('Error loading doctors', ''));
                }
            }
        }

        loadRegions('#step-2-form select[name="region"]').then(async () => {
            console.log('Regions loaded successfully');
            const urlParams = new URLSearchParams(window.location.search);
            const hospitalParam = urlParams.get('hospital_id') || urlParams.get('hospital');
            if (hospitalParam) {
                console.log('Hospital param found:', hospitalParam);
                const hospital = await api.getHospital(hospitalParam);
                if (hospital && hospital.id) {
                    $('#step-2-form select[name="region"]').val(hospital.region_id);
                    await onRegionChange(hospital.region_id, '#step-2-form select[name="hospital"]');
                    $('#step-2-form select[name="hospital"]').val(hospital.id);
                    await loadDoctorsForHospital(hospital.id);
                }
            }
        }).catch(error => {
            console.error('Error loading regions:', error);
        });

        $('#step-2-form select[name="region"]').change(function () {
            onRegionChange($(this).val(), '#step-2-form select[name="hospital"]').then(() => {
                $('#doctor-selection-group').hide();
                $('#step-2-form select[name="doctor"]').empty().append(new Option('Select Doctor', ''));
            });
        });

        $('#step-2-form select[name="hospital"]').change(function () {
            loadDoctorsForHospital($(this).val());
        });

        $('#step-1-form').submit(function (e) {
            e.preventDefault();
            $('#step-1').addClass('d-none');
            $('#step-2').removeClass('d-none');
            $('#appointment-progress').css('width', '66%');
            $('#progress-text').text('Step 2: Selection');
            $('#progress-percent').text('66%');
            $('#step-label-1').removeClass('active').addClass('completed');
            $('#step-label-2').addClass('active');
        });

        $('#back-to-step-1').click(function () {
            $('#step-2').addClass('d-none');
            $('#step-1').removeClass('d-none');
            $('#appointment-progress').css('width', '33%');
            $('#progress-text').text('Step 1: Info');
            $('#progress-percent').text('33%');
            $('#step-label-1').addClass('active').removeClass('completed');
            $('#step-label-2').removeClass('active');
        });

        $('#step-2-form').submit(async function (e) {
            e.preventDefault();
            const btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true).text('Booking...');
            const form = $(this);
            const selectedHospitalName = form.find('select[name="hospital"] option:selected').text();
            const payload = {
                name: $('#name').val(),
                phone: $('#phone').val(),
                date: form.find('input[name="date"]').val(),
                time: $('#step-1-form select[name="time"]').val(),
                hospitalId: form.find('select[name="hospital"]').val(),
                doctorId: form.find('select[name="doctor"]').val() || null,
                type: 'consultation'
            };
            console.log('Submitting appointment with payload:', payload);
            try {
                const res = await api.bookAppointment(payload);
                if (res.success) {
                    $('#step-2').addClass('d-none');
                    $('#step-3').removeClass('d-none');
                    $('#appointment-progress').css('width', '100%');
                    $('#progress-text').text('Step 3: Done');
                    $('#progress-percent').text('100%');
                    $('#step-label-2').removeClass('active').addClass('completed');
                    $('#step-label-3').addClass('active');
                    $('#confirm-name').text(payload.name);
                    $('#confirm-hospital').text(selectedHospitalName);
                    $('#confirm-date').text(payload.date);
                } else {
                    alert(res.message || 'Booking failed');
                    btn.prop('disabled', false).text('Next Step ->');
                }
            } catch (e) {
                alert('Connection error.');
                btn.prop('disabled', false).text('Next Step ->');
            }
        });
    }

    // --- DIET PLAN FORM LOGIC ---
    if ($('#diet-plan-form').length) {
        loadRegions('#diet-plan-form select[name="region"]');
        $('#diet-plan-form select[name="region"]').change(function () {
            onRegionChange($(this).val(), '#diet-plan-form select[name="hospital"]', '#diet-plan-form select[name="department"]', '#diet-plan-form select[name="doctor"]');
        });
        $('#diet-plan-form select[name="hospital"]').change(function () {
            onHospitalChange($(this).val(), '#diet-plan-form select[name="department"]', '#diet-plan-form select[name="doctor"]');
        });
        $('#diet-plan-form select[name="department"]').change(function () {
            onDepartmentChange($(this).val(), '#diet-plan-form select[name="doctor"]');
        });
        $('#diet-plan-form').submit(async function (e) {
            e.preventDefault();
            const btn = $(this).find('button[type="submit"]');
            const originalText = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin"></i> Processing...').prop('disabled', true);
            const formData = {
                name: $(this).find('input[name="name"]').val(),
                phone: $(this).find('input[name="phone"]').val(),
                age: $(this).find('input[name="age"]').val(),
                weight: $(this).find('input[name="weight"]').val(),
                height: $(this).find('input[name="height"]').val(),
                hospitalId: $(this).find('select[name="hospital"]').val(),
                doctorId: $(this).find('select[name="doctor"]').val(),
                goal: $(this).find('select[name="goal"]').val(),
                conditions: $(this).find('textarea[name="conditions"]').val()
            };
            try {
                const res = await $.ajax({
                    url: `${API_URL}/dietPlan`,
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(formData)
                });
                if (res.success) {
                    $('#diet-plan-form').addClass('d-none');
                    $('#diet-success').removeClass('d-none');
                } else {
                    alert(res.message || 'Failed');
                    btn.html(originalText).prop('disabled', false);
                }
            } catch (err) {
                const errorMsg = err.responseJSON && err.responseJSON.message 
                                 ? err.responseJSON.message 
                                 : 'Connection Error';
                alert(errorMsg);
                btn.html(originalText).prop('disabled', false);
            }
        });
    }

    // --- BLOOD DONATION FORM LOGIC ---
    if ($('#donor-form').length) {
        loadRegions('#donor-form select[name="region"]');
        $('#donor-form select[name="region"]').change(function () {
            onRegionChange($(this).val(), '#donor-form select[name="hospital"]');
        });
        $('#donor-form').submit(async function (e) {
            e.preventDefault();
            const btn = $(this).find('button[type="submit"]');
            btn.text('Registering...').prop('disabled', true);
            const formData = {
                name: $(this).find('input[name="name"]').val(),
                age: $(this).find('input[name="age"]').val(),
                blood_group: $(this).find('select[name="blood_group"]').val(),
                phone: $(this).find('input[name="phone"]').val(),
                location: $(this).find('input[name="location"]').val(),
                hospitalId: $(this).find('select[name="hospital"]').val()
            };
            try {
                const res = await $.ajax({
                    url: `${API_URL}/registerDonor`,
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(formData)
                });
                if (res.success) {
                    alert('Registered as donor!');
                    this.reset();
                } else {
                    alert('Error: ' + res.message);
                }
            } catch (e) { alert('Connection error'); }
            finally { btn.text('Register Now').prop('disabled', false); }
        });
    }

    if ($('#patient-form').length) {
        loadRegions('#patient-form select[name="region"]');
        $('#patient-form select[name="region"]').change(function () {
            onRegionChange($(this).val(), '#patient-form select[name="hospital"]');
        });
        $('#patient-form').submit(async function (e) {
            e.preventDefault();
            const btn = $(this).find('button[type="submit"]');
            btn.text('Broadcasting...').prop('disabled', true);
            const formData = {
                patient_name: $(this).find('input[name="patient_name"]').val(),
                blood_group: $(this).find('select[name="blood_group"]').val(),
                urgency: $(this).find('select[name="urgency"]').val(),
                phone: $(this).find('input[name="phone"]').val(),
                hospitalId: $(this).find('select[name="hospital"]').val()
            };
            try {
                const res = await $.ajax({
                    url: `${API_URL}/requestBlood`,
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(formData)
                });
                if (res.success) {
                    alert('Broadcasted successfully!');
                    this.reset();
                } else {
                    alert('Error: ' + res.message);
                }
            } catch (e) { alert('Connection error'); }
            finally { btn.text('Broadcast Request').prop('disabled', false); }
        });
    }

    // --- CONTACT FORM LOGIC ---
    $('#contact-form-submit').submit(async function (e) {
        e.preventDefault();
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).text('Sending...');
        const formData = {
            name: $(this).find('input[name="name"]').val(),
            phone: $(this).find('input[name="phone"]').val(),
            message: $(this).find('textarea[name="message"]').val()
        };
        try {
            const res = await api.sendMessage(formData);
            if (res.success) {
                alert('Message sent!');
                this.reset();
            } else { alert('Error sending message'); }
        } catch (e) { alert('Connection error'); }
        finally { btn.prop('disabled', false).text('Send Message'); }
    });

    // --- APPOINTMENT FORM LOGIC ---
    if ($('#appointment-form').length) {
        // Load doctors for appointment form
        api.getDoctors().then(doctors => {
            const doctorSelect = $('#appointment-form select[name="doctorId"]');
            doctorSelect.empty().append('<option selected disabled>Choose Doctor</option>');
            doctors.forEach(doctor => {
                doctorSelect.append(`<option value="${doctor.id}">${doctor.name} - ${doctor.specialty}</option>`);
            });
        }).catch(error => {
            console.error('Error loading doctors:', error);
        });

        $('#appointment-form').submit(async function (e) {
            e.preventDefault();
            const btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true).text('Booking...');
            const formData = {
                name: $(this).find('input[name="name"]').val(),
                phone: $(this).find('input[name="phone"]').val(),
                date: $(this).find('input[name="date"]').val(),
                doctorId: $(this).find('select[name="doctorId"]').val(),
                healthType: $(this).find('select[name="healthType"]').val()
            };
            try {
                const res = await api.bookAppointment(formData);
                if (res.success) {
                    alert('Appointment booked successfully!');
                    this.reset();
                } else { alert('Error booking appointment: ' + res.message); }
            } catch (e) { alert('Connection error'); }
            finally { btn.prop('disabled', false).text('Appointment Now'); }
        });
    }

    // --- NEWSLETTER LOGIC ---
    $('.newsletter-input').submit(function (e) {
        e.preventDefault();
        const phone = $(this).find('input[type="tel"]').val();
        alert(`We'll contact you on WhatsApp at ${phone}!`);
        this.reset();
    });

    // --- HOME PAGE DOCTORS GRID (FULL) ---
    if ($('#home-doctors-grid').length) {
        const localDoctorImgs = [
            'assets/doctor-fatima.jpg',
            'assets/doctor-male-1.jpg',
            'assets/doctor-male-2.jpg',
            'assets/doctor-female-1.jpg'
        ];
        api.getDoctors().then(doctors => {
            const grid = $('#home-doctors-grid');
            grid.empty();
            if (doctors.length === 0) {
                grid.html('<div class="col-12 text-center py-5"><p>No doctors available at the moment.</p></div>');
                return;
            }
            doctors.slice(0, 3).forEach((doctor, idx) => {
                const fallback = localDoctorImgs[idx % localDoctorImgs.length];
                const stars = parseFloat(doctor.rating || 4.5).toFixed(1);
                const bio = doctor.bio ? doctor.bio.substring(0, 75) + '…' : 'Specialist in their field.';
                const reviews = doctor.reviews_count || '';
                const doctorCard = `
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 text-center p-0">
                            <div style="height:230px; overflow:hidden; background:#eef6ff;">
                                <img src="${doctor.image || fallback}"
                                     alt="${doctor.name}"
                                     style="width:100%; height:100%; object-fit:cover; object-position:top;"
                                     onerror="this.src='${fallback}'">
                            </div>
                            <div class="p-4 d-flex flex-column">
                                <h5 class="fw-bold mb-1" style="color:#0F172A;">${doctor.name}</h5>
                                <p class="text-primary fw-semibold small mb-2">${doctor.specialty}</p>
                                <p class="text-muted small mb-3" style="line-height:1.5;">${bio}</p>
                                <div class="d-flex justify-content-center align-items-center gap-1 mb-3">
                                    <i class="fas fa-star text-warning"></i>
                                    <span class="fw-bold">${stars}</span>
                                    ${reviews ? `<span class="text-muted small">(${reviews} reviews)</span>` : ''}
                                </div>
                                <a href="appointment.html" class="btn btn-primary btn-sm rounded-pill mt-auto">Book Appointment</a>
                            </div>
                        </div>
                    </div>
                `;
                grid.append(doctorCard);
            });
        }).catch(error => {
            console.error('Error loading doctors:', error);
            $('#home-doctors-grid').html('<div class="col-12 text-center py-5"><p>Error loading doctors.</p></div>');
        });
    }

    // Mobile Menu Toggle
    $('#mobile-menu-btn').click(function () {
        $('#mobile-menu').toggle();
    });

    // --- BLOGS (NEW DYNAMIC FETCH) ---
    function loadBlogsHome() {
        const container = $('#blogs-container');
        if (!container.length) return;

        $.ajax({
            url: '../backend/?route=blog/api_list',
            method: 'GET',
            success: function (response) {
                if (response.status === 'success' && response.data && response.data.length > 0) {
                    let html = '';
                    // Show only top 3 for home
                    const blogs = response.data.slice(0, 3);
                    blogs.forEach(blog => {
                        const date = new Date(blog.created_at).toLocaleDateString('en-US', {
                            month: 'short', day: '2-digit', year: '2-digit'
                        });
                        // Enhanced Image Detection with Premium Fallbacks
                        let blogImg = blog.image;
                        const title = (blog.title || '').toLowerCase();
                        
                        // Smart matching to newly generated premium assets
                        if (!blogImg || blogImg.includes('placeholder')) {
                            if (title.includes('blood')) blogImg = 'assets/blood_registry.png';
                            else if (title.includes('psychological') || title.includes('mental')) blogImg = 'assets/mental_health.png';
                            else if (title.includes('booking') || title.includes('appointment')) blogImg = 'assets/smart_booking.png';
                            else blogImg = 'https://placehold.co/600x400/eef6ff/2f7bff?text=MedNova+News';
                        }

                        const fullImageUrl = (blogImg.startsWith('http') || blogImg.startsWith('assets/')) ? blogImg : `../backend/${blogImg}`;

                        html += `
                            <div class="col-md-4">
                                <div class="blog-pro-card h-100">
                                    <div style="overflow: hidden; height: 200px; border-radius: 12px; background: #eef6ff;">
                                        <img src="${fullImageUrl}" 
                                            onerror="this.src='https://placehold.co/600x400/eef6ff/2f7bff?text=Medical+News'"
                                            class="w-100 h-100" style="object-fit: cover;" alt="${blog.title}">
                                    </div>
                                    <div class="blog-body p-3 d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="blog-badge">${blog.category}</span>
                                            <span class="text-muted small">${date}</span>
                                        </div>
                                        <h5 class="blog-title">${blog.title}</h5>
                                        <p class="text-muted small">${blog.excerpt || ''}</p>
                                        <a href="#" class="blog-link mt-auto">Read More &rarr;</a>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    container.html(html);
                } else {
                    container.html('<div class="col-12 text-center text-muted">No news updates available at the moment.</div>');
                }
            },
            error: function () {
                container.html('<div class="col-12 text-center text-danger">Failed to load news.</div>');
            }
        });
    }

    loadBlogsHome();
});
