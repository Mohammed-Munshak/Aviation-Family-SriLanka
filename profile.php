<?php
session_start();
require_once 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); exit;
}

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : $_SESSION['user_id'];
$is_own_profile = ($user_id == $_SESSION['user_id']);

// HANDLE UPDATES
if ($is_own_profile && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $new_bio  = trim($_POST['bio']);
    $new_name = trim($_POST['full_name']);

    if (!empty($_FILES['avatar']['name'])) {
        $target_dir = "assets/uploads/avatars/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $target_file = $target_dir . time() . "_" . basename($_FILES["avatar"]["name"]);
        if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("UPDATE users SET bio = ?, avatar = ?, full_name = ? WHERE id = ?");
            $stmt->bind_param("sssi", $new_bio, $target_file, $new_name, $user_id);
            $stmt->execute();
        }
    } else {
        $stmt = $conn->prepare("UPDATE users SET bio = ?, full_name = ? WHERE id = ?");
        $stmt->bind_param("ssi", $new_bio, $new_name, $user_id);
        $stmt->execute();
    }
    header("Location: profile.php"); exit;
}

// Fetch User Data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("User not found.");
}

// Calculate Age safely
$age = "N/A";
if (!empty($user['dob'])) {
    $dob_obj   = new DateTime($user['dob']);
    $today_obj = new DateTime();
    $age       = $today_obj->diff($dob_obj)->y;
}

// Fetch Photos using prepared statement
$photo_stmt = $conn->prepare(
    $is_own_profile
        ? "SELECT * FROM spotting_photos WHERE user_id = ? ORDER BY id DESC"
        : "SELECT * FROM spotting_photos WHERE user_id = ? AND status = 'approved' ORDER BY id DESC"
);
$photo_stmt->bind_param("i", $user_id);
$photo_stmt->execute();
$photos     = $photo_stmt->get_result();
$spot_count = $photos->num_rows;

