<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar d-flex flex-column p-3 text-white shadow-lg" style="width: 260px; height: 100vh; position: sticky; top: 0;">
    
    <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['it', 'admin'])): ?>
        <a href="<?php echo BASE_URL; ?>index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none hover-opacity">
            <div class="brand-logo-container me-2">
                <img src="<?php echo BASE_URL; ?>assets/images/logo-hatyai.png" alt="Hospital Logo" style="width: 45px; height: 45px; object-fit: contain;">
            </div>
            <span class="fs-5 fw-bold tracking-wide">IT Helpdesk</span>
        </a>
    <?php else: ?>
        <div class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white user-select-none">
            <div class="brand-logo-container me-2">
                <img src="<?php echo BASE_URL; ?>assets/images/logo-hatyai.png" alt="Hospital Logo" style="width: 45px; height: 45px; object-fit: contain;">
            </div>
            <span class="fs-5 fw-bold tracking-wide">IT Helpdesk</span>
        </div>
    <?php endif; ?>
    
    <hr class="border-secondary opacity-25 mt-0">
    
    <ul class="nav nav-pills flex-column mb-auto"> 
        
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'staff'): ?>
            <li class="nav-item mt-2 mb-1 px-3">
                <small class="text-secondary fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">สำหรับผู้ใช้งาน</small>
            </li>
            <li class="nav-item mb-1">
                <a href="<?php echo BASE_URL; ?>modules/tickets/create.php" class="nav-link text-white d-flex align-items-center gap-3 px-3 py-2 <?php echo ($current_page == 'create.php') ? 'active shadow-sm' : ''; ?>">
                    <i class="bi bi-plus-circle fs-5" style="width: 24px; text-align: center;"></i> 
                    <span class="text-nowrap">แจ้งซ่อมใหม่</span>
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="<?php echo BASE_URL; ?>modules/tickets/view.php" class="nav-link text-white d-flex align-items-center gap-3 px-3 py-2 <?php echo ($current_page == 'view.php') ? 'active shadow-sm' : ''; ?>">
                    <i class="bi bi-clock-history fs-5" style="width: 24px; text-align: center;"></i> 
                    <span class="text-nowrap">ประวัติแจ้งซ่อมของฉัน</span>
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="<?php echo BASE_URL; ?>modules/knowledge_base/articles.php" class="nav-link d-flex align-items-center px-3 py-2 text-white">
                    <i class="bi bi-book-half me-3 icon-fixed"></i> <span>คู่มือแก้ปัญหาเบื้องต้น</span>
                </a>
            </li>
            <li class="nav-item mb-1 px-3 mt-2">
                <small class="text-secondary fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">ระบบหลังบ้าน</small>
            </li>
            <li class="nav-item mb-1">
                <a href="<?php echo BASE_URL; ?>modules/tickets/my_profile.php" class="nav-link text-white d-flex align-items-center gap-3 px-3 py-2 <?php echo ($current_page == 'my_profile.php') ? 'active shadow-sm' : ''; ?>">
                    <i class="bi bi-person-badge fs-5" style="width: 24px; text-align: center;"></i> 
                    <span class="text-nowrap">ข้อมูลส่วนตัวและสถิติ</span>
                </a>
            </li>

        <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'it'): ?>
            <li class="nav-item mt-2 mb-1 px-3">
                <small class="text-secondary fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">ศูนย์บัญชาการ</small>
            </li>
            <li class="nav-item mb-1">
                <a href="<?php echo BASE_URL; ?>it_support/dashboard.php" class="nav-link text-white d-flex align-items-center gap-3 px-3 py-2 <?php echo ($current_page == 'dashboard.php') ? 'active shadow-sm' : ''; ?>">
                    <i class="bi bi-speedometer2 fs-5" style="width: 24px; text-align: center;"></i> 
                    <span class="text-nowrap">แดชบอร์ดภาพรวม</span>
                </a>
            </li>
            
            <li class="nav-item mb-1">
                <a href="<?php echo BASE_URL; ?>it_support/kanban.php" class="nav-link text-white d-flex align-items-center gap-3 px-3 py-2 <?php echo ($current_page == 'kanban.php') ? 'active shadow-sm' : ''; ?>">
                    <i class="bi bi-kanban fs-5" style="width: 24px; text-align: center;"></i> 
                    <span class="text-nowrap">บอร์ดจัดการงาน</span>
                </a>
            </li>

            <li class="nav-item mb-1">
                <a href="<?php echo BASE_URL; ?>modules/tickets/manage.php" class="nav-link text-white d-flex align-items-center gap-3 px-3 py-2 <?php echo ($current_page == 'manage.php') ? 'active shadow-sm' : ''; ?>">
                    <i class="bi bi-list-task fs-5" style="width: 24px; text-align: center;"></i> 
                    <span class="text-nowrap">รายการงานแบบตาราง</span>
                </a>
            </li>

            
            
            <li class="nav-item my-2">
                <hr class="border-secondary opacity-25 m-0">
            </li>
            <li class="nav-item mb-1">
                <a href="<?php echo BASE_URL; ?>modules/knowledge_base/manage_articles.php" class="nav-link d-flex align-items-center px-3 py-2 text-white">
                    <i class="bi bi-journal-richtext me-3 icon-fixed"></i> <span>เขียนคู่มือ (CMS)</span>
                </a>
            </li>
            
            <li class="nav-item mb-1 px-3 mt-2">
                <small class="text-secondary fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">ระบบหลังบ้าน</small>
            </li>
            <li class="nav-item mb-1">
                <a href="<?php echo BASE_URL; ?>modules/reports/monthly_report.php" class="nav-link text-white d-flex align-items-center gap-3 px-3 py-2 <?php echo ($current_page == 'manage.php') ? 'active shadow-sm' : ''; ?>">
                    <i class="bi bi-graph-up-arrow text-primary fs-5" style="width: 24px; text-align: center;"></i> 
                    <span class="text-nowrap">รายงานประจำเดือน</span>
                </a>
            </li>
        <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <li class="nav-item my-2">
                <hr class="border-secondary opacity-25 m-0">
            </li>
            
            <li class="nav-item mb-1 px-3 mt-2">
                <small class="text-warning fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;"><i class="bi bi-shield-lock-fill me-1"></i> ผู้ดูแลระบบสูงสุด</small>
            </li>
            <li class="nav-item mb-1">
                <a href="<?php echo BASE_URL; ?>modules/admin/manage_users.php" class="nav-link text-white d-flex align-items-center gap-3 px-3 py-2 <?php echo ($current_page == 'manage_users.php') ? 'active shadow-sm' : ''; ?>">
                    <i class="bi bi-people-fill text-warning fs-5" style="width: 24px; text-align: center;"></i> 
                    <span class="text-nowrap">จัดการผู้ใช้งานระบบ</span>
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="<?php echo BASE_URL; ?>modules/admin/system_settings.php" class="nav-link text-white d-flex align-items-center gap-3 px-3 py-2">
                    <i class="bi bi-gear-fill text-warning fs-5" style="width: 24px; text-align: center;"></i> 
                    <span class="text-nowrap">ตั้งค่าระบบเบื้องต้น</span>
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="<?php echo BASE_URL; ?>modules/knowledge_base/manage_articles.php" class="nav-link d-flex align-items-center px-3 py-2 text-white">
                    <i class="bi bi-journal-richtext me-3 icon-fixed"></i> <span>เขียนคู่มือ (CMS)</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>modules/inventory/item_dashboard.php" class="nav-link text-white d-flex align-items-center gap-3 px-3 py-2">
                    <i class="bi bi-box-seam me-2"></i>
                    <span>แดชบอร์ดอะไหล่</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>modules/inventory/list_assets.php" class="nav-link text-white d-flex align-items-center gap-3 px-3 py-2">
                    <i class="bi bi-list-ul me-2"></i>
                    <span>รายการทรัพย์สินทั้งหมด</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>
    
    <hr class="border-secondary opacity-25 mt-auto">
    
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle px-2 py-2 rounded hover-bg-dark" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['full_name']); ?>&background=0D6EFD&color=fff" alt="" width="35" height="35" class="rounded-circle me-2 shadow-sm border border-secondary">
            <strong><?php echo $_SESSION['username']; ?></strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser">
            <li><span class="dropdown-item text-muted small"><i class="bi bi-building me-2"></i> แผนก: <?php echo $_SESSION['dept_id']; ?></span></li>
            <li><hr class="dropdown-divider border-secondary opacity-25"></li>
            <li><a class="dropdown-item text-danger fw-bold" href="<?php echo BASE_URL; ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i> ออกจากระบบ</a></li>
        </ul>
    </div>
</div>

<div class="main-content flex-grow-1 p-4 overflow-auto bg-light">

<style>
/* CSS ล็อคความสวยงาม */
.sidebar .nav-link {
    transition: all 0.2s ease-in-out;
    border-radius: 8px; 
    font-weight: 500;
}

.sidebar .nav-link:hover:not(.active) {
    background-color: rgba(255, 255, 255, 0.08);
    transform: translateX(3px);
}


.brand-logo-container {
    background-color: white; /* ใส่พื้นหลังขาวให้โลโก้เด่นขึ้น */
    padding: 5px;
    border-radius: 8px; /* ทำมุมโค้งมน */
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.hover-opacity:hover .brand-logo-container {
    transform: scale(1.05); /* เวลาชี้แล้วโลโก้ขยายขึ้นนิดนึงดูมีมิติ */
    transition: 0.3s;
}

.hover-opacity { transition: opacity 0.2s ease-in-out; }
.hover-opacity:hover { opacity: 0.8; }
.hover-bg-dark { transition: background 0.2s; }
.hover-bg-dark:hover { background-color: rgba(255, 255, 255, 0.1); }
</style>