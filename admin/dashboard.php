<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); exit;
}

$msg = ""; $msg_type = "success";

function uploadFile($fileInputName, $subdir = 'general') {
    $target_dir = "../assets/uploads/" . $subdir . "/";
    if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
    $ext = pathinfo($_FILES[$fileInputName]["name"], PATHINFO_EXTENSION);
    $filename = time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
    $target = $target_dir . $filename;
    if (move_uploaded_file($_FILES[$fileInputName]["tmp_name"], $target)) {
        return "assets/uploads/" . $subdir . "/" . $filename;
    }
    return null;
}

// DELETE HANDLERS
if (isset($_GET['delete_article'])) { $s=$conn->prepare("DELETE FROM articles WHERE id=?"); $s->bind_param("i",$_GET['delete_article']); $s->execute(); header("Location: dashboard.php?msg=Article+deleted&type=success"); exit; }
if (isset($_GET['delete_product'])) { $s=$conn->prepare("DELETE FROM products WHERE id=?"); $s->bind_param("i",$_GET['delete_product']); $s->execute(); header("Location: dashboard.php?msg=Product+deleted&type=success"); exit; }
if (isset($_GET['delete_job']))     { $s=$conn->prepare("DELETE FROM vacancies WHERE id=?"); $s->bind_param("i",$_GET['delete_job']); $s->execute(); header("Location: dashboard.php?msg=Vacancy+deleted&type=success"); exit; }
if (isset($_GET['delete_msg']))     { $s=$conn->prepare("DELETE FROM contact_messages WHERE id=?"); $s->bind_param("i",$_GET['delete_msg']); $s->execute(); header("Location: dashboard.php?msg=Message+deleted&type=success"); exit; }
if (isset($_GET['delete_user']))    { $s=$conn->prepare("DELETE FROM users WHERE id=? AND role!='admin'"); $s->bind_param("i",$_GET['delete_user']); $s->execute(); header("Location: dashboard.php?msg=Member+removed&type=success"); exit; }
if (isset($_GET['delete_app'])) {
    $id = intval($_GET['delete_app']);
    $s=$conn->prepare("SELECT resume_path FROM job_applications WHERE id=?"); $s->bind_param("i",$id); $s->execute();
    $app=$s->get_result()->fetch_assoc();
    if ($app && file_exists("../".$app['resume_path'])) unlink("../".$app['resume_path']);
    $s2=$conn->prepare("DELETE FROM job_applications WHERE id=?"); $s2->bind_param("i",$id); $s2->execute();
    header("Location: dashboard.php?msg=Application+deleted&type=success"); exit;
}

// STATUS HANDLERS
if (isset($_POST['update_photo_status'])) {
    $id=intval($_POST['photo_id']); $st=$_POST['status'];
    if (in_array($st,['approved','rejected','pending'])) { $s=$conn->prepare("UPDATE spotting_photos SET status=? WHERE id=?"); $s->bind_param("si",$st,$id); $s->execute(); $msg="Photo status updated."; }
}
if (isset($_POST['update_order_status'])) {
    $id=intval($_POST['order_id']); $st=$_POST['status'];
    if (in_array($st,['pending','completed','cancelled'])) { $s=$conn->prepare("UPDATE orders SET status=? WHERE id=?"); $s->bind_param("si",$st,$id); $s->execute(); $msg="Order marked as ".ucfirst($st)."."; }
}
if (isset($_POST['update_app_status'])) {
    $id=intval($_POST['app_id']); $st=$_POST['app_status'];
    if (in_array($st,['pending','reviewed','shortlisted','rejected'])) { $s=$conn->prepare("UPDATE job_applications SET status=? WHERE id=?"); $s->bind_param("si",$st,$id); $s->execute(); $msg="Application status updated."; }
}

// ADD HANDLERS
if (isset($_POST['add_article'])) {
    $img=uploadFile('article_image','articles');
    if ($img) { $s=$conn->prepare("INSERT INTO articles (title,category,image_url,summary,content,author_id) VALUES (?,?,?,?,?,?)"); $s->bind_param("sssssi",$_POST['title'],$_POST['category'],$img,$_POST['summary'],$_POST['content'],$_SESSION['user_id']); $s->execute(); $msg="Article published successfully."; }
    else { $msg="Image upload failed."; $msg_type="error"; }
}
if (isset($_POST['add_product'])) {
    $img=uploadFile('product_image','products');
    if ($img) { $s=$conn->prepare("INSERT INTO products (name,category,price,description,image_path,stock_status) VALUES (?,?,?,?,?,?)"); $s->bind_param("ssdsss",$_POST['name'],$_POST['category'],$_POST['price'],$_POST['description'],$img,$_POST['stock_status']); $s->execute(); $msg="Product added successfully."; }
    else { $msg="Image upload failed."; $msg_type="error"; }
}
if (isset($_POST['add_job'])) {
    $s=$conn->prepare("INSERT INTO vacancies (job_title,company,description,closing_date,apply_link) VALUES (?,?,?,?,?)");
    $s->bind_param("sssss",$_POST['title'],$_POST['company'],$_POST['description'],$_POST['closing_date'],$_POST['link']);
    $s->execute(); $msg="Vacancy posted successfully.";
}
if (isset($_POST['save_settings'])) {
    foreach (['fb_followers','ig_followers','yt_subscribers','wa_members'] as $key) {
        if (isset($_POST[$key])) { $val=(string)intval($_POST[$key]); $s=$conn->prepare("INSERT INTO settings (`key`,`value`) VALUES (?,?) ON DUPLICATE KEY UPDATE `value`=?"); $s->bind_param("sss",$key,$val,$val); $s->execute(); }
    }
    $msg="Settings saved successfully.";
}

