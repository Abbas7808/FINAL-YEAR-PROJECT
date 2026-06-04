<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' — Mid-Nova' : 'Mid-Nova Admin' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=1780419041">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="assets/js/theme.js?v=1780419041"></script>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'views/layouts/sidebar.php'; ?>
        <div class="main-content">

            <!-- ====== TOP HEADER ====== -->
            <?php
                $notifs  = $notifications ?? [];
                $unread  = [];
                $read    = [];
                foreach ($notifs as $n) {
                    // Use database is_read field primarily, but only for notifications with ids
                    $isRead = isset($n['id']) ? (($n['is_read'] ?? 0) == 1) : true; // Blood notifications are always "read"
                    if (!$isRead) {
                        $unread[] = $n;
                    } else {
                        $read[] = $n;
                    }
                }
                $unreadCount = count($unread);
            ?>
            <div class="top-header">
                <!-- Mobile Menu Toggle -->
                <button class="btn btn-link text-reset d-md-none me-3" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="page-title">
                    <?= isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard' ?>
                </div>

                <!-- Universal Search Bar -->
                <div class="flex-grow-1 mx-4 d-none d-md-block" style="max-width: 500px;">
                    <?php 
                        $searchRoute = $_SESSION['role'] === 'admin' ? 'admin/search' : 'hospital_admin/search';
                    ?>
                    <form action="?route=<?= $searchRoute ?>" method="GET" class="position-relative">
                        <input type="hidden" name="route" value="<?= $searchRoute ?>">
                        <input type="text" name="q" class="form-control border-0 bg-light rounded-pill px-4" 
                               placeholder="Search..."
                               value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                               style="height: 42px; font-size: 14px;">
                        <button type="submit" class="position-absolute end-0 top-0 h-100 border-0 bg-transparent px-3 text-muted">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>

                <div class="header-actions">

                    <!-- Bell Dropdown -->
                    <div class="dropdown">
                        <button class="icon-btn position-relative" id="notifBtn"
                                type="button"
                                data-bs-toggle="dropdown"
                                data-bs-auto-close="outside"
                                aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            <span class="notif-badge" id="notifBadge"
                                  style="<?= $unreadCount > 0 ? '' : 'display:none;' ?>">
                                <?= $unreadCount ?>
                            </span>
                        </button>

                        <div class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 p-0"
                             id="notifDropdownMenu"
                             style="min-width:340px; max-height:500px; overflow-y:auto;">

                            <div class="px-4 py-3 border-bottom d-flex align-items-center justify-content-between"
                                 style="position:sticky;top:0;background:var(--bg-card);z-index:1;">
                                <span class="fw-bold">Notifications</span>
                                <span class="small text-muted" id="notifSummary">
                                    <?= $unreadCount > 0 ? $unreadCount . ' new' : 'All read' ?>
                                </span>
                            </div>

                            <div id="notifList">
                            <?php if (empty($notifs)): ?>
                                <div class="text-muted small text-center py-5 px-3">
                                    <i class="fas fa-check-circle text-success d-block mb-2" style="font-size:26px;"></i>
                                    No recent activity.
                                </div>
                            <?php else: 
                                // Show unread first, then read
                                $displayNotifs = array_merge($unread, $read);
                                $hasMore = count($notifs) > 10;
                            ?>
                                <?php foreach ($displayNotifs as $n): 
                                    $isUnread = in_array($n, $unread);
                                    $href = isset($n['id']) ? "?route=hospital_admin/read_notification&id=" . $n['id'] : ($n['link'] ?? '#');
                                ?>
                                <a href="<?= $href ?>" class="text-decoration-none">
                                    <div class="notif-item d-flex align-items-start gap-3 px-4 py-3 border-bottom <?= $isUnread ? '' : 'opacity-75' ?>">
                                        <div class="notif-icon-wrap notif-icon-<?= $n['color'] ?> <?= $isUnread ? '' : 'opacity-75' ?>">
                                            <i class="<?= $n['icon'] ?>"></i>
                                        </div>
                                        <div style="flex:1;min-width:0;">
                                            <div class="small <?= $isUnread ? 'fw-semibold' : 'fw-normal' ?> text-reset" style="line-height:1.3;"><?= htmlspecialchars($n['message']) ?></div>
                                            <div class="text-muted" style="font-size:11px;"><?= date('M d, g:i a', strtotime($n['time'])) ?></div>
                                        </div>
                                        <?php if ($isUnread): ?>
                                            <span class="notif-unread-dot"></span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                                <?php endforeach; ?>

                                <!-- See All Link -->
                                <div class="p-2 text-center border-top">
                                    <a href="?route=hospital_admin/notifications" class="text-primary small fw-semibold text-decoration-none py-1 d-block">
                                        View All Notifications
                                        <i class="fas fa-chevron-right ms-1" style="font-size: 10px;"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                            </div><!-- /notifList -->
                        </div>
                    </div>

                    <!-- User Pill -->
                    <div class="dropdown">
                        <button class="user-pill" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar">
                                <?= strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1)) ?>
                            </div>
                            <div class="user-info-text">
                                <div class="user-name"><?= htmlspecialchars($_SESSION['name'] ?? 'User') ?></div>
                                <div class="user-role"><?= ucfirst(str_replace('_', ' ', $_SESSION['role'] ?? 'Admin')) ?></div>
                            </div>
                            <i class="fas fa-chevron-down ms-2 text-muted" style="font-size:11px;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow rounded-4 mt-2">
                            <li class="px-3 py-2 border-bottom">
                                <div class="fw-bold"><?= htmlspecialchars($_SESSION['name'] ?? 'User') ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($_SESSION['email'] ?? '') ?></div>
                            </li>
                            <li>
                                <a class="dropdown-item rounded-3 my-1" href="?route=hospital_admin/settings">
                                    <i class="fas fa-cog me-2 text-muted"></i> Settings
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item rounded-3 my-1 text-danger" href="?route=auth/logout">
                                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
            <!-- END TOP HEADER -->

            <div class="page-content">