// Prepare card values
$display_name  = htmlspecialchars($user['full_name'] ?: $user['username']);
$card_name_upper = strtoupper($user['full_name'] ?: $user['username']);
$card_handle   = '@' . htmlspecialchars($user['username']);
$card_role     = $user['role'] === 'admin' ? 'Administrator' : 'Official Member';
$card_dob      = !empty($user['dob']) ? date('d M Y', strtotime($user['dob'])) : 'N/A';
$card_since    = date('d M Y', strtotime($user['created_at']));
$card_mid_pad4 = str_pad($user['id'], 4, '0', STR_PAD_LEFT);
$card_mid_pad3 = str_pad($user['id'], 3, '0', STR_PAD_LEFT);
$card_avatar   = !empty($user['avatar']) ? $user['avatar'] : 'assets/images/default-avatar.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Profile - <?php echo htmlspecialchars($user['username']); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        /* ── EXISTING PROFILE CARD ── */
        .profile-header-card {
            background: white; width: 350px; margin: 0 auto 40px auto;
            border-radius: 16px; overflow: hidden; text-align: center;
            padding-bottom: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border: 1px solid #e2e8f0; position: relative;
        }
        .profile-cover {
            height: 100px;
            background: linear-gradient(135deg, var(--primary) 0%, #0f172a 100%);
            position: relative;
        }
        .profile-cover::after {
            content: ''; position: absolute; bottom: 0; left: 0;
            width: 100%; height: 5px; background: var(--accent);
        }
        .profile-avatar {
            width: 120px; height: 120px; border-radius: 50%; border: 4px solid white;
            margin-top: -60px; object-fit: cover; background: #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1); position: relative; z-index: 10;
        }
        .stats-bar {
            display: flex; justify-content: space-around; margin: 20px 15px;
            padding-top: 15px; border-top: 1px dashed #cbd5e1;
        }
        .stat-box { text-align: center; }
        .stat-val { display: block; font-weight: 800; color: var(--primary); font-size: 1.1rem; }
        .stat-label { font-size: 0.7rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        .member-badge {
            margin-top: 5px; font-size: 0.7rem; letter-spacing: 1px;
            background: var(--primary); color: white; font-weight: 700;
            text-transform: uppercase; display: inline-block; padding: 4px 12px; border-radius: 4px;
        }
        .badge-admin { background: #b91c1c; }
        .verified-badge { display: inline-flex; vertical-align: middle; color: #3b82f6; margin-left: 5px; }
        .action-row { display: flex; justify-content: center; align-items: center; gap: 12px; margin-top: 20px; }
        .profile-btn { height: 40px; padding: 0 20px; border-radius: 6px; font-size: 0.9rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; border: none; }
        .btn-dl  { background-color: white; color: #166534; border: 1px solid #166534 !important; }
        .btn-edit { background-color: var(--accent); color: var(--primary); border: 1px solid var(--accent) !important; }

        /* ── GALLERY ── */
        .gallery-compact { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 15px; }
        .mini-card { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05); position: relative; }
        .mini-img  { width: 100%; height: 100px; object-fit: cover; }
        .mini-info { padding: 10px; font-size: 0.8rem; }
        .btn-delete { position: absolute; top: 5px; right: 5px; background: rgba(255,0,0,0.8); color: white; border: none; border-radius: 4px; padding: 4px; cursor: pointer; display: flex; }

        /* ── MODAL ── */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; }
        .modal-content { background: white; padding: 30px; border-radius: 8px; width: 90%; max-width: 400px; }

        
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">

        <!-- ═══════════════════════════════════════════
             EXISTING PROFILE CARD (unchanged)
        ════════════════════════════════════════════ -->
        <div id="id-card-area" class="profile-header-card">
            <div class="profile-cover"></div>
            <img src="<?php echo $card_avatar; ?>" class="profile-avatar" crossorigin="anonymous">

            <h2 style="color:var(--primary);margin:10px 0 5px;font-size:1.5rem;display:flex;align-items:center;justify-content:center;">
                <?php echo $display_name; ?>
                <?php if($user['role'] === 'admin'): ?>
                    <span class="verified-badge" title="Verified Admin">
                        <i data-lucide="badge-check" style="width:20px;height:20px;fill:#3b82f6;color:white;"></i>
                    </span>
                <?php endif; ?>
            </h2>

            <?php if($user['role'] === 'admin'): ?>
                <div class="member-badge badge-admin">Administrator</div>
            <?php else: ?>
                <div class="member-badge">Official Member</div>
            <?php endif; ?>

            <div style="font-size:0.75rem;color:#1e293b;font-weight:bold;margin-top:5px;">
                Age: <?php echo $age; ?> | Aviation Family Sri Lanka
            </div>

            <p style="color:#475569;font-size:0.9rem;padding:0 20px;margin-top:10px;line-height:1.4;">
                <?php echo !empty($user['bio']) ? htmlspecialchars($user['bio']) : "Enthusiast."; ?>
            </p>

            <div class="stats-bar">
                <div class="stat-box">
                    <span class="stat-val"><?php echo $spot_count; ?></span>
                    <span class="stat-label">Spots</span>
                </div>
                <div class="stat-box">
                    <span class="stat-val"><?php echo date("d M Y", strtotime($user['created_at'])); ?></span>
                    <span class="stat-label">Since</span>
                </div>
                <div class="stat-box">
                    <span class="stat-val">#<?php echo $card_mid_pad3; ?></span>
                    <span class="stat-label">ID</span>
                </div>
            </div>

            <?php if ($is_own_profile): ?>
            <div class="action-row" data-html2canvas-ignore="true">
                <button onclick="downloadID()" class="profile-btn btn-dl">
                    <i data-lucide="download" style="width:16px;"></i> Download ID
                </button>
                <button onclick="document.getElementById('editModal').style.display='flex'" class="profile-btn btn-edit">
                    <i data-lucide="edit-2" style="width:16px;"></i> Edit
                </button>
            </div>
            <?php endif; ?>
        </div>

        <!-- ═══════════════════════════════════════════
             SPOTTING LOG
        ════════════════════════════════════════════ -->
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h2 class="page-title" style="margin:0;border:none;font-size:1.4rem;">Spotting Log (<?php echo $spot_count; ?>)</h2>
            <?php if($is_own_profile): ?>
                <a href="upload_photo.php" class="btn-filled" style="padding:6px 15px;font-size:0.8rem;">+ Add Photo</a>
            <?php endif; ?>
        </div>

        <div class="gallery-compact">
            <?php if($photos->num_rows > 0):
                $photos->data_seek(0);
                while($photo = $photos->fetch_assoc()): ?>
                <div class="mini-card">
                    <img src="<?php echo htmlspecialchars($photo['image_path']); ?>" class="mini-img">
                    <?php if($is_own_profile): ?>
                        <a href="delete_photo.php?id=<?php echo $photo['id']; ?>" class="btn-delete" onclick="return confirm('Delete photo?')">
                            <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                        </a>
                    <?php endif; ?>
                    <div class="mini-info">
                        <div style="font-weight:bold;color:var(--primary);"><?php echo htmlspecialchars($photo['airline']); ?></div>
                        <div style="color:#666;font-size:0.75rem;"><?php echo htmlspecialchars($photo['aircraft_model']); ?></div>
                        <div style="color:var(--accent);font-size:0.7rem;font-weight:bold;"><?php echo htmlspecialchars($photo['reg_number']); ?></div>
                        <?php if($photo['status'] == 'pending'): ?>
                            <div style="color:#eab308;font-weight:bold;font-size:0.65rem;margin-top:2px;">REVIEWING</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; else: ?>
                <p style="color:#999;grid-column:1/-1;text-align:center;">No photos logged.</p>
            <?php endif; ?>
        </div>

    </div><!-- /container -->


    <!-- ═══════════════════════════════════════════
         EDIT MODAL
    ════════════════════════════════════════════ -->
    <?php if ($is_own_profile): ?>
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3 style="color:var(--primary);">Update Profile</h3>
            <form method="POST" enctype="multipart/form-data">
                <label style="display:block;margin:10px 0 5px;font-weight:bold;font-size:0.9rem;">Display Name</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>"
                       style="width:100%;border:1px solid #ddd;padding:8px;border-radius:4px;">

                <label style="display:block;margin:10px 0 5px;font-weight:bold;font-size:0.9rem;">Bio</label>
                <textarea name="bio" rows="2" style="width:100%;border:1px solid #ddd;padding:8px;border-radius:4px;"><?php echo htmlspecialchars($user['bio']); ?></textarea>

                <label style="display:block;margin:10px 0 5px;font-weight:bold;font-size:0.9rem;">New Avatar</label>
                <input type="file" name="avatar" style="margin-bottom:20px;">

                <button type="submit" name="update_profile" class="btn-filled" style="width:100%;">Save Changes</button>
                <button type="button" onclick="document.getElementById('editModal').style.display='none'"
                        style="width:100%;border:none;background:white;margin-top:10px;cursor:pointer;color:#666;">Cancel</button>
            </form>
        </div>
    </div>
    <?php endif; ?>


    <script>
        lucide.createIcons();

        function downloadID() {
            const W = 1040, H = 620; // 2x for sharpness (renders at 520x310 equivalent)
            const s = 2;             // scale factor

            const canvas = document.createElement('canvas');
            canvas.width  = W;
            canvas.height = H;
            const c = canvas.getContext('2d');

            // ── HELPER: rounded rect ──
            function roundRect(x, y, w, h, r) {
                c.beginPath();
                c.moveTo(x + r, y);
                c.lineTo(x + w - r, y);
                c.quadraticCurveTo(x + w, y, x + w, y + r);
                c.lineTo(x + w, y + h - r);
                c.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
                c.lineTo(x + r, y + h);
                c.quadraticCurveTo(x, y + h, x, y + h - r);
                c.lineTo(x, y + r);
                c.quadraticCurveTo(x, y, x + r, y);
                c.closePath();
            }

            function drawCard(logoImg, avatarImg) {
                // ── BACKGROUND ──
                const bg = c.createLinearGradient(0, 0, W * 0.6, H);
                bg.addColorStop(0,   '#0b1a30');
                bg.addColorStop(0.4, '#0d2140');
                bg.addColorStop(1,   '#071220');
                roundRect(0, 0, W, H, 44);
                c.fillStyle = bg;
                c.fill();

                // clip everything to card shape
                c.save();
                roundRect(0, 0, W, H, 44);
                c.clip();

                // ── GRID TEXTURE ──
                c.strokeStyle = 'rgba(255,255,255,0.045)';
                c.lineWidth = 1;
                for (let x = 0; x < W; x += 60) { c.beginPath(); c.moveTo(x,0); c.lineTo(x,H); c.stroke(); }
                for (let y = 0; y < H; y += 60) { c.beginPath(); c.moveTo(0,y); c.lineTo(W,y); c.stroke(); }

                // ── SHINE SWEEP ──
                const shine = c.createLinearGradient(0, 0, W, H);
                shine.addColorStop(0,    'rgba(255,255,255,0)');
                shine.addColorStop(0.38, 'rgba(255,255,255,0)');
                shine.addColorStop(0.45, 'rgba(255,220,80,0.06)');
                shine.addColorStop(0.50, 'rgba(255,255,255,0.10)');
                shine.addColorStop(0.55, 'rgba(255,220,80,0.06)');
                shine.addColorStop(0.62, 'rgba(255,255,255,0)');
                shine.addColorStop(1,    'rgba(255,255,255,0)');
                c.fillStyle = shine;
                c.fillRect(0, 0, W, H);

                // ── WATERMARK LOGO (right side, low opacity) ──
                if (logoImg) {
                    c.save();
                    c.globalAlpha = 0.09;
                    const lSize = 380;
                    c.drawImage(logoImg, W - lSize - 20, (H - lSize) / 2, lSize, lSize);
                    c.globalAlpha = 1;
                    c.restore();
                }

                // ── GOLD TOP STRIPE ──
                const gt = c.createLinearGradient(0, 0, W, 0);
                gt.addColorStop(0,    '#5a3c00');
                gt.addColorStop(0.2,  '#c8960c');
                gt.addColorStop(0.5,  '#ffe680');
                gt.addColorStop(0.8,  '#c8960c');
                gt.addColorStop(1,    '#5a3c00');
                c.fillStyle = gt;
                c.fillRect(0, 0, W, 10);

                // ── GOLD BOTTOM STRIPE ──
                const gb = c.createLinearGradient(0, 0, W, 0);
                gb.addColorStop(0,   '#5a3c00');
                gb.addColorStop(0.2, '#c8960c');
                gb.addColorStop(0.5, '#ffe680');
                gb.addColorStop(0.8, '#c8960c');
                gb.addColorStop(1,   '#5a3c00');
                c.fillStyle = gb;
                c.fillRect(0, H - 10, W, 10);

                // ── SMALL LOGO (top-left circle) ──
                if (logoImg) {
                    c.save();
                    c.beginPath();
                    c.arc(52 * s, 42 * s, 19 * s, 0, Math.PI * 2);
                    c.fillStyle = 'white';
                    c.fill();
                    // gold ring
                    c.strokeStyle = 'rgba(255,215,0,0.5)';
                    c.lineWidth = 2 * s;
                    c.stroke();
                    c.clip();
                    c.drawImage(logoImg, 33 * s, 23 * s, 38 * s, 38 * s);
                    c.restore();
                }

                // ── BRAND NAME ──
                c.fillStyle = '#ffd700';
                c.font = `bold ${11 * s}px Arial`;
                c.letterSpacing = '3px';
                c.fillText('AVIATION FAMILY', 82 * s, 38 * s);
                c.fillStyle = 'rgba(255,210,0,0.45)';
                c.font = `${7 * s}px Arial`;
                c.fillText('SRI LANKA', 82 * s, 52 * s);
                c.letterSpacing = '0px';

                // ── ROLE BADGE (top-right) ──
                const roleText = '<?php echo strtoupper($card_role); ?>';
                c.font = `bold ${7 * s}px Arial`;
                const roleW = c.measureText(roleText).width + 28 * s;
                const roleX = W - roleW - 24 * s;
                const roleY = 18 * s;
                const badgeGrad = c.createLinearGradient(roleX, 0, roleX + roleW, 0);
                badgeGrad.addColorStop(0,   '#7a5200');
                badgeGrad.addColorStop(0.4, '#e6ac00');
                badgeGrad.addColorStop(0.6, '#ffd700');
                badgeGrad.addColorStop(1,   '#e6ac00');
                roundRect(roleX, roleY, roleW, 18 * s, 4 * s);
                c.fillStyle = badgeGrad;
                c.fill();
                c.fillStyle = '#040d1a';
                c.fillText(roleText, roleX + 14 * s, roleY + 13 * s);

                // ── GOLD DIVIDER ──
                const divGrad = c.createLinearGradient(0, 0, W, 0);
                divGrad.addColorStop(0,   'rgba(255,215,0,0)');
                divGrad.addColorStop(0.3, 'rgba(255,215,0,0.25)');
                divGrad.addColorStop(0.7, 'rgba(255,215,0,0.25)');
                divGrad.addColorStop(1,   'rgba(255,215,0,0)');
                c.fillStyle = divGrad;
                c.fillRect(0, 70 * s, W, 1 * s);

                // ── AVATAR ──
                const avX = 26 * s, avY = 88 * s, avR = 48 * s;
                // outer glow ring
                c.beginPath();
                c.arc(avX + avR, avY + avR, avR + 6 * s, 0, Math.PI * 2);
                c.strokeStyle = 'rgba(255,215,0,0.15)';
                c.lineWidth = 6 * s;
                c.stroke();
                // gold border
                c.beginPath();
                c.arc(avX + avR, avY + avR, avR + 2.5 * s, 0, Math.PI * 2);
                c.strokeStyle = '#ffd700';
                c.lineWidth = 2.5 * s;
                c.stroke();
                // avatar image clipped to circle
                c.save();
                c.beginPath();
                c.arc(avX + avR, avY + avR, avR, 0, Math.PI * 2);
                c.clip();
                if (avatarImg) {
                    c.drawImage(avatarImg, avX, avY, avR * 2, avR * 2);
                } else {
                    c.fillStyle = '#1a3355';
                    c.fill();
                }
                c.restore();

                // ── MEMBER NAME ──
                const infoX = 140 * s;
                c.fillStyle = '#ffffff';
                c.font = `bold ${17 * s}px Arial`;
                c.fillText('<?php echo addslashes($card_name_upper); ?>', infoX, 102 * s);

                // ── HANDLE ──
                c.fillStyle = '#ffd700';
                c.font = `${9 * s}px Arial`;
                c.fillText('<?php echo addslashes($card_handle); ?>', infoX, 118 * s);

                // ── INFO GRID ──
                const labels = ['DATE OF BIRTH','MEMBER SINCE','TOTAL SPOTS','STATUS'];
                const values = [
                    '<?php echo addslashes($card_dob); ?>',
                    '<?php echo addslashes($card_since); ?>',
                    '<?php echo $spot_count; ?>',
                    'ACTIVE'
                ];
                const valueColors = ['rgba(255,255,255,0.88)','rgba(255,255,255,0.88)','rgba(255,255,255,0.88)','#ffd700'];
                const colW = 130 * s;
                const startY = 136 * s;
                const rowH  = 32 * s;

                for (let i = 0; i < 4; i++) {
                    const col = i % 2;
                    const row = Math.floor(i / 2);
                    const x = infoX + col * colW;
                    const y = startY + row * rowH;

                    c.fillStyle = 'rgba(255,210,0,0.45)';
                    c.font = `${6.5 * s}px Arial`;
                    c.fillText(labels[i], x, y);

                    c.fillStyle = valueColors[i];
                    c.font = i === 3 ? `bold ${10 * s}px Arial` : `${10 * s}px Arial`;
                    c.fillText(values[i], x, y + 14 * s);
                }

                // ── BOTTOM CARD NUMBER ──
                c.fillStyle = 'rgba(255,255,255,0.28)';
                c.font = `${11.5 * s}px "Courier New"`;
                c.fillText('AFSL \u2022\u2022\u2022\u2022 \u2022\u2022\u2022\u2022 <?php echo $card_mid_pad4; ?>', 26 * s, H - 20 * s);

                // ── BOTTOM MEMBER ID ──
                c.fillStyle = 'rgba(255,210,0,0.45)';
                c.font = `${6.5 * s}px Arial`;
                const midLabel = 'MEMBER ID';
                const midLabelW = c.measureText(midLabel).width;
                c.fillText(midLabel, W - midLabelW - 24 * s, H - 34 * s);

                c.fillStyle = '#ffd700';
                c.font = `bold ${14 * s}px Arial`;
                const midVal = '#<?php echo $card_mid_pad3; ?>';
                const midValW = c.measureText(midVal).width;
                c.fillText(midVal, W - midValW - 24 * s, H - 18 * s);

                c.restore(); // end clip

                // ── DOWNLOAD ──
                const filename = 'AviationFamilySL_<?php echo preg_replace('/[^A-Za-z0-9_]/', '_', $user['full_name'] ?: $user['username']); ?>.png';
                const link = document.createElement('a');
                link.download = filename;
                link.href = canvas.toDataURL('image/png');
                link.click();
            }

            // Load logo and avatar images, then draw
            let logoImg   = new Image();
            let avatarImg = new Image();
            logoImg.crossOrigin   = 'anonymous';
            avatarImg.crossOrigin = 'anonymous';

            let loaded = 0;
            function onLoad() {
                loaded++;
                if (loaded >= 2) drawCard(logoImg, avatarImg);
            }
            function onError() {
                loaded++;
                if (loaded >= 2) drawCard(logoImg, avatarImg);
            }

            logoImg.onload   = onLoad;
            logoImg.onerror  = onError;
            avatarImg.onload = onLoad;
            avatarImg.onerror = onError;

            logoImg.src   = 'assets/images/logo.png?v=' + Date.now();
            avatarImg.src = '<?php echo $card_avatar; ?>?v=' + Date.now();
        }
    </script>
</body>
</html>