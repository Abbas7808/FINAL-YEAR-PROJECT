            </div> <!-- /.page-content -->
        </div> <!-- /.main-content -->
    </div> <!-- /.dashboard-container -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js?v=1780419041"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Real-time notification polling -->
    <script>
    (function() {
        const badge     = document.getElementById('notifBadge');
        const listEl    = document.getElementById('notifList');
        const summaryEl = document.getElementById('notifSummary');
        let knownCount  = parseInt(badge ? badge.textContent : '0') || 0;
        let lastPollTs  = '<?= $_SESSION['notif_read_at'] ?? date('Y-m-d H:i:s') ?>';

        function playTone() {
            try {
                const ctx  = new (window.AudioContext || window.webkitAudioContext)();
                const osc  = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain); gain.connect(ctx.destination);
                osc.type = 'sine';
                osc.frequency.setValueAtTime(880, ctx.currentTime);
                osc.frequency.exponentialRampToValueAtTime(440, ctx.currentTime + 0.3);
                gain.gain.setValueAtTime(0.35, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.5);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + 0.5);
            } catch(e) {}
        }

        function poll() {
            fetch('?route=hospital_admin/poll_notifications&since=' + encodeURIComponent(lastPollTs))
                .then(r => r.json())
                .then(data => {
                    lastPollTs = data.server_time || lastPollTs;
                    const total = data.unread_total || 0;

                    if (badge) {
                        if (total > 0) { badge.textContent = total; badge.style.display = ''; }
                        else { badge.style.display = 'none'; }
                    }
                    if (summaryEl) summaryEl.textContent = total > 0 ? total + ' new' : 'All read';

                    if (data.new_count > 0 && total > knownCount) {
                        playTone();
                        if (listEl && data.items && data.items.length > 0) {
                            // Remove "no activity" placeholder if present
                            const empty = listEl.querySelector('.fa-check-circle');
                            if (empty) listEl.innerHTML = '<div class="px-4 pt-2 pb-1 notif-section-label">New</div>';
                            data.items.forEach(item => {
                                const wrap = document.createElement('a');
                                wrap.href = item.link || '#';
                                wrap.className = 'text-decoration-none';
                                wrap.innerHTML = `
                                    <div class="notif-item d-flex align-items-start gap-3 px-4 py-3 border-bottom notif-new-flash">
                                        <div class="notif-icon-wrap notif-icon-${item.color}"><i class="${item.icon}"></i></div>
                                        <div style="flex:1;min-width:0;">
                                            <div class="small fw-semibold text-reset" style="line-height:1.3;">${item.message}</div>
                                            <div class="text-muted" style="font-size:11px;">Just now</div>
                                        </div>
                                        <span class="notif-unread-dot"></span>
                                    </div>`;
                                listEl.prepend(wrap);
                            });
                        }
                    }
                    knownCount = total;
                })
                .catch(() => {});
        }

        // Mark as read when bell is opened
        const notifBtn = document.getElementById('notifBtn');
        if (notifBtn) {
            notifBtn.addEventListener('shown.bs.dropdown', function() {
                setTimeout(() => {
                    fetch('?route=hospital_admin/mark_notifications_read', { method: 'POST' });
                    knownCount = 0;
                    if (badge) badge.style.display = 'none';
                    if (summaryEl) summaryEl.textContent = 'All read';
                }, 500);
            });
        }

        // Poll every 30 seconds
        setInterval(poll, 30000);
    })();
    </script>
    <script>
        // Toast Config
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        <?php if(isset($_SESSION['success'])): ?>
            Toast.fire({
                icon: 'success',
                title: '<?= $_SESSION['success'] ?>'
            });
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if(isset($_SESSION['error'])): ?>
            Toast.fire({
                icon: 'error',
                title: '<?= $_SESSION['error'] ?>'
            });
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </script>
</body>
</html>