// COUNTS
$total_members   = $conn->query("SELECT COUNT(*) as c FROM users WHERE role!='admin'")->fetch_assoc()['c'];
$pending_orders  = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status='pending'")->fetch_assoc()['c'];
$pending_photos  = $conn->query("SELECT COUNT(*) as c FROM spotting_photos WHERE status='pending'")->fetch_assoc()['c'];
$unread_msgs     = $conn->query("SELECT COUNT(*) as c FROM contact_messages WHERE is_read=0")->fetch_assoc()['c'];
$total_articles  = $conn->query("SELECT COUNT(*) as c FROM articles")->fetch_assoc()['c'];
$total_products  = $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'];
$total_vacancies = $conn->query("SELECT COUNT(*) as c FROM vacancies")->fetch_assoc()['c'];

$settings=[]; $res=$conn->query("SELECT `key`,`value` FROM settings");
if ($res) while ($row=$res->fetch_assoc()) $settings[$row['key']]=$row['value'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard — Aviation Family SL</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest"></script>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--navy:#002147;--navy2:#001533;--gold:#FFC107;--gold2:#e6a800;--red:#ef4444;--green:#22c55e;--bg:#f1f5f9;--white:#fff;--border:#e2e8f0;--text:#1e293b;--muted:#64748b}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh}

/* SIDEBAR */
.sidebar{width:240px;background:var(--navy2);min-height:100vh;position:fixed;top:0;left:0;display:flex;flex-direction:column;z-index:100;transition:transform .3s}
.sb-brand{padding:20px;border-bottom:1px solid rgba(255,255,255,.07);display:flex;align-items:center;gap:10px}
.sb-brand img{width:34px;height:34px;border-radius:50%;background:white;padding:2px;object-fit:contain}
.sb-brand-main{color:white;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.5px}
.sb-brand-sub{color:var(--gold);font-size:9px;letter-spacing:2px;text-transform:uppercase}
.sb-nav{flex:1;padding:12px 0;overflow-y:auto}
.sb-label{font-size:9px;letter-spacing:2.5px;text-transform:uppercase;color:rgba(255,255,255,.22);padding:14px 18px 5px}
.sb-item{display:flex;align-items:center;gap:10px;padding:10px 18px;color:rgba(255,255,255,.58);cursor:pointer;font-size:13px;font-weight:500;border:none;background:none;width:100%;text-align:left;transition:.2s;text-decoration:none;border-right:3px solid transparent}
.sb-item:hover{background:rgba(255,255,255,.05);color:white}
.sb-item.active{background:rgba(255,193,7,.1);color:var(--gold);border-right-color:var(--gold)}
.sb-icon{width:15px;height:15px;flex-shrink:0}
.sb-badge{margin-left:auto;background:var(--red);color:white;font-size:10px;font-weight:700;padding:1px 6px;border-radius:10px}
.sb-footer{padding:16px 18px;border-top:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.15)}
.sb-footer a{display:flex;align-items:center;gap:9px;color:rgba(255,255,255,.55);font-size:12.5px;text-decoration:none;padding:9px 12px;transition:.25s;border-radius:7px;font-weight:500}
.sb-footer a:hover{color:white;background:rgba(255,255,255,.08)}
.sb-footer a.sb-logout:hover{color:#ef4444;background:rgba(239,68,68,.1)}

/* MAIN */
.main{margin-left:240px;flex:1;display:flex;flex-direction:column;min-height:100vh}
.topbar{background:var(--white);border-bottom:1px solid var(--border);padding:0 26px;height:58px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50}
.topbar-title{font-size:15px;font-weight:700;color:var(--navy)}
.tb-admin{display:flex;align-items:center;gap:10px;font-size:13px;font-weight:600;color:var(--navy)}
.tb-avatar{width:30px;height:30px;background:var(--navy);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--gold);font-weight:700;font-size:12px}
.tb-actions{display:flex;align-items:center;gap:8px;margin-left:14px;padding-left:14px;border-left:1px solid var(--border)}
.tb-btn{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;transition:all .25s;border:1px solid var(--border);cursor:pointer}
.tb-btn-home{background:#f0f7ff;color:var(--navy)}
.tb-btn-home:hover{background:var(--navy);color:white;border-color:var(--navy);transform:translateY(-1px);box-shadow:0 3px 10px rgba(0,33,71,.2)}
.tb-btn-logout{background:#fef2f2;color:#dc2626}
.tb-btn-logout:hover{background:#dc2626;color:white;border-color:#dc2626;transform:translateY(-1px);box-shadow:0 3px 10px rgba(220,38,38,.25)}
.content{padding:26px;flex:1}

/* TABS */
.tab-panel{display:none}.tab-panel.active{display:block}

/* FLASH */
.flash{padding:11px 16px;border-radius:8px;margin-bottom:22px;font-size:13px;font-weight:500;display:flex;align-items:center;gap:9px}
.flash.success{background:#f0fdf4;color:#166534;border:1px solid #bbf7d0}
.flash.error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}

/* STAT GRID */
.stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;margin-bottom:24px}
.stat-card{background:var(--white);border-radius:10px;padding:18px;border:1px solid var(--border);transition:box-shadow .2s}
.stat-card:hover{box-shadow:0 4px 14px rgba(0,33,71,.08)}
.stat-accent{width:28px;height:3px;border-radius:2px;background:var(--gold);margin-bottom:8px}
.stat-num{font-size:1.9rem;font-weight:800;color:var(--navy);line-height:1}
.stat-lbl{font-size:10px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:var(--muted);margin-top:4px}

/* SECTION */
.sec-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}
.sec-top h2{font-size:17px;font-weight:700;color:var(--navy)}

/* CARD */
.card{background:var(--white);border-radius:10px;border:1px solid var(--border);overflow:hidden;margin-bottom:18px}
.card-hd{padding:14px 18px;border-bottom:1px solid var(--border);font-size:13.5px;font-weight:700;color:var(--navy);display:flex;align-items:center;gap:7px}
.card-bd{padding:18px}

/* 2-COL */
.two-col{display:grid;grid-template-columns:340px 1fr;gap:20px;align-items:start}

/* FORM */
.fg{margin-bottom:13px}
.fl{display:block;font-size:11px;font-weight:600;color:var(--muted);margin-bottom:4px;text-transform:uppercase;letter-spacing:.4px}
.fc{width:100%;padding:8px 11px;border:1px solid var(--border);border-radius:6px;font-size:13px;font-family:'Inter',sans-serif;color:var(--text);background:var(--white);transition:.2s}
.fc:focus{outline:none;border-color:var(--navy);box-shadow:0 0 0 3px rgba(0,33,71,.07)}
textarea.fc{resize:vertical;min-height:85px}

/* TABLE */
.tw{overflow-x:auto}
.dt{width:100%;border-collapse:collapse;font-size:12.5px}
.dt th{background:#f8fafc;padding:10px 13px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--muted);border-bottom:1px solid var(--border);white-space:nowrap}
.dt td{padding:11px 13px;border-bottom:1px solid #f1f5f9;vertical-align:middle}
.dt tr:last-child td{border-bottom:none}
.dt tbody tr:hover{background:#fafbfc}

/* BADGES */
.badge{display:inline-flex;align-items:center;padding:2px 8px;border-radius:20px;font-size:10.5px;font-weight:600;white-space:nowrap}
.badge-pending{background:#fef3c7;color:#92400e}
.badge-completed{background:#dcfce7;color:#166534}
.badge-cancelled{background:#fee2e2;color:#991b1b}
.badge-approved{background:#dcfce7;color:#166534}
.badge-rejected{background:#fee2e2;color:#991b1b}
.badge-reviewed{background:#dbeafe;color:#1e40af}
.badge-shortlisted{background:#f3e8ff;color:#6b21a8}
.badge-news{background:#dbeafe;color:#1e40af}
.badge-knowledge{background:#f0fdf4;color:#166534}
.badge-like{background:#dcfce7;color:#166534}
.badge-dislike{background:#fee2e2;color:#991b1b}
.badge-in{background:#dcfce7;color:#166534}
.badge-out{background:#fee2e2;color:#991b1b}

/* BUTTONS */
.btn{display:inline-flex;align-items:center;gap:5px;padding:7px 16px;border-radius:6px;font-size:12.5px;font-weight:600;border:none;cursor:pointer;text-decoration:none;transition:.2s;white-space:nowrap}
.btn:hover{opacity:.87;transform:translateY(-1px)}
.btn-navy{background:var(--navy);color:white}
.btn-gold{background:var(--gold);color:var(--navy)}
.btn-red{background:var(--red);color:white}
.btn-green{background:var(--green);color:white}
.btn-sm{padding:4px 10px;font-size:11px;border-radius:5px}
.btn-full{width:100%;justify-content:center}

/* PHOTO GRID */
.photo-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:16px}
.photo-card{background:var(--white);border-radius:10px;overflow:hidden;border:1px solid var(--border)}
.photo-card img{width:100%;height:160px;object-fit:cover;display:block}
.photo-bd{padding:12px}
.photo-meta{font-size:11.5px;color:var(--muted);margin-bottom:3px}
.photo-meta strong{color:var(--text)}
.photo-actions{display:flex;gap:7px;margin-top:10px}

/* MSG */
.msg-card{background:var(--white);border:1px solid var(--border);border-left:4px solid var(--gold);border-radius:8px;padding:14px 16px;margin-bottom:10px}

/* SETTINGS */
.sg{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:14px}
.si{background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:18px}
.pl{font-size:11.5px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;margin-bottom:9px}
.pl.fb{color:#1877F2}.pl.ig{color:#E1306C}.pl.yt{color:#FF0000}.pl.wa{color:#25D366}

/* EMPTY */
.empty{text-align:center;padding:44px 20px;color:var(--muted)}
.empty p{font-size:13.5px;margin-top:8px}

/* MOBILE */
#mob-toggle{display:none}
@media(max-width:900px){
    .sidebar{transform:translateX(-100%)}.sidebar.open{transform:translateX(0)}
    .main{margin-left:0}.two-col{grid-template-columns:1fr}
    #mob-toggle{display:flex!important}
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="sb-brand">
        <img src="../assets/images/logo.png" alt="Logo" onerror="this.style.display='none'">
        <div><div class="sb-brand-main">Aviation Family</div><div class="sb-brand-sub">Admin Panel</div></div>
    </div>
    <nav class="sb-nav">
        <div class="sb-label">Overview</div>
        <button class="sb-item active" onclick="openTab('overview',this)"><i data-lucide="layout-dashboard" class="sb-icon"></i>Dashboard</button>
        <div class="sb-label">Manage</div>
        <button class="sb-item" onclick="openTab('orders',this)"><i data-lucide="shopping-bag" class="sb-icon"></i>Orders<?php if($pending_orders>0):?><span class="sb-badge"><?php echo $pending_orders;?></span><?php endif;?></button>
        <button class="sb-item" onclick="openTab('photos',this)"><i data-lucide="camera" class="sb-icon"></i>Photos<?php if($pending_photos>0):?><span class="sb-badge"><?php echo $pending_photos;?></span><?php endif;?></button>
        <button class="sb-item" onclick="openTab('members',this)"><i data-lucide="users" class="sb-icon"></i>Members</button>
        <button class="sb-item" onclick="openTab('apps',this)"><i data-lucide="file-text" class="sb-icon"></i>Applications</button>
        <button class="sb-item" onclick="openTab('messages',this)"><i data-lucide="mail" class="sb-icon"></i>Messages<?php if($unread_msgs>0):?><span class="sb-badge"><?php echo $unread_msgs;?></span><?php endif;?></button>
        <div class="sb-label">Content</div>
        <button class="sb-item" onclick="openTab('news',this)"><i data-lucide="newspaper" class="sb-icon"></i>News & Knowledge</button>
        <button class="sb-item" onclick="openTab('store',this)"><i data-lucide="package" class="sb-icon"></i>Store</button>
        <button class="sb-item" onclick="openTab('careers',this)"><i data-lucide="briefcase" class="sb-icon"></i>Careers</button>
        <div class="sb-label">System</div>
        <button class="sb-item" onclick="openTab('settings',this)"><i data-lucide="settings" class="sb-icon"></i>Settings</button>
    </nav>
    <div class="sb-footer">
        <a href="../index.php"><i data-lucide="home" style="width:15px;height:15px;"></i> Back to Website</a>
        <a href="../logout.php" class="sb-logout"><i data-lucide="log-out" style="width:15px;height:15px;"></i> Logout</a>
    </div>
</aside>

<!-- MAIN -->
<div class="main">
    <div class="topbar">
        <div style="display:flex;align-items:center;gap:12px;">
            <button id="mob-toggle" class="btn btn-sm" onclick="document.getElementById('sidebar').classList.toggle('open')" style="border:1px solid var(--border);background:white;color:var(--muted);">
                <i data-lucide="menu" style="width:14px;height:14px;"></i>
            </button>
            <span class="topbar-title" id="page-title">Dashboard</span>
        </div>
        <div class="tb-admin">
            <div class="tb-avatar">A</div>
            <?php echo htmlspecialchars($_SESSION['username']??'Admin'); ?>
            <div class="tb-actions">
                <a href="../index.php" class="tb-btn tb-btn-home">&#8592; Home</a>
                <a href="../logout.php" class="tb-btn tb-btn-logout">Logout &#x2192;</a>
            </div>
        </div>
    </div>

    <div class="content">

        <!-- FLASH -->
        <?php
        $fm = $msg ?: (isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : '');
        $ft = $msg_type ?: (isset($_GET['type']) ? $_GET['type'] : 'success');
        if ($fm): ?>
        <div class="flash <?php echo $ft;?>">
            <i data-lucide="<?php echo $ft==='success'?'check-circle':'alert-circle';?>" style="width:15px;height:15px;"></i>
            <?php echo $fm;?>
        </div>
        <?php endif; ?>

        <!-- ═══ OVERVIEW ═══ -->
        <div id="overview" class="tab-panel active">
            <div class="stat-grid">
                <div class="stat-card"><div class="stat-accent"></div><div class="stat-num"><?php echo $total_members;?></div><div class="stat-lbl">Members</div></div>
                <div class="stat-card"><div class="stat-accent" style="background:#ef4444;"></div><div class="stat-num"><?php echo $pending_orders;?></div><div class="stat-lbl">Pending Orders</div></div>
                <div class="stat-card"><div class="stat-accent" style="background:#3b82f6;"></div><div class="stat-num"><?php echo $pending_photos;?></div><div class="stat-lbl">Photos Pending</div></div>
                <div class="stat-card"><div class="stat-accent" style="background:#8b5cf6;"></div><div class="stat-num"><?php echo $unread_msgs;?></div><div class="stat-lbl">Unread Messages</div></div>
                <div class="stat-card"><div class="stat-accent" style="background:#f59e0b;"></div><div class="stat-num"><?php echo $total_articles;?></div><div class="stat-lbl">Articles</div></div>
                <div class="stat-card"><div class="stat-accent" style="background:#10b981;"></div><div class="stat-num"><?php echo $total_products;?></div><div class="stat-lbl">Products</div></div>
                <div class="stat-card"><div class="stat-accent" style="background:#06b6d4;"></div><div class="stat-num"><?php echo $total_vacancies;?></div><div class="stat-lbl">Vacancies</div></div>
            </div>
            <div class="card">
                <div class="card-hd"><i data-lucide="shopping-bag" style="width:15px;height:15px;"></i>Recent Orders</div>
                <div class="tw"><table class="dt">
                    <thead><tr><th>Order</th><th>Member</th><th>Product</th><th>Qty</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                    <?php $rc=$conn->query("SELECT o.id,o.status,o.quantity,o.created_at,u.username,p.name pname FROM orders o JOIN users u ON o.user_id=u.id JOIN products p ON o.product_id=p.id ORDER BY o.created_at DESC LIMIT 6");
                    if($rc&&$rc->num_rows>0): while($r=$rc->fetch_assoc()):?>
                    <tr>
                        <td><strong>#<?php echo $r['id'];?></strong></td>
                        <td><?php echo htmlspecialchars($r['username']);?></td>
                        <td><?php echo htmlspecialchars($r['pname']);?></td>
                        <td><?php echo $r['quantity'];?></td>
                        <td><span class="badge badge-<?php echo $r['status'];?>"><?php echo ucfirst($r['status']);?></span></td>
                        <td><?php echo date('d M Y',strtotime($r['created_at']));?></td>
                    </tr>
                    <?php endwhile; else:?><tr><td colspan="6"><div class="empty"><p>No orders yet.</p></div></td></tr><?php endif;?>
                    </tbody>
                </table></div>
            </div>
        </div>

        <!-- ═══ ORDERS ═══ -->
        <div id="orders" class="tab-panel">
            <div class="sec-top"><h2>Customer Orders</h2></div>
            <div class="card"><div class="tw"><table class="dt">
                <thead><tr><th>#</th><th>Member</th><th>WhatsApp</th><th>Product</th><th>Qty</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
                <tbody>
                <?php $or=$conn->query("SELECT o.id oid,o.status,o.quantity,o.created_at,u.username,u.whatsapp_number,p.name pname FROM orders o JOIN users u ON o.user_id=u.id JOIN products p ON o.product_id=p.id ORDER BY o.created_at DESC");
                if($or&&$or->num_rows>0): while($ord=$or->fetch_assoc()):?>
                <tr>
                    <td><strong>#<?php echo $ord['oid'];?></strong></td>
                    <td><?php echo htmlspecialchars($ord['username']);?></td>
                    <td><a href="https://wa.me/<?php echo $ord['whatsapp_number'];?>" target="_blank" style="color:#25D366;font-size:11.5px;"><?php echo htmlspecialchars($ord['whatsapp_number']);?></a></td>
                    <td><?php echo htmlspecialchars($ord['pname']);?></td>
                    <td><?php echo $ord['quantity'];?></td>
                    <td><span class="badge badge-<?php echo $ord['status'];?>"><?php echo ucfirst($ord['status']);?></span></td>
                    <td><?php echo date('d M Y',strtotime($ord['created_at']));?></td>
                    <td><?php if($ord['status']==='pending'):?>
                        <form method="POST" style="display:flex;gap:4px;">
                            <input type="hidden" name="order_id" value="<?php echo $ord['oid'];?>">
                            <input type="hidden" name="update_order_status" value="1">
                            <button name="status" value="completed" class="btn btn-green btn-sm" onclick="return confirm('Mark completed?')">Complete</button>
                            <button name="status" value="cancelled" class="btn btn-red btn-sm" onclick="return confirm('Cancel?')">Cancel</button>
                        </form>
                    <?php endif;?></td>
                </tr>
                <?php endwhile; else:?><tr><td colspan="8"><div class="empty"><p>No orders yet.</p></div></td></tr><?php endif;?>
                </tbody>
            </table></div></div>
        </div>

        <!-- ═══ PHOTOS ═══ -->
        <div id="photos" class="tab-panel">
            <div class="sec-top"><h2>Photo Approvals</h2></div>
            <?php $pend=$conn->query("SELECT s.*,u.username,u.full_name FROM spotting_photos s JOIN users u ON s.user_id=u.id WHERE s.status='pending' ORDER BY s.id DESC");
            if($pend&&$pend->num_rows>0):?>
            <div class="photo-grid">
                <?php while($p=$pend->fetch_assoc()):?>
                <div class="photo-card">
                    <img src="../<?php echo htmlspecialchars($p['image_path']);?>" alt="Photo">
                    <div class="photo-bd">
                        <div class="photo-meta"><strong>By:</strong> <?php echo htmlspecialchars($p['username']);?> <?php echo $p['full_name']?'('.htmlspecialchars($p['full_name']).')':'';?></div>
                        <div class="photo-meta"><strong>Airline:</strong> <?php echo htmlspecialchars($p['airline']);?></div>
                        <div class="photo-meta"><strong>Aircraft:</strong> <?php echo htmlspecialchars($p['aircraft_model']);?></div>
                        <div class="photo-meta"><strong>Reg:</strong> <?php echo htmlspecialchars($p['reg_number']);?></div>
                        <div class="photo-meta"><strong>Location:</strong> <?php echo htmlspecialchars($p['location']);?></div>
                        <div class="photo-actions">
                            <form method="POST" style="display:flex;gap:7px;width:100%;">
                                <input type="hidden" name="photo_id" value="<?php echo $p['id'];?>">
                                <input type="hidden" name="update_photo_status" value="1">
                                <button name="status" value="approved" class="btn btn-green btn-sm" style="flex:1;">Approve</button>
                                <button name="status" value="rejected" class="btn btn-red btn-sm" style="flex:1;">Reject</button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endwhile;?>
            </div>
            <?php else:?><div class="card"><div class="empty"><i data-lucide="check-circle" style="width:34px;height:34px;color:var(--green);"></i><p>No pending photos — all caught up!</p></div></div><?php endif;?>
        </div>

        <!-- ═══ MEMBERS ═══ -->
        <div id="members" class="tab-panel">
            <div class="sec-top"><h2>Members (<?php echo $total_members;?>)</h2></div>
            <div class="card"><div class="tw"><table class="dt">
                <thead><tr><th>ID</th><th>Full Name</th><th>Username</th><th>Email</th><th>WhatsApp</th><th>Joined</th><th>Action</th></tr></thead>
                <tbody>
                <?php $ul=$conn->query("SELECT * FROM users WHERE role!='admin' ORDER BY id DESC"); while($u=$ul->fetch_assoc()):?>
                <tr>
                    <td><strong>#<?php echo str_pad($u['id'],3,'0',STR_PAD_LEFT);?></strong></td>
                    <td><?php echo htmlspecialchars($u['full_name']?:'—');?></td>
                    <td><?php echo htmlspecialchars($u['username']);?></td>
                    <td><?php echo htmlspecialchars($u['email']);?></td>
                    <td><?php echo htmlspecialchars($u['whatsapp_number']?:'—');?></td>
                    <td><?php echo date('d M Y',strtotime($u['created_at']));?></td>
                    <td><a href="?delete_user=<?php echo $u['id'];?>" class="btn btn-red btn-sm" onclick="return confirm('Remove member permanently?')">Remove</a></td>
                </tr>
                <?php endwhile;?>
                </tbody>
            </table></div></div>
        </div>

        <!-- ═══ APPLICATIONS ═══ -->
        <div id="apps" class="tab-panel">
            <div class="sec-top"><h2>Job Applications</h2></div>
            <div class="card"><div class="tw"><table class="dt">
                <thead><tr><th>Applicant</th><th>Position</th><th>Experience</th><th>Statement</th><th>Resume</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                <?php $apps=$conn->query("SELECT a.*,v.job_title FROM job_applications a JOIN vacancies v ON a.vacancy_id=v.id ORDER BY a.applied_at DESC");
                if($apps&&$apps->num_rows>0): while($row=$apps->fetch_assoc()):?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($row['full_name']);?></strong><br><span style="font-size:11px;color:var(--muted);"><?php echo htmlspecialchars($row['email']);?></span><br><span style="font-size:11px;color:var(--muted);"><?php echo htmlspecialchars($row['phone']);?></span></td>
                    <td><?php echo htmlspecialchars($row['job_title']);?></td>
                    <td><?php echo htmlspecialchars($row['experience_years']);?></td>
                    <td style="max-width:190px;font-size:11.5px;color:var(--muted);"><em>"<?php echo htmlspecialchars(substr($row['why_aviation'],0,100));?>..."</em></td>
                    <td><a href="../<?php echo htmlspecialchars($row['resume_path']);?>" class="btn btn-navy btn-sm" download>Download</a></td>
                    <td><span class="badge badge-<?php echo $row['status'];?>"><?php echo ucfirst($row['status']);?></span></td>
                    <td style="min-width:160px;">
                        <form method="POST" style="display:flex;flex-direction:column;gap:5px;">
                            <input type="hidden" name="app_id" value="<?php echo $row['id'];?>">
                            <input type="hidden" name="update_app_status" value="1">
                            <div style="display:flex;gap:4px;">
                                <select name="app_status" class="fc" style="padding:4px 6px;font-size:11px;height:auto;">
                                    <option value="pending" <?php if($row['status']=='pending') echo 'selected';?>>Pending</option>
                                    <option value="reviewed" <?php if($row['status']=='reviewed') echo 'selected';?>>Reviewed</option>
                                    <option value="shortlisted" <?php if($row['status']=='shortlisted') echo 'selected';?>>Shortlisted</option>
                                    <option value="rejected" <?php if($row['status']=='rejected') echo 'selected';?>>Rejected</option>
                                </select>
                                <button type="submit" class="btn btn-navy btn-sm">Save</button>
                            </div>
                        </form>
                        <a href="?delete_app=<?php echo $row['id'];?>" class="btn btn-red btn-sm" onclick="return confirm('Delete?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; else:?><tr><td colspan="7"><div class="empty"><p>No applications yet.</p></div></td></tr><?php endif;?>
                </tbody>
            </table></div></div>
        </div>

        <!-- ═══ MESSAGES ═══ -->
        <div id="messages" class="tab-panel">
            <div class="sec-top"><h2>Contact Inbox</h2></div>
            <?php $msgs=$conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC"); $conn->query("UPDATE contact_messages SET is_read=1");
            if($msgs&&$msgs->num_rows>0): while($m=$msgs->fetch_assoc()):?>
            <div class="msg-card">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:7px;">
                    <strong style="color:var(--navy);font-size:13.5px;"><?php echo htmlspecialchars($m['subject']);?></strong>
                    <span style="font-size:11px;color:var(--muted);"><?php echo date('d M Y, H:i',strtotime($m['created_at']));?></span>
                </div>
                <div style="font-size:11.5px;color:var(--muted);margin-bottom:9px;">From: <strong style="color:var(--text);"><?php echo htmlspecialchars($m['name']);?></strong> &nbsp;·&nbsp; <?php echo htmlspecialchars($m['email']);?></div>
                <p style="font-size:12.5px;background:#f8fafc;padding:11px;border-radius:6px;line-height:1.6;"><?php echo nl2br(htmlspecialchars($m['message']));?></p>
                <div style="margin-top:9px;"><a href="?delete_msg=<?php echo $m['id'];?>" class="btn btn-red btn-sm" onclick="return confirm('Delete?')">Delete</a></div>
            </div>
            <?php endwhile; else:?><div class="card"><div class="empty"><i data-lucide="mail-open" style="width:34px;height:34px;color:var(--muted);"></i><p>No messages yet.</p></div></div><?php endif;?>
        </div>

        <!-- ═══ NEWS ═══ -->
        <div id="news" class="tab-panel">
            <div class="sec-top"><h2>News & Knowledge</h2></div>
            <div class="two-col">
                <div class="card">
                    <div class="card-hd"><i data-lucide="plus-circle" style="width:14px;height:14px;"></i>Post Article</div>
                    <div class="card-bd">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="fg"><label class="fl">Title</label><input type="text" name="title" class="fc" placeholder="Article title" required></div>
                            <div class="fg"><label class="fl">Category</label><select name="category" class="fc"><option value="news">News</option><option value="knowledge">Knowledge</option></select></div>
                            <div class="fg"><label class="fl">Cover Image</label><input type="file" name="article_image" class="fc" accept="image/*" required></div>
                            <div class="fg"><label class="fl">Summary</label><input type="text" name="summary" class="fc" placeholder="Short description" required></div>
                            <div class="fg"><label class="fl">Content</label><textarea name="content" class="fc" placeholder="Full content..." required></textarea></div>
                            <button name="add_article" class="btn btn-navy btn-full">Publish Article</button>
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="card-hd"><i data-lucide="list" style="width:14px;height:14px;"></i>All Articles (<?php echo $total_articles;?>)</div>
                    <div class="tw"><table class="dt">
                        <thead><tr><th>ID</th><th>Title</th><th>Category</th><th>Reactions</th><th>Action</th></tr></thead>
                        <tbody>
                        <?php $arts=$conn->query("SELECT a.*,(SELECT COUNT(*) FROM article_interactions WHERE article_id=a.id AND type='like') likes,(SELECT COUNT(*) FROM article_interactions WHERE article_id=a.id AND type='dislike') dislikes FROM articles a ORDER BY a.id DESC");
                        while($a=$arts->fetch_assoc()):?>
                        <tr>
                            <td><strong>#<?php echo $a['id'];?></strong></td>
                            <td style="max-width:180px;"><?php echo htmlspecialchars(substr($a['title'],0,32));?>...</td>
                            <td><span class="badge badge-<?php echo $a['category'];?>"><?php echo ucfirst($a['category']);?></span></td>
                            <td><span class="badge badge-like">&#128077; <?php echo $a['likes'];?></span> <span class="badge badge-dislike">&#128078; <?php echo $a['dislikes'];?></span></td>
                            <td><a href="?delete_article=<?php echo $a['id'];?>" class="btn btn-red btn-sm" onclick="return confirm('Delete?')">Delete</a></td>
                        </tr>
                        <?php endwhile;?>
                        </tbody>
                    </table></div>
                </div>
            </div>
        </div>

        <!-- ═══ STORE ═══ -->
        <div id="store" class="tab-panel">
            <div class="sec-top"><h2>Store Management</h2></div>
            <div class="two-col">
                <div class="card">
                    <div class="card-hd"><i data-lucide="plus-circle" style="width:14px;height:14px;"></i>Add Product</div>
                    <div class="card-bd">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="fg"><label class="fl">Name</label><input type="text" name="name" class="fc" placeholder="Product name" required></div>
                            <div class="fg"><label class="fl">Category</label><select name="category" class="fc"><option value="Model Planes">Model Planes</option><option value="Throttles">Throttles</option><option value="Games">Games</option><option value="Key Tags">Key Tags</option><option value="Accessories">Accessories</option></select></div>
                            <div class="fg"><label class="fl">Price (LKR)</label><input type="number" name="price" class="fc" placeholder="0.00" step="0.01" required></div>
                            <div class="fg"><label class="fl">Description</label><textarea name="description" class="fc" placeholder="Description..."></textarea></div>
                            <div class="fg"><label class="fl">Stock Status</label><select name="stock_status" class="fc"><option value="In Stock">In Stock</option><option value="Out of Stock">Out of Stock</option></select></div>
                            <div class="fg"><label class="fl">Image</label><input type="file" name="product_image" class="fc" accept="image/*" required></div>
                            <button name="add_product" class="btn btn-navy btn-full">Add Product</button>
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="card-hd"><i data-lucide="package" style="width:14px;height:14px;"></i>Inventory (<?php echo $total_products;?>)</div>
                    <div class="tw"><table class="dt">
                        <thead><tr><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Action</th></tr></thead>
                        <tbody>
                        <?php $prods=$conn->query("SELECT * FROM products ORDER BY id DESC"); while($pr=$prods->fetch_assoc()):?>
                        <tr>
                            <td><?php echo htmlspecialchars($pr['name']);?></td>
                            <td style="font-size:11px;color:var(--muted);"><?php echo htmlspecialchars($pr['category']);?></td>
                            <td>LKR <?php echo number_format($pr['price'],2);?></td>
                            <td><span class="badge <?php echo $pr['stock_status']==='In Stock'?'badge-approved':'badge-rejected';?>"><?php echo $pr['stock_status'];?></span></td>
                            <td><a href="?delete_product=<?php echo $pr['id'];?>" class="btn btn-red btn-sm" onclick="return confirm('Delete?')">Delete</a></td>
                        </tr>
                        <?php endwhile;?>
                        </tbody>
                    </table></div>
                </div>
            </div>
        </div>

        <!-- ═══ CAREERS ═══ -->
        <div id="careers" class="tab-panel">
            <div class="sec-top"><h2>Careers Management</h2></div>
            <div class="two-col">
                <div class="card">
                    <div class="card-hd"><i data-lucide="plus-circle" style="width:14px;height:14px;"></i>Post Vacancy</div>
                    <div class="card-bd">
                        <form method="POST">
                            <div class="fg"><label class="fl">Job Title</label><input type="text" name="title" class="fc" placeholder="e.g. Cabin Crew" required></div>
                            <div class="fg"><label class="fl">Company</label><input type="text" name="company" class="fc" placeholder="e.g. SriLankan Airlines" required></div>
                            <div class="fg"><label class="fl">Description</label><textarea name="description" class="fc" placeholder="Job details..." required></textarea></div>
                            <div class="fg"><label class="fl">Closing Date</label><input type="date" name="closing_date" class="fc"></div>
                            <div class="fg"><label class="fl">Apply Link</label><input type="text" name="link" class="fc" placeholder="https://..." required></div>
                            <button name="add_job" class="btn btn-navy btn-full">Post Vacancy</button>
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="card-hd"><i data-lucide="briefcase" style="width:14px;height:14px;"></i>Active Vacancies (<?php echo $total_vacancies;?>)</div>
                    <div class="tw"><table class="dt">
                        <thead><tr><th>Title</th><th>Company</th><th>Closing</th><th>Action</th></tr></thead>
                        <tbody>
                        <?php $jobs=$conn->query("SELECT * FROM vacancies ORDER BY id DESC"); while($j=$jobs->fetch_assoc()):?>
                        <tr>
                            <td><?php echo htmlspecialchars($j['job_title']);?></td>
                            <td><?php echo htmlspecialchars($j['company']);?></td>
                            <td><?php echo $j['closing_date']?date('d M Y',strtotime($j['closing_date'])):'—';?></td>
                            <td><a href="?delete_job=<?php echo $j['id'];?>" class="btn btn-red btn-sm" onclick="return confirm('Delete?')">Delete</a></td>
                        </tr>
                        <?php endwhile;?>
                        </tbody>
                    </table></div>
                </div>
            </div>
        </div>

        <!-- ═══ SETTINGS ═══ -->
        <div id="settings" class="tab-panel">
            <div class="sec-top"><h2>Site Settings</h2></div>
            <div class="card">
                <div class="card-hd"><i data-lucide="bar-chart-2" style="width:14px;height:14px;"></i>Social Media Follower Counts</div>
                <div class="card-bd">
                    <p style="font-size:12.5px;color:var(--muted);margin-bottom:18px;">Update your social counts displayed on the homepage stats bar.</p>
                    <form method="POST">
                        <div class="sg">
                            <div class="si"><div class="pl fb">Facebook</div><div class="fg" style="margin:0;"><label class="fl">Followers</label><input type="number" name="fb_followers" class="fc" value="<?php echo $settings['fb_followers']??0;?>" min="0"></div></div>
                            <div class="si"><div class="pl ig">Instagram</div><div class="fg" style="margin:0;"><label class="fl">Followers</label><input type="number" name="ig_followers" class="fc" value="<?php echo $settings['ig_followers']??0;?>" min="0"></div></div>
                            <div class="si"><div class="pl yt">YouTube</div><div class="fg" style="margin:0;"><label class="fl">Subscribers</label><input type="number" name="yt_subscribers" class="fc" value="<?php echo $settings['yt_subscribers']??0;?>" min="0"></div></div>
                            <div class="si"><div class="pl wa">WhatsApp</div><div class="fg" style="margin:0;"><label class="fl">Members</label><input type="number" name="wa_members" class="fc" value="<?php echo $settings['wa_members']??0;?>" min="0"></div></div>
                        </div>
                        <div style="margin-top:18px;"><button name="save_settings" class="btn btn-gold">Save Settings</button></div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
lucide.createIcons();
function openTab(name, el) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.sb-item').forEach(b => b.classList.remove('active'));
    document.getElementById(name).classList.add('active');
    if (el) { el.classList.add('active'); document.getElementById('page-title').textContent = el.textContent.trim(); }
}
if (window.innerWidth <= 900) document.getElementById('mob-toggle').style.display = 'flex';
</script>
</body>
</html>