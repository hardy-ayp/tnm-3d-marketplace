<?php
/**
 * TNM Dashboard — Manajemen Catatan & Blog (Artikel)
 * Terintegrasi dalam Unified Admin Dashboard TNM
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';

if (!file_exists(__DIR__ . '/../../blog_config.php')) {
    $adminTitle = 'Catatan & Blog';
    include __DIR__ . '/includes/header.php';
    echo '<div style="padding:48px 24px; text-align:center; max-width:600px; margin:40px auto; background:var(--surface); border:1px solid var(--ring); border-radius:var(--radius-md);">
        <h2 style="font-family:var(--font-display); font-size:22px; margin-bottom:12px; color:var(--ink);">Modul Blog Terpisah</h2>
        <p style="color:var(--muted); font-size:14px; line-height:1.6; margin-bottom:24px;">Modul Catatan & Blog adalah komponen portal utama TNM dan dinonaktifkan dalam mode repositori mandiri (standalone) 3D Apparel Marketplace.</p>
        <a href="index.php" class="btn red sm">Kembali ke Dashboard</a>
    </div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

require_once __DIR__ . '/../../blog_config.php';

$adminTitle = 'Catatan & Blog';
$db = getBlogDB();
$message = '';
$error = '';

$action = $_GET['action'] ?? 'list';
$editId = intval($_GET['edit'] ?? 0);

// Helper URL untuk asset cover di admin
function blogAsset($path) {
    if (empty($path)) return '../../assets/img/blog-dibalik-layar.svg';
    if (preg_match('~^https?://~i', $path)) return $path;
    return '../../' . ltrim($path, '/');
}

// Handle Delete Article
if (isset($_GET['delete'])) {
    if (!verifyMarketplaceCsrfToken($_GET['csrf_token'] ?? '')) {
        $error = 'Validasi token keamanan CSRF gagal saat menghapus catatan.';
    } else {
        $delId = intval($_GET['delete']);
        try {
            if (deletePost($delId)) {
                header('Location: articles.php?msg=deleted');
                exit;
            } else {
                $error = 'Gagal menghapus catatan.';
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Handle Form Submission (Add or Edit)
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!verifyMarketplaceCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Sesi keamanan formulir (CSRF) kedaluwarsa. Silakan muat ulang halaman.';
    } else {
        $postId   = intval($_POST['post_id'] ?? 0);
        $title    = trim($_POST['title'] ?? '');
        $slug     = trim($_POST['slug'] ?? '');
        $category = trim($_POST['category'] ?? 'Catatan Pribadi');
        $author   = trim($_POST['author'] ?? 'Andri TNM');
        $excerpt  = trim($_POST['excerpt'] ?? '');
        $content  = trim($_POST['content'] ?? '');
        $status    = in_array($_POST['status'] ?? '', ['published', 'draft']) ? $_POST['status'] : 'published';
        $cover     = trim($_POST['cover_image'] ?? 'assets/img/blog-dibalik-layar.svg');
        $coverMeta = trim($_POST['cover_meta'] ?? '');

    // Handle Canvas Generated Thumbnail (Base64 PNG from Studio)
    if (!empty($_POST['cover_canvas_data']) && str_starts_with($_POST['cover_canvas_data'], 'data:image/png;base64,')) {
        try {
            $base64Data = substr($_POST['cover_canvas_data'], strlen('data:image/png;base64,'));
            $decodedImg = base64_decode($base64Data);
            if ($decodedImg !== false) {
                if (!file_exists(BLOG_UPLOADS_DIR)) {
                    mkdir(BLOG_UPLOADS_DIR, 0755, true);
                }
                $baseSlug = !empty($slug) ? slugify($slug) : (!empty($title) ? slugify($title) : 'thumb');
                $canvasFileName = 'cover-' . $baseSlug . '-' . time() . '.png';
                $canvasFilePath = BLOG_UPLOADS_DIR . '/' . $canvasFileName;
                if (file_put_contents($canvasFilePath, $decodedImg)) {
                    $cover = 'uploads/blog/' . $canvasFileName;
                }
            }
        } catch (Exception $e) {
            // Keep previous cover if decoding error occurs
        }
    }

    // Handle Upload Cover File (Explicit file upload overrides)
    if (!empty($_FILES['cover_file']['name']) && $_FILES['cover_file']['error'] === UPLOAD_ERR_OK) {
        try {
            $uploaded = handleImageUpload('cover_file');
            if ($uploaded) {
                $cover = $uploaded;
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }

    if (empty($title)) {
        $error = 'Judul artikel wajib diisi.';
    } elseif (empty($content)) {
        $error = 'Isi konten artikel wajib diisi.';
    } elseif (empty($error)) {
        $readTime = calculateReadTime($content);
        $payload = [
            'title'       => $title,
            'slug'        => $slug,
            'category'    => $category,
            'author'      => $author,
            'excerpt'     => $excerpt,
            'content'     => $content,
            'cover_image' => $cover,
            'cover_meta'  => $coverMeta,
            'read_time'   => $readTime,
            'status'      => $status
        ];

        try {
            if ($postId > 0) {
                updatePost($postId, $payload);
                header('Location: articles.php?msg=updated');
                exit;
            } else {
                createPost($payload);
                header('Location: articles.php?msg=created');
                exit;
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
}

// Fetch single article for editing
$editPost = null;
if ($action === 'edit' && $editId > 0) {
    $editPost = getPostById($editId);
    if (!$editPost) {
        $action = 'list';
        $error = 'Artikel tidak ditemukan.';
    }
}

// Notification messages
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'created') $message = 'Artikel catatan baru berhasil diterbitkan!';
    if ($_GET['msg'] === 'updated') $message = 'Perubahan artikel catatan berhasil disimpan!';
    if ($_GET['msg'] === 'deleted') $message = 'Artikel catatan berhasil dihapus.';
}

// Filter & Fetch Posts for list
$categoryFilter = $_GET['cat'] ?? 'all';
$searchTerm = trim($_GET['q'] ?? '');
$allPosts = getAllPosts($categoryFilter, $searchTerm, true);

// Categories list
$categories = [
    'Catatan Pribadi',
    '#CeritaSerem',
    '#MalamMinggu',
    '#Perkara',
    'Editorial'
];

include __DIR__ . '/includes/header.php';
?>

<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap: 16px; margin-bottom: 24px;">
  <div>
    <h1 style="font-family: var(--font-display); font-size: 26px; font-weight: 800; color: var(--ink); margin: 0 0 4px;">
      <?= $action === 'create' ? 'Tulis Catatan / Artikel Baru' : ($action === 'edit' ? 'Sunting Artikel' : 'Kelola Catatan & Blog') ?>
    </h1>
    <p style="color: var(--muted); font-size: 13.5px; margin: 0;">
      <?= $action === 'list' ? 'Daftar seluruh catatan pemikiran, refleksi di balik layar, dan artikel investigasi TNM.' : 'Format teks mendukung markdown tebal, miring, kutipan, dan judul paragraf.' ?>
    </p>
  </div>

  <div style="display:flex; gap: 10px;">
    <?php if ($action === 'list'): ?>
      <a href="articles.php?action=create" class="btn red sm">
        <svg class="ic" viewBox="0 0 256 256" style="width:14px;height:14px;"><path d="M224,128a8,8,0,0,1-8,8H136v80a8,8,0,0,1-16,0V136H40a8,8,0,0,1,0-16h80V40a8,8,0,0,1,16,0v80h80A8,8,0,0,1,224,128Z"></path></svg>
        <span>Tulis Catatan Baru</span>
      </a>
      <a href="../../blog.php" target="_blank" class="btn sm ghost">
        Lihat Blog Live ↗
      </a>
    <?php else: ?>
      <a href="articles.php" class="btn sm ghost">
        ← Kembali ke Daftar
      </a>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($message)): ?>
  <div style="background: rgba(46,204,113,0.12); border: 1px solid rgba(46,204,113,0.3); color: #2ecc71; padding: 12px 18px; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 13.5px;">
     <?= e($message) ?>
  </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div style="background: rgba(231,76,60,0.12); border: 1px solid rgba(231,76,60,0.3); color: var(--red-ink); padding: 12px 18px; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 13.5px;">
     <?= e($error) ?>
  </div>
<?php endif; ?>

<?php if ($action === 'create' || $action === 'edit'): ?>
  <!-- ================= FORM CREATE / EDIT ================= -->
  <form action="articles.php?action=<?= $action ?><?= $editId ? '&edit=' . $editId : '' ?>" method="POST" enctype="multipart/form-data" id="articleMainForm">
    <input type="hidden" name="csrf_token" value="<?= e(getMarketplaceCsrfToken()) ?>">
    <input type="hidden" name="post_id" value="<?= $editPost['id'] ?? 0 ?>">
    <input type="hidden" name="cover_image" id="coverImagePath" value="<?= e($editPost['cover_image'] ?? 'assets/img/blog-dibalik-layar.svg') ?>">
    <input type="hidden" name="cover_canvas_data" id="coverCanvasData" value="">
    <input type="hidden" name="cover_meta" id="coverMetaJson" value="<?= e($editPost['cover_meta'] ?? '') ?>">

    <style>
      @media (max-width: 920px) {
        .article-form-layout {
          grid-template-columns: 1fr !important;
          gap: 16px !important;
        }
      }
    </style>
    <div class="article-form-layout" style="display:grid; grid-template-columns: 2fr 1fr; gap: 24px;">
      
      <!-- Kolom Kiri: Judul, Slug, Excerpt, Konten -->
      <div>
        <div style="background: var(--surface); border: 1px solid var(--ring); border-radius: var(--radius-md); padding: 24px; margin-bottom: 20px;">
          
          <div style="margin-bottom: 18px;">
            <label style="display:block; font-size: 13px; font-weight:700; color:var(--ink); margin-bottom: 6px;">Judul Artikel *</label>
            <input type="text" name="title" id="articleTitle" value="<?= e($editPost['title'] ?? '') ?>" required
                   placeholder="Contoh: Di Balik Layar TNM: Kenapa Jam 2 Pagi Selalu Punya Cerita Berbeda"
                   style="width:100%; background:var(--bg); border:1px solid var(--ring); border-radius:var(--radius-sm); padding:12px 14px; font-size:15px; font-weight:600; color:var(--ink); font-family:inherit; outline:none; box-sizing:border-box;">
          </div>

          <div style="margin-bottom: 18px;">
            <label style="display:block; font-size: 12.5px; font-weight:700; color:var(--ink); margin-bottom: 6px;">
              Slug URL <small style="color:var(--faint); font-weight:400;">(Otomatis dibuat dari judul)</small>
            </label>
            <input type="text" name="slug" id="articleSlug" value="<?= e($editPost['slug'] ?? '') ?>"
                   placeholder="di-balik-layar-tnm-kenapa-jam-2-pagi..."
                   style="width:100%; background:var(--shell); border:1px solid var(--ring); border-radius:var(--radius-sm); padding:9px 12px; font-size:13px; font-family:monospace; color:var(--muted); outline:none; box-sizing:border-box;">
          </div>

          <div style="margin-bottom: 18px;">
            <label style="display:block; font-size: 12.5px; font-weight:700; color:var(--ink); margin-bottom: 6px;">
              Ringkasan Singkat (Excerpt) <small style="color:var(--faint); font-weight:400;">(Ditampilkan di kartu preview beranda)</small>
            </label>
            <textarea name="excerpt" rows="2" placeholder="Tuliskan 1-2 kalimat pengantar yang memikat pembaca..."
                      style="width:100%; background:var(--bg); border:1px solid var(--ring); border-radius:var(--radius-sm); padding:10px 12px; font-size:13.5px; color:var(--ink); font-family:inherit; outline:none; resize:vertical; box-sizing:border-box;"><?= e($editPost['excerpt'] ?? '') ?></textarea>
          </div>

          <!-- Markdown Toolbar & Content -->
          <div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 6px;">
              <label style="font-size: 13px; font-weight:700; color:var(--ink);">Isi Konten Artikel *</label>
              <div style="font-size: 11.5px; color: var(--faint);">Dukungan Markdown Format</div>
            </div>

            <!-- Toolbar Buttons -->
            <div style="display:flex; flex-wrap:wrap; gap: 4px; padding: 6px 10px; background:var(--shell); border:1px solid var(--ring); border-bottom:none; border-radius:var(--radius-sm) var(--radius-sm) 0 0;">
              <button type="button" class="btn sm ghost" onclick="insertSyntax('**', '**')" style="padding:4px 8px; font-size:12px; height:auto;"><b>B</b></button>
              <button type="button" class="btn sm ghost" onclick="insertSyntax('*', '*')" style="padding:4px 8px; font-size:12px; height:auto;"><i>I</i></button>
              <button type="button" class="btn sm ghost" onclick="insertSyntax('## ', '')" style="padding:4px 8px; font-size:12px; height:auto;">H2</button>
              <button type="button" class="btn sm ghost" onclick="insertSyntax('### ', '')" style="padding:4px 8px; font-size:12px; height:auto;">H3</button>
              <button type="button" class="btn sm ghost" onclick="insertSyntax('> ', '')" style="padding:4px 8px; font-size:12px; height:auto;">Quote</button>
              <button type="button" class="btn sm ghost" onclick="insertSyntax('- ', '')" style="padding:4px 8px; font-size:12px; height:auto;">• List</button>
              <button type="button" class="btn sm ghost" onclick="insertSyntax('`', '`')" style="padding:4px 8px; font-size:12px; height:auto;">Code</button>
              <button type="button" class="btn sm ghost" onclick="insertSyntax('[Judul Link](https://', ')')" style="padding:4px 8px; font-size:12px; height:auto;">Link</button>
              <button type="button" class="btn sm ghost" onclick="insertSyntax('---\n', '')" style="padding:4px 8px; font-size:12px; height:auto;">Garis</button>
            </div>

            <textarea name="content" id="articleContent" rows="18" required
                      placeholder="Tuliskan cerita, pemikiran, atau ulasan perkara di sini...&#10;&#10;Gunakan baris ganda untuk memisahkan paragraf baru."
                      style="width:100%; background:var(--bg); border:1px solid var(--ring); border-radius: 0 0 var(--radius-sm) var(--radius-sm); padding:14px; font-size:14px; color:var(--ink); font-family:'Consolas', 'Courier New', monospace; line-height:1.6; outline:none; resize:vertical; box-sizing:border-box;"><?= e($editPost['content'] ?? '') ?></textarea>
          </div>

        </div>
      </div>

      <!-- Kolom Kanan: Pengaturan Penerbitan & Cover -->
      <div>
        <!-- Card: Status & Meta -->
        <div style="background: var(--surface); border: 1px solid var(--ring); border-radius: var(--radius-md); padding: 20px; margin-bottom: 20px;">
          <h3 style="font-size: 14px; font-weight: 800; color: var(--ink); margin: 0 0 14px; border-bottom: 1px solid var(--ring); padding-bottom: 10px;">
            Penerbitan
          </h3>

          <div style="margin-bottom: 14px;">
            <label style="display:block; font-size: 12.5px; font-weight:700; color:var(--ink); margin-bottom: 6px;">Status</label>
            <select name="status" style="width:100%; background:var(--bg); border:1px solid var(--ring); border-radius:var(--radius-sm); padding:10px 12px; font-size:13.5px; color:var(--ink); outline:none;">
              <option value="published" <?= ($editPost['status'] ?? '') === 'published' ? 'selected' : '' ?>>Diterbitkan (Publik)</option>
              <option value="draft" <?= ($editPost['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draf (Disimpan Sementara)</option>
            </select>
          </div>

          <div style="margin-bottom: 14px;">
            <label style="display:block; font-size: 12.5px; font-weight:700; color:var(--ink); margin-bottom: 6px;">Kategori</label>
            <select name="category" style="width:100%; background:var(--bg); border:1px solid var(--ring); border-radius:var(--radius-sm); padding:10px 12px; font-size:13.5px; color:var(--ink); outline:none;">
              <?php foreach ($categories as $cat): ?>
                <option value="<?= e($cat) ?>" <?= ($editPost['category'] ?? '') === $cat ? 'selected' : '' ?>>
                  <?= e($cat) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div style="margin-bottom: 18px;">
            <label style="display:block; font-size: 12.5px; font-weight:700; color:var(--ink); margin-bottom: 6px;">Penulis</label>
            <input type="text" name="author" value="<?= e($editPost['author'] ?? 'Andri TNM') ?>"
                   style="width:100%; background:var(--bg); border:1px solid var(--ring); border-radius:var(--radius-sm); padding:9px 12px; font-size:13.5px; color:var(--ink); outline:none; box-sizing:border-box;">
          </div>

          <button type="submit" class="btn red" style="width:100%; justify-content:center;">
            <span><?= $action === 'edit' ? 'Perbarui Artikel' : 'Terbitkan Artikel' ?></span>
          </button>
        </div>

        <!-- Card: Gambar Sampul (Cover) & Studio Generator -->
        <div style="background: var(--surface); border: 1px solid var(--ring); border-radius: var(--radius-md); padding: 20px;">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 14px; border-bottom: 1px solid var(--ring); padding-bottom: 10px;">
            <h3 style="font-size: 14px; font-weight: 800; color: var(--ink); margin: 0;">
              Gambar Sampul (Cover)
            </h3>
            <span id="customCoverStatusPill" style="font-size: 11px; font-weight: 700; <?= !empty($editPost['cover_meta']) ? 'color:#2ecc71; background:rgba(46,204,113,0.12); border:1px solid rgba(46,204,113,0.3);' : 'color:var(--muted); background:var(--shell); border:1px solid var(--ring);' ?> border-radius: 999px; padding: 2px 8px;">
              <?= !empty($editPost['cover_meta']) ? ' Template Custom' : 'Standar / Preset' ?>
            </span>
          </div>

          <div style="margin-bottom: 14px; text-align:center; position:relative;">
            <div style="position:relative; border-radius:var(--radius-sm); overflow:hidden; border:1px solid var(--ring); background:var(--shell);">
              <img id="coverPreviewImg" src="<?= e(blogAsset($editPost['cover_image'] ?? 'assets/img/blog-dibalik-layar.svg')) ?>" 
                   alt="Preview Cover" style="width:100%; height:140px; object-fit:cover; display:block;">
              <div id="coverTypeOverlay" style="position:absolute; bottom:6px; right:8px; background:rgba(0,0,0,0.7); backdrop-filter:blur(4px); font-size:10px; font-weight:700; color:#ECE8DD; padding:2px 8px; border-radius:6px; border:1px solid rgba(255,255,255,0.15);">
                16:9 Cover
              </div>
            </div>
            <div id="coverNoticeBox" style="display:none; font-size:11.5px; color:#2ecc71; background:rgba(46,204,113,0.1); border:1px solid rgba(46,204,113,0.25); border-radius:6px; padding:6px 10px; margin-top:8px; text-align:left;">
               Thumbnail dari Studio siap disimpan saat artikel diterbitkan.
            </div>
          </div>

          <!-- Studio Trigger Button -->
          <button type="button" class="btn red" id="btnOpenStudio" onclick="openThumbnailStudio()" 
                  style="width:100%; justify-content:center; gap:8px; padding:10px 14px; margin-bottom:14px; font-size:13px; font-weight:700; box-shadow:0 6px 18px rgba(214,59,41,0.25);">
            <svg class="ic" viewBox="0 0 256 256" style="width:16px;height:16px;fill:currentColor;"><path d="M199,125.31l-49.88-18.39L130.69,57a19.92,19.92,0,0,0-37.38,0L74.92,106.92,25,125.31a19.92,19.92,0,0,0,0,37.38l49.88,18.39L93.31,231a19.92,19.92,0,0,0,37.38,0l18.39-49.88L199,162.69a19.92,19.92,0,0,0,0-37.38Z"></path></svg>
            <span id="studioBtnLabel"><?= !empty($editPost['cover_meta']) ? 'Sunting Thumbnail di Studio ' : ' Buka Studio Thumbnail &amp; Template' ?></span>
          </button>

          <div style="margin-bottom: 12px;">
            <label style="display:block; font-size: 12px; font-weight:700; color:var(--ink); margin-bottom: 4px;">Atau Pilih Cover Preset SVG:</label>
            <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:6px;">
              <button type="button" class="btn sm ghost" onclick="selectPresetCover('assets/img/blog-dibalik-layar.svg')" style="padding:4px; font-size:11px; height:auto; text-align:center;">
                Di Balik Layar
              </button>
              <button type="button" class="btn sm ghost" onclick="selectPresetCover('assets/img/blog-bedah-perkara.svg')" style="padding:4px; font-size:11px; height:auto; text-align:center;">
                Bedah Perkara
              </button>
              <button type="button" class="btn sm ghost" onclick="selectPresetCover('assets/img/blog-surat-pembaca.svg')" style="padding:4px; font-size:11px; height:auto; text-align:center;">
                Surat Pembaca
              </button>
            </div>
          </div>

          <div>
            <label style="display:block; font-size: 12px; font-weight:700; color:var(--ink); margin-bottom: 4px;">Atau Unggah Gambar File:</label>
            <input type="file" name="cover_file" accept="image/*" id="coverFileInput"
                   style="font-size:12px; color:var(--muted); width:100%;">
            <small style="color:var(--faint); font-size:11px; display:block; margin-top:4px;">JPG, PNG, WebP, SVG. Maks 10 MB.</small>
          </div>
        </div>

      </div>

    </div>
  </form>

  <style>
    /* Thumbnail Studio Styles */
    .tpl-btn {
      display: flex;
      align-items: center;
      gap: 10px;
      background: var(--bg);
      border: 1px solid var(--ring);
      border-radius: 10px;
      padding: 10px 12px;
      cursor: pointer;
      text-align: left;
      transition: all 0.2s ease;
      color: var(--ink);
      font-family: inherit;
    }
    .tpl-btn:hover {
      background: var(--surface);
      border-color: rgba(236,232,221,0.25);
    }
    .tpl-btn.active {
      background: rgba(214,59,41,0.14) !important;
      border-color: var(--red) !important;
      box-shadow: 0 0 14px rgba(214,59,41,0.3);
    }
    .tpl-btn div b {
      display: block;
      font-size: 13px;
      color: var(--ink);
    }
    .tpl-btn div small {
      display: block;
      font-size: 11px;
      color: var(--muted);
      margin-top: 1px;
    }
    .c-swatch {
      width: 26px;
      height: 26px;
      border-radius: 50%;
      border: 2px solid transparent;
      cursor: pointer;
      padding: 0;
      transition: transform 0.15s, border-color 0.15s;
    }
    .c-swatch:hover {
      transform: scale(1.18);
    }
    .c-swatch.active {
      border-color: #ECE8DD !important;
      box-shadow: 0 0 10px currentColor;
      transform: scale(1.18);
    }
    @media (max-width: 960px) {
      .studio-body-grid {
        grid-template-columns: 1fr !important;
        overflow-y: auto !important;
        height: auto !important;
      }
    }
  </style>

  <!-- ================= STUDIO THUMBNAIL & TEMPLATE MODAL ================= -->
  <div id="thumbnailStudioModal" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(0,0,0,0.88); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); align-items:center; justify-content:center; padding:16px; box-sizing:border-box;">
    <div style="background:#141210; border:1px solid rgba(236,232,221,0.16); border-radius:20px; width:1320px; max-width:98vw; height:92vh; max-height:92vh; display:flex; flex-direction:column; box-shadow:0 32px 80px rgba(0,0,0,0.95); overflow:hidden;">
      
      <!-- Studio Header -->
      <div style="padding:16px 24px; background:#1A1712; border-bottom:1px solid rgba(236,232,221,0.12); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
        <div style="display:flex; align-items:center; gap:12px;">
          <div style="width:38px; height:38px; border-radius:10px; background:var(--red-soft); color:var(--red-ink); display:flex; align-items:center; justify-content:center; font-size:20px;">
            
          </div>
          <div>
            <h2 style="font-family:var(--font-display); font-size:17px; font-weight:800; color:var(--ink); margin:0 0 2px;">
              Studio Thumbnail &amp; Cover Generator
            </h2>
            <p style="font-size:12px; color:var(--muted); margin:0;">
              Template adaptif beresolusi 1200×630 HD — Otomatis menyesuaikan isi artikel dan dapat diedit ulang.
            </p>
          </div>
        </div>

        <div style="display:flex; align-items:center; gap:10px;">
          <span style="font-size:11px; font-weight:700; color:var(--red-ink); background:var(--red-soft); padding:4px 10px; border-radius:999px; border:1px solid rgba(214,59,41,0.3);">
            1200 × 630 px HD (16:9)
          </span>
          <button type="button" onclick="closeThumbnailStudio()" class="btn sm ghost" style="padding:6px 12px; font-size:13px; font-weight:700; color:var(--muted);">
             Tutup
          </button>
        </div>
      </div>

      <!-- Studio Body (2 Columns) -->
      <div style="display:grid; grid-template-columns: 1.18fr 0.82fr; height:calc(100% - 68px); overflow:hidden;" class="studio-body-grid">
        
        <!-- Left: Canvas HD Preview & Fast Actions -->
        <div style="padding:22px; background:#0B0A09; border-right:1px solid rgba(236,232,221,0.1); display:flex; flex-direction:column; justify-content:space-between; overflow-y:auto;">
          
          <div>
            <!-- Status Bar -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
              <span style="font-size:12px; font-weight:700; color:#ECE8DD; display:flex; align-items:center; gap:6px;">
                <span style="width:8px; height:8px; border-radius:50%; background:#D63B29; box-shadow:0 0 8px #D63B29;"></span>
                Live Preview Real-time (16:9)
              </span>
              <button type="button" class="btn sm ghost" onclick="syncFromArticleForm()" style="padding:4px 10px; font-size:11.5px; height:auto; gap:6px;">
                 Sinkronkan dari Form Artikel
              </button>
            </div>

            <!-- Canvas Container -->
            <div style="background:#161310; padding:12px; border-radius:16px; border:1px solid rgba(236,232,221,0.12); box-shadow:inset 0 2px 8px rgba(0,0,0,0.6); position:relative;">
              <canvas id="tnmThumbCanvas" width="1200" height="630" 
                      style="width:100%; height:auto; aspect-ratio:1200/630; border-radius:10px; display:block; box-shadow:0 16px 36px rgba(0,0,0,0.85); background:#0F0F0F;">
              </canvas>
            </div>
            
            <p style="color:var(--faint); font-size:11px; margin:8px 0 0; text-align:center;">
              Semua teks dan visual di atas dapat Anda ubah dan disesuaikan secara bebas di panel sebelah kanan.
            </p>
          </div>

          <!-- Bottom Action Buttons in Studio -->
          <div style="margin-top:20px; padding-top:16px; border-top:1px solid rgba(236,232,221,0.1);">
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:8px;">
              <button type="button" class="btn sm ghost" onclick="downloadThumbnailPng()" style="justify-content:center; gap:6px; font-weight:700; padding:10px;">
                <svg class="ic" viewBox="0 0 256 256" style="width:15px;height:15px;"><path d="M224,152v56a16,16,0,0,1-16,16H48a16,16,0,0,1-16-16V152a8,8,0,0,1,16,0v56H208V152a8,8,0,0,1,16,0Zm-101.66,13.66a8,8,0,0,0,11.32,0l40-40a8,8,0,0,0-11.32-11.32L136,140.69V40a8,8,0,0,0-16,0v100.69l-26.34-26.35a8,8,0,0,0-11.32,11.32Z"></path></svg>
                <span>Unduh PNG (1200×630)</span>
              </button>
              
              <button type="button" class="btn red" onclick="applyThumbnailToArticle()" style="justify-content:center; gap:6px; font-weight:700; padding:10px; box-shadow:0 6px 20px rgba(214,59,41,0.35);">
                <svg class="ic" viewBox="0 0 256 256" style="width:15px;height:15px;"><path d="M229.66,77.66l-128,128a8,8,0,0,1-11.32,0l-56-56a8,8,0,0,1,11.32-11.32L96,188.69,218.34,66.34a8,8,0,0,1,11.32,11.32Z"></path></svg>
                <span>Terapkan sebagai Cover</span>
              </button>
            </div>
            <div id="studioAlertMsg" style="display:none; font-size:12px; text-align:center; padding:6px; border-radius:6px; color:#2ecc71; background:rgba(46,204,113,0.12); border:1px solid rgba(46,204,113,0.3);"></div>
          </div>

        </div>

        <!-- Right: Studio Customization Controls -->
        <div style="padding:22px; background:#141210; overflow-y:auto; display:flex; flex-direction:column; gap:20px;">
          
          <!-- 1. TEMPLATE PRESETS -->
          <div>
            <label style="display:block; font-size:12px; font-weight:800; color:var(--ink); text-transform:uppercase; letter-spacing:0.8px; margin-bottom:10px;">
              1. Pilih Template Desain TNM
            </label>
            <div style="display:grid; grid-template-columns: repeat(2, 1fr); gap:8px;" id="templateButtonsGroup">
              <button type="button" class="tpl-btn" data-template="cerita-serem" onclick="setStudioTemplate('cerita-serem')">
                <span style="font-size:18px;">️</span>
                <div>
                  <b>#CeritaSerem</b>
                  <small>Horor Mistis &amp; Crimson Glow</small>
                </div>
              </button>
              <button type="button" class="tpl-btn" data-template="malam-minggu" onclick="setStudioTemplate('malam-minggu')">
                <span style="font-size:18px;"></span>
                <div>
                  <b>#MalamMinggu</b>
                  <small>Radio Malam &amp; Gelombang Suara</small>
                </div>
              </button>
              <button type="button" class="tpl-btn" data-template="perkara" onclick="setStudioTemplate('perkara')">
                <span style="font-size:18px;"></span>
                <div>
                  <b>#Perkara</b>
                  <small>Investigasi &amp; Dossier Berkas</small>
                </div>
              </button>
              <button type="button" class="tpl-btn" data-template="dibalik-layar" onclick="setStudioTemplate('dibalik-layar')">
                <span style="font-size:18px;">️</span>
                <div>
                  <b>Di Balik Layar</b>
                  <small>Studio Siaran &amp; Catatan Pribadi</small>
                </div>
              </button>
              <button type="button" class="tpl-btn" data-template="minimalist" onclick="setStudioTemplate('minimalist')" style="grid-column: span 2;">
                <span style="font-size:18px;">⬛</span>
                <div>
                  <b>Minimalist Monolith</b>
                  <small>Obsidian Modern, Tipografi Tegas &amp; Monogram</small>
                </div>
              </button>
            </div>
          </div>

          <!-- 2. TEKS & TIPOGRAFI KONTEN -->
          <div style="background:#1B1813; border:1px solid rgba(236,232,221,0.08); border-radius:12px; padding:16px;">
            <label style="display:block; font-size:12px; font-weight:800; color:var(--ink); text-transform:uppercase; letter-spacing:0.8px; margin-bottom:12px;">
              2. Kustomisasi Teks Thumbnail
            </label>

            <div style="margin-bottom:12px;">
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                <label style="font-size:11.5px; font-weight:700; color:var(--muted);">Badge Kategori (Opsional)</label>
                <button type="button" class="btn sm ghost" onclick="clearStudioBadge()" style="padding:1px 6px; font-size:10.5px; height:auto; color:var(--faint);" title="Kosongkan agar badge tidak muncul di thumbnail">
                   Hapus Badge
                </button>
              </div>
              <input type="text" id="tsBadge" oninput="renderThumbnail()" placeholder="Opsional (kosongkan jika tidak ingin menampilkan badge)..."
                     style="width:100%; background:var(--bg); border:1px solid var(--ring); border-radius:6px; padding:8px 10px; font-size:13px; color:var(--ink); outline:none; box-sizing:border-box;">
              <small style="color:var(--faint); font-size:10.5px; display:block; margin-top:3px;">
                 Jika dikosongkan, pill badge tidak akan muncul dan teks judul otomatis digeser ke atas.
              </small>
            </div>

            <div style="margin-bottom:12px;">
              <label style="display:block; font-size:11.5px; font-weight:700; color:var(--muted); margin-bottom:4px;">Judul Baris Utama (Putih Tegas)</label>
              <input type="text" id="tsTitle" oninput="renderThumbnail()" placeholder="Judul artikel utama..."
                     style="width:100%; background:var(--bg); border:1px solid var(--ring); border-radius:6px; padding:8px 10px; font-size:13px; font-weight:700; color:var(--ink); outline:none; box-sizing:border-box;">
            </div>

            <div style="margin-bottom:12px;">
              <label style="display:block; font-size:11.5px; font-weight:700; color:var(--muted); margin-bottom:4px;">Subjudul / Kalimat Aksen Berwarna (Opsional)</label>
              <input type="text" id="tsSubtitle" oninput="renderThumbnail()" placeholder="Contoh: Kenapa Jam 2 Pagi Selalu Berbeda?"
                     style="width:100%; background:var(--bg); border:1px solid var(--ring); border-radius:6px; padding:8px 10px; font-size:13px; font-weight:600; color:var(--ink); outline:none; box-sizing:border-box;">
            </div>

            <div style="margin-bottom:12px;">
              <label style="display:block; font-size:11.5px; font-weight:700; color:var(--muted); margin-bottom:4px;">Kutipan / Ringkasan (1-2 Baris)</label>
              <textarea id="tsQuote" rows="2" oninput="renderThumbnail()" placeholder="Kutipan singkat atau hook cerita penonton..."
                        style="width:100%; background:var(--bg); border:1px solid var(--ring); border-radius:6px; padding:8px 10px; font-size:12.5px; color:var(--muted); outline:none; resize:vertical; box-sizing:border-box;"></textarea>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
              <div>
                <label style="display:block; font-size:11.5px; font-weight:700; color:var(--muted); margin-bottom:4px;">Nama Penulis</label>
                <input type="text" id="tsAuthor" oninput="renderThumbnail()" value="Andri TNM"
                       style="width:100%; background:var(--bg); border:1px solid var(--ring); border-radius:6px; padding:7px 9px; font-size:12px; color:var(--ink); outline:none; box-sizing:border-box;">
              </div>
              <div>
                <label style="display:block; font-size:11.5px; font-weight:700; color:var(--muted); margin-bottom:4px;">Tagline / Info</label>
                <input type="text" id="tsTagline" oninput="renderThumbnail()" value="Host &amp; Kurator Cerita · 4 Menit Baca"
                       style="width:100%; background:var(--bg); border:1px solid var(--ring); border-radius:6px; padding:7px 9px; font-size:12px; color:var(--ink); outline:none; box-sizing:border-box;">
              </div>
            </div>

            <div style="margin-top:12px;">
              <label style="display:block; font-size:11.5px; font-weight:700; color:var(--muted); margin-bottom:4px;">Ukuran Font Judul</label>
              <select id="tsFontSize" onchange="renderThumbnail()" style="width:100%; background:var(--bg); border:1px solid var(--ring); border-radius:6px; padding:7px 10px; font-size:12.5px; color:var(--ink); outline:none;">
                <option value="auto">Otomatis (Menyesuaikan Panjang Judul)</option>
                <option value="compact">Kompak (34px)</option>
                <option value="medium">Sedang (40px)</option>
                <option value="large">Besar (48px)</option>
              </select>
            </div>
          </div>

          <!-- 3. WARNA AKSEN & SUASANA VISUAL -->
          <div style="background:#1B1813; border:1px solid rgba(236,232,221,0.08); border-radius:12px; padding:16px;">
            <label style="display:block; font-size:12px; font-weight:800; color:var(--ink); text-transform:uppercase; letter-spacing:0.8px; margin-bottom:12px;">
              3. Warna Aksen &amp; Suasana Latar
            </label>

            <!-- Color Swatches -->
            <div style="margin-bottom:12px;">
              <label style="display:block; font-size:11.5px; font-weight:700; color:var(--muted); margin-bottom:6px;">Warna Aksen Desain:</label>
              <div style="display:flex; flex-wrap:wrap; gap:8px; align-items:center;" id="colorSwatches">
                <button type="button" class="c-swatch active" data-color="#D63B29" style="background:#D63B29;" title="TNM Crimson" onclick="setStudioColor('#D63B29')"></button>
                <button type="button" class="c-swatch" data-color="#961C1C" style="background:#961C1C;" title="Dark Blood Wine" onclick="setStudioColor('#961C1C')"></button>
                <button type="button" class="c-swatch" data-color="#E67E22" style="background:#E67E22;" title="Midnight Amber" onclick="setStudioColor('#E67E22')"></button>
                <button type="button" class="c-swatch" data-color="#8E44AD" style="background:#8E44AD;" title="Mystic Violet" onclick="setStudioColor('#8E44AD')"></button>
                <button type="button" class="c-swatch" data-color="#00D2D3" style="background:#00D2D3;" title="Cyber Cyan" onclick="setStudioColor('#00D2D3')"></button>
                <button type="button" class="c-swatch" data-color="#F1C40F" style="background:#F1C40F;" title="Caution Gold" onclick="setStudioColor('#F1C40F')"></button>
                <button type="button" class="c-swatch" data-color="#ECE8DD" style="background:#ECE8DD;" title="Bone White" onclick="setStudioColor('#ECE8DD')"></button>
                <button type="button" class="c-swatch" data-color="#2ECC71" style="background:#2ECC71;" title="Forest Emerald" onclick="setStudioColor('#2ECC71')"></button>
                
                <input type="color" id="tsColorPicker" value="#D63B29" oninput="setStudioColor(this.value)" 
                       style="width:30px; height:30px; padding:0; border:none; border-radius:50%; cursor:pointer; background:transparent;">
              </div>
            </div>

            <!-- Background Theme -->
            <div style="margin-bottom:6px;">
              <label style="display:block; font-size:11.5px; font-weight:700; color:var(--muted); margin-bottom:4px;">Gradien Latar Belakang:</label>
              <select id="tsBgTheme" onchange="renderThumbnail()" style="width:100%; background:var(--bg); border:1px solid var(--ring); border-radius:6px; padding:7px 10px; font-size:12.5px; color:var(--ink); outline:none;">
                <option value="obsidian">Deep Obsidian (#0F0F0F — Hitam Pekat)</option>
                <option value="charcoal">Studio Charcoal (#171412 — Hangat Studio)</option>
                <option value="midnight">Midnight Navy (#0A0E18 — Malam Biru Gelap)</option>
                <option value="crimson">Crimson Shadow (#1F0E0E — Horor Berdarah)</option>
              </select>
            </div>
          </div>

          <!-- 4. ELEMEN DEKORASI & GRAFIS -->
          <div style="background:#1B1813; border:1px solid rgba(236,232,221,0.08); border-radius:12px; padding:16px;">
            <label style="display:block; font-size:12px; font-weight:800; color:var(--ink); text-transform:uppercase; letter-spacing:0.8px; margin-bottom:12px;">
              4. Elemen Dekorasi &amp; Ilustrasi
            </label>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:10px;">
              <div>
                <label style="display:block; font-size:11.5px; font-weight:700; color:var(--muted); margin-bottom:4px;">Pola Tekstur</label>
                <select id="tsPattern" onchange="renderThumbnail()" style="width:100%; background:var(--bg); border:1px solid var(--ring); border-radius:6px; padding:7px 10px; font-size:12px; color:var(--ink); outline:none;">
                  <option value="grid">Grid Kotak TNM</option>
                  <option value="dots">Pola Titik (Dots)</option>
                  <option value="waves">Garis Gelombang Suara</option>
                  <option value="none">Polos (Tanpa Pola)</option>
                </select>
              </div>
              <div>
                <label style="display:block; font-size:11.5px; font-weight:700; color:var(--muted); margin-bottom:4px;">Ikon Grafis Kanan</label>
                <select id="tsGraphic" onchange="renderThumbnail()" style="width:100%; background:var(--bg); border:1px solid var(--ring); border-radius:6px; padding:7px 10px; font-size:12px; color:var(--ink); outline:none;">
                  <option value="auto">Otomatis Sesuai Template</option>
                  <option value="mic">Mikrofon Studio &amp; Audio</option>
                  <option value="radio">Radio Equalizer &amp; Bulan</option>
                  <option value="dossier">Berkas Kasus &amp; Kaca Pembesar</option>
                  <option value="ghost">Segel Mistis &amp; Partikel Horor</option>
                  <option value="monolith">Monogram Monolith TNM</option>
                  <option value="none">Tanpa Ikon Grafis</option>
                </select>
              </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
              <div>
                <label style="display:block; font-size:11.5px; font-weight:700; color:var(--muted); margin-bottom:4px;">Glow Spotlight</label>
                <select id="tsSpotlight" onchange="renderThumbnail()" style="width:100%; background:var(--bg); border:1px solid var(--ring); border-radius:6px; padding:7px 10px; font-size:12px; color:var(--ink); outline:none;">
                  <option value="right">Kanan Atas (Fokus Grafis)</option>
                  <option value="left">Kiri Atas (Fokus Judul)</option>
                  <option value="center">Tengah</option>
                  <option value="none">Tanpa Glow</option>
                </select>
              </div>
              <div>
                <label style="display:block; font-size:11.5px; font-weight:700; color:var(--muted); margin-bottom:4px;">Gaya Bingkai (Frame)</label>
                <select id="tsFrame" onchange="renderThumbnail()" style="width:100%; background:var(--bg); border:1px solid var(--ring); border-radius:6px; padding:7px 10px; font-size:12px; color:var(--ink); outline:none;">
                  <option value="double">Frame Ganda TNM Modern</option>
                  <option value="viewfinder">Viewfinder Sudut Kamera</option>
                  <option value="single">Garis Tunggal Halus</option>
                  <option value="none">Tanpa Frame</option>
                </select>
              </div>
            </div>

          </div>

        </div>

      </div>

    </div>
  </div>

  <script>
    // Live slug generation from title
    const titleInput = document.getElementById('articleTitle');
    const slugInput = document.getElementById('articleSlug');
    if (titleInput && slugInput && !slugInput.value) {
      titleInput.addEventListener('input', function() {
        slugInput.value = this.value
          .toLowerCase()
          .replace(/[^\w\s-]/g, '')
          .trim()
          .replace(/[\s_-]+/g, '-')
          .replace(/^-+|-+$/g, '');
      });
    }

    // Markdown insertion helper
    function insertSyntax(before, after) {
      const textarea = document.getElementById('articleContent');
      if (!textarea) return;
      const start = textarea.selectionStart;
      const end = textarea.selectionEnd;
      const selected = textarea.value.substring(start, end);
      const replacement = before + selected + after;
      textarea.value = textarea.value.substring(0, start) + replacement + textarea.value.substring(end);
      textarea.focus();
      textarea.selectionStart = start + before.length;
      textarea.selectionEnd = end + before.length;
    }

    // Preset cover select
    function selectPresetCover(path) {
      document.getElementById('coverImagePath').value = path;
      document.getElementById('coverCanvasData').value = '';
      document.getElementById('coverPreviewImg').src = '../../' + path;
      const statusPill = document.getElementById('customCoverStatusPill');
      if (statusPill) {
        statusPill.textContent = 'Preset SVG';
        statusPill.style.color = 'var(--muted)';
        statusPill.style.background = 'var(--shell)';
        statusPill.style.borderColor = 'var(--ring)';
      }
      const notice = document.getElementById('coverNoticeBox');
      if (notice) notice.style.display = 'none';
    }

    // Live preview of uploaded file
    const fileInput = document.getElementById('coverFileInput');
    if (fileInput) {
      fileInput.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
          const reader = new FileReader();
          reader.onload = function(ev) {
            document.getElementById('coverPreviewImg').src = ev.target.result;
            const statusPill = document.getElementById('customCoverStatusPill');
            if (statusPill) {
              statusPill.textContent = 'Upload File';
              statusPill.style.color = '#3498db';
              statusPill.style.background = 'rgba(52,152,219,0.12)';
              statusPill.style.borderColor = 'rgba(52,152,219,0.3)';
            }
            const notice = document.getElementById('coverNoticeBox');
            if (notice) notice.style.display = 'none';
          };
          reader.readAsDataURL(this.files[0]);
        }
      });
    }

    /* ====================================================================
       TNM THUMBNAIL STUDIO & TEMPLATE GENERATOR ENGINE
       ==================================================================== */
    let studioState = {
      template: 'dibalik-layar',
      badge: 'CATATAN PRIBADI',
      title: '',
      subtitle: '',
      quote: '',
      author: 'Andri TNM',
      tagline: 'Host & Kurator Cerita · 4 Menit Baca',
      accentColor: '#D63B29',
      bgTheme: 'obsidian',
      pattern: 'grid',
      graphic: 'auto',
      spotlight: 'right',
      frame: 'double',
      fontSize: 'auto'
    };

    function hexToRgba(hex, alpha) {
      if (!hex) return `rgba(214, 59, 41, ${alpha})`;
      let c = hex.replace('#', '');
      if (c.length === 3) c = c.split('').map(x => x + x).join('');
      const num = parseInt(c, 16);
      if (isNaN(num)) return `rgba(214, 59, 41, ${alpha})`;
      const r = (num >> 16) & 255;
      const g = (num >> 8) & 255;
      const b = num & 255;
      return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }

    function roundRect(ctx, x, y, w, h, r) {
      ctx.beginPath();
      ctx.moveTo(x + r, y);
      ctx.arcTo(x + w, y, x + w, y + h, r);
      ctx.arcTo(x + w, y + h, x, y + h, r);
      ctx.arcTo(x, y + h, x, y, r);
      ctx.arcTo(x, y, x + w, y, r);
      ctx.closePath();
    }

    function wrapText(ctx, text, maxWidth) {
      if (!text) return [];
      const words = text.split(/\s+/);
      const lines = [];
      let currentLine = words[0];

      for (let i = 1; i < words.length; i++) {
        const word = words[i];
        const width = ctx.measureText(currentLine + ' ' + word).width;
        if (width < maxWidth) {
          currentLine += ' ' + word;
        } else {
          lines.push(currentLine);
          currentLine = word;
        }
      }
      if (currentLine) lines.push(currentLine);
      return lines;
    }

    function openThumbnailStudio() {
      const modal = document.getElementById('thumbnailStudioModal');
      if (!modal) return;
      modal.style.display = 'flex';
      document.body.style.overflow = 'hidden';

      const metaInput = document.getElementById('coverMetaJson');
      let loadedFromSaved = false;

      if (metaInput && metaInput.value && metaInput.value.trim().startsWith('{')) {
        try {
          const parsed = JSON.parse(metaInput.value);
          studioState = Object.assign({}, studioState, parsed);
          applyStateToStudioInputs();
          loadedFromSaved = true;
        } catch (e) {
          console.warn('Gagal membaca cover_meta JSON:', e);
        }
      }

      if (!loadedFromSaved) {
        syncFromArticleForm();
      }

      updateTemplateButtonUI();
      updateColorSwatchUI();

      // Ensure fonts are ready before first draw
      if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(() => renderThumbnail());
      } else {
        renderThumbnail();
      }
    }

    function closeThumbnailStudio() {
      const modal = document.getElementById('thumbnailStudioModal');
      if (modal) modal.style.display = 'none';
      document.body.style.overflow = '';
    }

    function readInputsIntoStudioState() {
      const b = document.getElementById('tsBadge');
      const t = document.getElementById('tsTitle');
      const s = document.getElementById('tsSubtitle');
      const q = document.getElementById('tsQuote');
      const a = document.getElementById('tsAuthor');
      const tg = document.getElementById('tsTagline');
      const fs = document.getElementById('tsFontSize');
      const bg = document.getElementById('tsBgTheme');
      const pt = document.getElementById('tsPattern');
      const gr = document.getElementById('tsGraphic');
      const sp = document.getElementById('tsSpotlight');
      const fr = document.getElementById('tsFrame');
      const cp = document.getElementById('tsColorPicker');

      if (b) studioState.badge = b.value;
      if (t) studioState.title = t.value;
      if (s) studioState.subtitle = s.value;
      if (q) studioState.quote = q.value;
      if (a) studioState.author = a.value;
      if (tg) studioState.tagline = tg.value;
      if (fs) studioState.fontSize = fs.value;
      if (bg) studioState.bgTheme = bg.value;
      if (pt) studioState.pattern = pt.value;
      if (gr) studioState.graphic = gr.value;
      if (sp) studioState.spotlight = sp.value;
      if (fr) studioState.frame = fr.value;
      if (cp) studioState.accentColor = cp.value;
    }

    function updateStylingInputsFromState() {
      if (document.getElementById('tsBgTheme')) document.getElementById('tsBgTheme').value = studioState.bgTheme;
      if (document.getElementById('tsPattern')) document.getElementById('tsPattern').value = studioState.pattern;
      if (document.getElementById('tsGraphic')) document.getElementById('tsGraphic').value = studioState.graphic;
      if (document.getElementById('tsSpotlight')) document.getElementById('tsSpotlight').value = studioState.spotlight;
      if (document.getElementById('tsFrame')) document.getElementById('tsFrame').value = studioState.frame;
      if (document.getElementById('tsColorPicker')) document.getElementById('tsColorPicker').value = studioState.accentColor;
    }

    function applyStateToStudioInputs() {
      if (document.getElementById('tsBadge')) document.getElementById('tsBadge').value = studioState.badge || '';
      if (document.getElementById('tsTitle')) document.getElementById('tsTitle').value = studioState.title || '';
      if (document.getElementById('tsSubtitle')) document.getElementById('tsSubtitle').value = studioState.subtitle || '';
      if (document.getElementById('tsQuote')) document.getElementById('tsQuote').value = studioState.quote || '';
      if (document.getElementById('tsAuthor')) document.getElementById('tsAuthor').value = studioState.author || 'Andri TNM';
      if (document.getElementById('tsTagline')) document.getElementById('tsTagline').value = studioState.tagline || 'Host & Kurator Cerita · 4 Menit Baca';
      if (document.getElementById('tsFontSize')) document.getElementById('tsFontSize').value = studioState.fontSize || 'auto';
      updateStylingInputsFromState();
    }

    function clearStudioBadge() {
      const badgeInput = document.getElementById('tsBadge');
      if (badgeInput) {
        badgeInput.value = '';
        studioState.badge = '';
        renderThumbnail();
      }
    }

    function syncFromArticleForm() {
      const artTitle = (document.getElementById('articleTitle')?.value || '').trim();
      const artCat = (document.querySelector('select[name="category"]')?.value || 'Catatan Pribadi').trim();
      const artExcerpt = (document.querySelector('textarea[name="excerpt"]')?.value || '').trim();
      const artAuthor = (document.querySelector('input[name="author"]')?.value || 'Andri TNM').trim();

      // Determine matching template
      let matchedTpl = 'dibalik-layar';
      if (artCat.includes('CeritaSerem')) matchedTpl = 'cerita-serem';
      else if (artCat.includes('MalamMinggu')) matchedTpl = 'malam-minggu';
      else if (artCat.includes('Perkara')) matchedTpl = 'perkara';
      else if (artCat.includes('Editorial')) matchedTpl = 'minimalist';

      studioState.template = matchedTpl;
      studioState.badge = artCat ? artCat.toUpperCase() : '';
      studioState.author = artAuthor || 'Andri TNM';
      studioState.quote = artExcerpt;

      // Smart title splitting (if contains ':' or ' - ')
      if (artTitle.includes(':')) {
        const parts = artTitle.split(':');
        studioState.title = parts[0].trim();
        studioState.subtitle = parts.slice(1).join(':').trim();
      } else if (artTitle.includes(' - ')) {
        const parts = artTitle.split(' - ');
        studioState.title = parts[0].trim();
        studioState.subtitle = parts.slice(1).join(' - ').trim();
      } else {
        studioState.title = artTitle || 'JUDUL CATATAN TNM';
        studioState.subtitle = '';
      }

      applyTemplateDefaults(matchedTpl);
      applyStateToStudioInputs();
      updateTemplateButtonUI();
      updateColorSwatchUI();
      renderThumbnail();
    }

    function applyTemplateDefaults(tpl) {
      if (tpl === 'cerita-serem') {
        studioState.accentColor = '#D63B29';
        studioState.bgTheme = 'crimson';
        studioState.pattern = 'grid';
        studioState.graphic = 'auto';
        studioState.spotlight = 'right';
        studioState.frame = 'double';
      } else if (tpl === 'malam-minggu') {
        studioState.accentColor = '#E67E22';
        studioState.bgTheme = 'midnight';
        studioState.pattern = 'waves';
        studioState.graphic = 'auto';
        studioState.spotlight = 'right';
        studioState.frame = 'double';
      } else if (tpl === 'perkara') {
        studioState.accentColor = '#D63B29';
        studioState.bgTheme = 'charcoal';
        studioState.pattern = 'dots';
        studioState.graphic = 'auto';
        studioState.spotlight = 'right';
        studioState.frame = 'viewfinder';
      } else if (tpl === 'minimalist') {
        studioState.accentColor = '#ECE8DD';
        studioState.bgTheme = 'obsidian';
        studioState.pattern = 'grid';
        studioState.graphic = 'auto';
        studioState.spotlight = 'center';
        studioState.frame = 'viewfinder';
      } else { // dibalik-layar
        studioState.accentColor = '#D63B29';
        studioState.bgTheme = 'obsidian';
        studioState.pattern = 'grid';
        studioState.graphic = 'auto';
        studioState.spotlight = 'right';
        studioState.frame = 'double';
      }
    }

    function setStudioTemplate(tplName) {
      // 1. Simpan seluruh teks yang sedang diketik user agar tidak hilang
      readInputsIntoStudioState();

      // 2. Terapkan template baru
      studioState.template = tplName;
      applyTemplateDefaults(tplName);

      // 3. Perbarui hanya kontrol visual styling di form, jangan timpa teks user!
      updateStylingInputsFromState();
      updateTemplateButtonUI();
      updateColorSwatchUI();

      // 4. Bersihkan dan gambar ulang frame baru
      renderThumbnail();
    }

    function updateTemplateButtonUI() {
      document.querySelectorAll('#templateButtonsGroup .tpl-btn').forEach(btn => {
        if (btn.getAttribute('data-template') === studioState.template) {
          btn.classList.add('active');
        } else {
          btn.classList.remove('active');
        }
      });
    }

    function setStudioColor(color) {
      studioState.accentColor = color;
      const cp = document.getElementById('tsColorPicker');
      if (cp) cp.value = color;
      updateColorSwatchUI();
      renderThumbnail();
    }

    function updateColorSwatchUI() {
      const activeColor = (studioState.accentColor || '').toLowerCase();
      document.querySelectorAll('#colorSwatches .c-swatch').forEach(sw => {
        const swColor = (sw.getAttribute('data-color') || '').toLowerCase();
        if (swColor === activeColor) {
          sw.classList.add('active');
        } else {
          sw.classList.remove('active');
        }
      });
    }

    /* RENDER THUMBNAIL TO CANVAS (1200 x 630) */
    function renderThumbnail() {
      const canvas = document.getElementById('tnmThumbCanvas');
      if (!canvas) return;
      const ctx = canvas.getContext('2d');
      const w = 1200;
      const h = 630;

      // 1. RESET SEMPURNA SELURUH PROPERTI CONTEXT
      ctx.setTransform(1, 0, 0, 1, 0, 0);
      ctx.globalAlpha = 1.0;
      ctx.globalCompositeOperation = 'source-over';
      ctx.setLineDash([]);
      ctx.shadowColor = 'transparent';
      ctx.shadowBlur = 0;
      ctx.shadowOffsetX = 0;
      ctx.shadowOffsetY = 0;
      ctx.filter = 'none';
      ctx.imageSmoothingEnabled = true;
      ctx.imageSmoothingQuality = 'high';

      // 2. BERSIHKAN TOTAL SELURUH PIKSEL CANVAS SEBELUM MENGGAMBAR
      ctx.clearRect(0, 0, w, h);

      // Baca nilai terbaru dari input form studio
      readInputsIntoStudioState();

      const accent = studioState.accentColor || '#D63B29';

      // 3. GAMBAR GRADIEN LATAR BELAKANG
      let bgGrad = ctx.createLinearGradient(0, 0, w, h);
      if (studioState.bgTheme === 'crimson') {
        bgGrad.addColorStop(0, '#140909');
        bgGrad.addColorStop(0.5, '#261010');
        bgGrad.addColorStop(1, '#3A1414');
      } else if (studioState.bgTheme === 'midnight') {
        bgGrad.addColorStop(0, '#090B12');
        bgGrad.addColorStop(0.5, '#111728');
        bgGrad.addColorStop(1, '#1A2136');
      } else if (studioState.bgTheme === 'charcoal') {
        bgGrad.addColorStop(0, '#141311');
        bgGrad.addColorStop(0.5, '#1E1B17');
        bgGrad.addColorStop(1, '#28221D');
      } else { // obsidian
        bgGrad.addColorStop(0, '#0C0C0C');
        bgGrad.addColorStop(0.5, '#171512');
        bgGrad.addColorStop(1, '#211C18');
      }
      ctx.fillStyle = bgGrad;
      ctx.fillRect(0, 0, w, h);

      // 4. GAMBAR POLA TEKSTUR
      if (studioState.pattern === 'grid') {
        ctx.save();
        ctx.strokeStyle = '#ECE8DD';
        ctx.globalAlpha = 0.04;
        ctx.lineWidth = 1;
        for (let x = 0; x < w; x += 40) {
          ctx.beginPath(); ctx.moveTo(x, 0); ctx.lineTo(x, h); ctx.stroke();
        }
        for (let y = 0; y < h; y += 40) {
          ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(w, y); ctx.stroke();
        }
        ctx.restore();
      } else if (studioState.pattern === 'dots') {
        ctx.save();
        ctx.fillStyle = '#ECE8DD';
        ctx.globalAlpha = 0.06;
        for (let x = 20; x < w; x += 30) {
          for (let y = 20; y < h; y += 30) {
            ctx.beginPath(); ctx.arc(x, y, 1.5, 0, Math.PI * 2); ctx.fill();
          }
        }
        ctx.restore();
      } else if (studioState.pattern === 'waves') {
        ctx.save();
        ctx.globalAlpha = 0.15;
        ctx.lineWidth = 2;
        ctx.strokeStyle = '#ECE8DD';
        ctx.beginPath();
        ctx.moveTo(0, 480);
        ctx.bezierCurveTo(300, 420, 600, 520, w, 450);
        ctx.stroke();

        ctx.strokeStyle = accent;
        ctx.beginPath();
        ctx.moveTo(0, 510);
        ctx.bezierCurveTo(350, 450, 700, 540, w, 490);
        ctx.stroke();

        ctx.strokeStyle = '#ECE8DD';
        ctx.lineWidth = 1.5;
        ctx.beginPath();
        ctx.moveTo(0, 540);
        ctx.bezierCurveTo(400, 480, 800, 560, w, 520);
        ctx.stroke();
        ctx.restore();
      }

      // 5. GAMBAR SPOTLIGHT GLOW
      if (studioState.spotlight !== 'none') {
        let spotX = 880, spotY = 260, spotR = 380;
        if (studioState.spotlight === 'left') { spotX = 320; spotY = 260; }
        else if (studioState.spotlight === 'center') { spotX = 600; spotY = 315; spotR = 450; }

        ctx.save();
        const spotGrad = ctx.createRadialGradient(spotX, spotY, 20, spotX, spotY, spotR);
        spotGrad.addColorStop(0, hexToRgba(accent, 0.28));
        spotGrad.addColorStop(0.7, hexToRgba(accent, 0.0));
        ctx.fillStyle = spotGrad;
        ctx.beginPath();
        ctx.arc(spotX, spotY, spotR, 0, Math.PI * 2);
        ctx.fill();
        ctx.restore();
      }

      // 6. GAMBAR ILUSTRASI GRAFIS KANAN
      let effectiveGraphic = studioState.graphic;
      if (effectiveGraphic === 'auto') {
        if (studioState.template === 'cerita-serem') effectiveGraphic = 'ghost';
        else if (studioState.template === 'malam-minggu') effectiveGraphic = 'radio';
        else if (studioState.template === 'perkara') effectiveGraphic = 'dossier';
        else if (studioState.template === 'minimalist') effectiveGraphic = 'monolith';
        else effectiveGraphic = 'mic';
      }

      if (effectiveGraphic === 'mic') {
        ctx.save();
        ctx.translate(760, 130);
        // Aura circle
        ctx.beginPath();
        ctx.arc(160, 170, 140, 0, Math.PI * 2);
        ctx.strokeStyle = accent;
        ctx.lineWidth = 1.5;
        ctx.setLineDash([6, 6]);
        ctx.globalAlpha = 0.4;
        ctx.stroke();
        ctx.setLineDash([]);
        // Mic Backplate
        ctx.beginPath();
        ctx.arc(160, 170, 110, 0, Math.PI * 2);
        ctx.fillStyle = '#14110E';
        ctx.strokeStyle = '#ECE8DD';
        ctx.lineWidth = 2;
        ctx.globalAlpha = 0.8;
        ctx.fill();
        ctx.globalAlpha = 0.15;
        ctx.stroke();
        // Mic capsule
        ctx.globalAlpha = 1;
        roundRect(ctx, 135, 90, 50, 90, 25);
        ctx.fillStyle = '#1D1914';
        ctx.fill();
        ctx.strokeStyle = accent;
        ctx.lineWidth = 3.5;
        ctx.stroke();
        // Grille horizontal lines
        ctx.strokeStyle = '#ECE8DD';
        ctx.lineWidth = 2;
        ctx.globalAlpha = 0.4;
        [115, 128, 141].forEach(ly => {
          ctx.beginPath(); ctx.moveTo(142, ly); ctx.lineTo(178, ly); ctx.stroke();
        });
        // Stand arc
        ctx.globalAlpha = 0.75;
        ctx.beginPath();
        ctx.arc(160, 145, 40, 0, Math.PI);
        ctx.strokeStyle = '#ECE8DD';
        ctx.lineWidth = 4;
        ctx.lineCap = 'round';
        ctx.stroke();
        // Stand stem
        ctx.beginPath(); ctx.moveTo(160, 185); ctx.lineTo(160, 235); ctx.stroke();
        // Stand base
        ctx.strokeStyle = accent;
        ctx.lineWidth = 5;
        ctx.beginPath(); ctx.moveTo(130, 235); ctx.lineTo(190, 235); ctx.stroke();
        // Equalizer Bars
        const eqHeights = [24, 36, 18, 40, 22, 32, 20, 28, 14, 36, 22, 12, 28, 18];
        eqHeights.forEach((eh, ei) => {
          const bx = 60 + (ei * 14);
          const by = 260 + (40 - eh);
          roundRect(ctx, bx, by, 6, eh, 3);
          ctx.fillStyle = (ei % 2 === 0) ? accent : '#ECE8DD';
          ctx.globalAlpha = (ei % 2 === 0) ? 0.9 : 0.65;
          ctx.fill();
        });
        ctx.restore();
      } else if (effectiveGraphic === 'dossier') {
        ctx.save();
        ctx.translate(740, 120);
        // Dossier Folder
        ctx.globalAlpha = 0.9;
        roundRect(ctx, 60, 80, 260, 200, 14);
        ctx.fillStyle = '#201C17';
        ctx.fill();
        ctx.strokeStyle = '#ECE8DD';
        ctx.lineWidth = 2;
        ctx.globalAlpha = 0.15;
        ctx.stroke();
        // Folder tab line
        ctx.beginPath();
        ctx.moveTo(60, 110); ctx.lineTo(140, 110); ctx.lineTo(165, 130); ctx.lineTo(320, 130);
        ctx.strokeStyle = accent;
        ctx.lineWidth = 3;
        ctx.globalAlpha = 0.8;
        ctx.stroke();
        // Document lines
        ctx.strokeStyle = '#ECE8DD';
        ctx.lineWidth = 3;
        ctx.lineCap = 'round';
        const dlines = [
          { x2: 160, y: 160, a: 0.4 },
          { x2: 150, y: 185, a: 0.3 },
          { x2: 165, y: 210, a: 0.25 },
          { x2: 135, y: 235, a: 0.2 }
        ];
        dlines.forEach(dl => {
          ctx.globalAlpha = dl.a;
          ctx.beginPath(); ctx.moveTo(85, dl.y); ctx.lineTo(dl.x2, dl.y); ctx.stroke();
        });
        // Stamped Badge
        ctx.save();
        ctx.translate(235, 191);
        ctx.rotate(-8 * Math.PI / 180);
        roundRect(ctx, -55, -21, 110, 42, 6);
        ctx.fillStyle = hexToRgba(accent, 0.18);
        ctx.fill();
        ctx.strokeStyle = accent;
        ctx.lineWidth = 2;
        ctx.stroke();
        ctx.font = "bold 13px 'Archivo Black', sans-serif";
        ctx.fillStyle = accent;
        ctx.textAlign = 'center';
        ctx.fillText('INVESTIGASI', 0, 5);
        ctx.restore();
        // Magnifying Glass
        ctx.save();
        ctx.translate(190, 180);
        ctx.beginPath();
        ctx.arc(70, 70, 60, 0, Math.PI * 2);
        ctx.fillStyle = 'rgba(42, 27, 24, 0.6)';
        ctx.fill();
        ctx.strokeStyle = '#ECE8DD';
        ctx.lineWidth = 6;
        ctx.globalAlpha = 0.8;
        ctx.stroke();
        ctx.beginPath();
        ctx.arc(70, 70, 54, 0, Math.PI * 2);
        ctx.strokeStyle = accent;
        ctx.lineWidth = 2;
        ctx.stroke();
        ctx.beginPath();
        ctx.arc(70, 70, 45, -Math.PI * 0.75, -Math.PI * 0.25);
        ctx.strokeStyle = '#FFFFFF';
        ctx.lineWidth = 3;
        ctx.lineCap = 'round';
        ctx.globalAlpha = 0.6;
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(112, 112); ctx.lineTo(170, 170);
        ctx.strokeStyle = accent;
        ctx.lineWidth = 12;
        ctx.lineCap = 'round';
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(124, 124); ctx.lineTo(162, 162);
        ctx.strokeStyle = '#ECE8DD';
        ctx.lineWidth = 4;
        ctx.stroke();
        ctx.restore();
        ctx.restore();
      } else if (effectiveGraphic === 'radio') {
        ctx.save();
        ctx.translate(740, 120);
        // Moon Glow
        ctx.save();
        ctx.beginPath();
        ctx.arc(220, 110, 52, 0, Math.PI * 2);
        ctx.fillStyle = accent;
        ctx.globalAlpha = 0.85;
        ctx.fill();
        ctx.globalCompositeOperation = 'destination-out';
        ctx.beginPath();
        ctx.arc(200, 100, 46, 0, Math.PI * 2);
        ctx.fill();
        ctx.globalCompositeOperation = 'source-over'; // Reset blend mode
        ctx.restore();
        // Radio transmission waves
        ctx.strokeStyle = accent;
        ctx.lineWidth = 2;
        [100, 140, 180].forEach((rw, rIdx) => {
          ctx.save();
          ctx.globalAlpha = 0.4 - (rIdx * 0.1);
          ctx.setLineDash([8, 8]);
          ctx.beginPath();
          ctx.arc(160, 180, rw, -Math.PI * 0.8, -Math.PI * 0.2);
          ctx.stroke();
          ctx.setLineDash([]);
          ctx.restore();
        });
        // Equalizer visualizer
        const rbars = [16, 28, 45, 65, 85, 50, 72, 95, 60, 40, 78, 90, 55, 35, 60, 42, 25, 18];
        rbars.forEach((rbh, rbi) => {
          const bx = 40 + (rbi * 15);
          const by = 260 + (95 - rbh);
          roundRect(ctx, bx, by, 7, rbh, 3.5);
          ctx.fillStyle = (rbi % 2 === 0) ? accent : '#ECE8DD';
          ctx.globalAlpha = (rbi % 2 === 0) ? 0.9 : 0.65;
          ctx.fill();
        });
        // Dial frequency pill
        roundRect(ctx, 80, 365, 190, 32, 16);
        ctx.fillStyle = 'rgba(20, 18, 14, 0.85)';
        ctx.fill();
        ctx.strokeStyle = accent;
        ctx.lineWidth = 1;
        ctx.globalAlpha = 0.6;
        ctx.stroke();
        ctx.beginPath();
        ctx.arc(102, 381, 4, 0, Math.PI * 2);
        ctx.fillStyle = accent;
        ctx.globalAlpha = 1;
        ctx.fill();
        ctx.font = "bold 11px 'Plus Jakarta Sans', sans-serif";
        ctx.fillStyle = '#ECE8DD';
        ctx.fillText('LIVE STREAM • 94.2 FM', 114, 385);
        ctx.restore();
      } else if (effectiveGraphic === 'ghost') {
        ctx.save();
        ctx.translate(750, 130);
        // Outer Talisman Ring
        ctx.beginPath();
        ctx.arc(160, 170, 140, 0, Math.PI * 2);
        ctx.strokeStyle = accent;
        ctx.lineWidth = 2;
        ctx.setLineDash([8, 8]);
        ctx.globalAlpha = 0.5;
        ctx.stroke();
        ctx.setLineDash([]);
        // Middle ring
        ctx.beginPath();
        ctx.arc(160, 170, 110, 0, Math.PI * 2);
        ctx.strokeStyle = '#ECE8DD';
        ctx.lineWidth = 1.5;
        ctx.globalAlpha = 0.25;
        ctx.stroke();
        // Inner Void
        ctx.beginPath();
        ctx.arc(160, 170, 80, 0, Math.PI * 2);
        const gGrad = ctx.createRadialGradient(160, 170, 10, 160, 170, 80);
        gGrad.addColorStop(0, hexToRgba(accent, 0.5));
        gGrad.addColorStop(1, 'rgba(10, 8, 8, 0.85)');
        ctx.fillStyle = gGrad;
        ctx.fill();
        ctx.strokeStyle = accent;
        ctx.lineWidth = 3;
        ctx.stroke();
        // Inverted Triangle
        ctx.beginPath();
        ctx.moveTo(160, 215); ctx.lineTo(125, 145); ctx.lineTo(195, 145);
        ctx.closePath();
        ctx.strokeStyle = '#ECE8DD';
        ctx.lineWidth = 2.5;
        ctx.globalAlpha = 0.7;
        ctx.stroke();
        // Center Eye
        ctx.beginPath();
        ctx.arc(160, 170, 8, 0, Math.PI * 2);
        ctx.fillStyle = accent;
        ctx.globalAlpha = 1;
        ctx.fill();
        // Floating Embers
        const embers = [
          [50, 80, 3, 0.6], [80, 40, 2, 0.8], [240, 60, 3.5, 0.7], [280, 110, 2, 0.5],
          [300, 180, 2.5, 0.9], [270, 250, 4, 0.6], [220, 290, 2, 0.8], [70, 270, 3, 0.7],
          [40, 200, 2.5, 0.5], [110, 90, 2, 0.9], [220, 90, 1.5, 0.7], [160, 40, 3, 0.85]
        ];
        embers.forEach(em => {
          ctx.beginPath();
          ctx.arc(em[0], em[1], em[2], 0, Math.PI * 2);
          ctx.fillStyle = accent;
          ctx.globalAlpha = em[3];
          ctx.fill();
        });
        ctx.restore();
      } else if (effectiveGraphic === 'monolith') {
        ctx.save();
        ctx.translate(720, 120);
        ctx.font = "900 150px 'Archivo Black', sans-serif";
        ctx.fillStyle = '#ECE8DD';
        ctx.globalAlpha = 0.08;
        ctx.fillText('TNM.', 40, 240);
        ctx.font = "900 90px 'Archivo Black', sans-serif";
        ctx.fillStyle = accent;
        ctx.globalAlpha = 0.45;
        ctx.fillText('“', 30, 100);
        ctx.strokeStyle = '#ECE8DD';
        ctx.lineWidth = 1.5;
        ctx.globalAlpha = 0.35;
        const cross = [[30, 40], [310, 40], [30, 280], [310, 280], [170, 160]];
        cross.forEach(ch => {
          ctx.beginPath(); ctx.moveTo(ch[0] - 8, ch[1]); ctx.lineTo(ch[0] + 8, ch[1]); ctx.stroke();
          ctx.beginPath(); ctx.moveTo(ch[0], ch[1] - 8); ctx.lineTo(ch[0], ch[1] + 8); ctx.stroke();
        });
        ctx.beginPath(); ctx.moveTo(30, 280); ctx.lineTo(310, 280); ctx.stroke();
        for (let t = 30; t <= 310; t += 20) {
          const th = (t % 40 === 0) ? 8 : 4;
          ctx.beginPath(); ctx.moveTo(t, 280); ctx.lineTo(t, 280 - th); ctx.stroke();
        }
        ctx.restore();
      }

      // 7. GAMBAR BLOK TEKS (KIRI)
      ctx.save();
      ctx.globalAlpha = 1.0;

      // Cek apakah Badge Kategori diisi atau sengaja dikosongkan
      const badgeText = (studioState.badge || '').trim();
      let curY = 150; // Posisi vertikal judul jika badge tidak ada

      if (badgeText.length > 0) {
        // Gambar Pill Badge hanya jika teks badge tidak kosong
        ctx.font = "bold 13px 'Plus Jakarta Sans', sans-serif";
        const bWidth = Math.max(100, ctx.measureText(badgeText.toUpperCase()).width + 32);
        roundRect(ctx, 90, 95, bWidth, 34, 17);
        ctx.fillStyle = hexToRgba(accent, 0.18);
        ctx.fill();
        ctx.strokeStyle = accent;
        ctx.lineWidth = 1.5;
        ctx.stroke();

        ctx.fillStyle = accent;
        ctx.textAlign = 'center';
        ctx.fillText(badgeText.toUpperCase(), 90 + (bWidth / 2), 117);
        ctx.textAlign = 'left';

        curY = 195; // Posisi vertikal judul jika ada badge
      }

      // Judul Utama (Main Title)
      const rawTitle = (studioState.title || '').trim() || 'JUDUL CATATAN TNM';
      let titleSize = 42;
      if (studioState.fontSize === 'compact') titleSize = 32;
      else if (studioState.fontSize === 'medium') titleSize = 38;
      else if (studioState.fontSize === 'large') titleSize = 46;
      else {
        if (rawTitle.length > 50) titleSize = 32;
        else if (rawTitle.length > 30) titleSize = 38;
        else titleSize = 44;
      }

      ctx.font = `bold ${titleSize}px 'Archivo Black', sans-serif`;
      ctx.fillStyle = '#ECE8DD';
      const tLines = wrapText(ctx, rawTitle.toUpperCase(), 610);
      tLines.slice(0, 3).forEach(tl => {
        ctx.fillText(tl, 90, curY);
        curY += (titleSize * 1.18);
      });

      // Subjudul Aksen Warna (Subtitle / Highlight)
      const subText = (studioState.subtitle || '').trim();
      if (subText.length > 0) {
        curY += 8;
        const subSize = Math.max(20, Math.min(titleSize - 8, 28));
        ctx.font = `800 ${subSize}px 'Plus Jakarta Sans', sans-serif`;
        ctx.fillStyle = accent;
        const sLines = wrapText(ctx, subText, 610);
        sLines.slice(0, 2).forEach(sl => {
          ctx.fillText(sl, 90, curY);
          curY += (subSize * 1.22);
        });
      }

      // Kutipan / Excerpt Singkat (Hook cerita)
      const quoteText = (studioState.quote || '').trim();
      if (quoteText.length > 0) {
        // Berikan jarak vertikal yang lebih lega dan turun ke bawah
        // Selaras dengan template SVG original (blog-dibalik-layar.svg dsb) di Y = 345
        const minQuoteY = (badgeText.length > 0) ? 345 : 320;
        curY = Math.max(curY + 38, minQuoteY);

        if (curY <= 455) {
          ctx.font = "400 18.5px 'Plus Jakarta Sans', sans-serif";
          ctx.fillStyle = '#A8A294';
          const qLines = wrapText(ctx, quoteText, 600);
          const maxQLines = Math.min(2, Math.floor((485 - curY) / 30));
          qLines.slice(0, Math.max(1, maxQLines)).forEach(ql => {
            ctx.fillText(ql, 90, curY);
            curY += 30;
          });
        }
      }

      // Baris Penulis & Tagline (Posisikan rapi di y=515)
      ctx.save();
      ctx.translate(90, 515);
      // Avatar Circle
      ctx.beginPath();
      ctx.arc(20, 20, 20, 0, Math.PI * 2);
      ctx.fillStyle = '#231F19';
      ctx.fill();
      ctx.strokeStyle = '#ECE8DD';
      ctx.lineWidth = 1;
      ctx.globalAlpha = 0.25;
      ctx.stroke();

      // Inisial Penulis
      ctx.font = "bold 15px 'Plus Jakarta Sans', sans-serif";
      ctx.fillStyle = '#ECE8DD';
      ctx.textAlign = 'center';
      ctx.globalAlpha = 1;
      const initial = (studioState.author || 'A').charAt(0).toUpperCase();
      ctx.fillText(initial, 20, 25);
      ctx.textAlign = 'left';

      // Nama Penulis & Tagline
      ctx.font = "bold 15.5px 'Plus Jakarta Sans', sans-serif";
      ctx.fillStyle = '#ECE8DD';
      ctx.fillText(studioState.author || 'Andri TNM', 54, 18);

      ctx.font = "400 13px 'Plus Jakarta Sans', sans-serif";
      ctx.fillStyle = '#857F72';
      ctx.fillText(studioState.tagline || 'Host & Kurator Cerita', 54, 36);
      ctx.restore();

      ctx.restore();

      // 8. GAMBAR BINGKAI FRAME
      if (studioState.frame === 'double') {
        roundRect(ctx, 24, 24, 1152, 582, 28);
        ctx.strokeStyle = '#ECE8DD';
        ctx.lineWidth = 1.5;
        ctx.globalAlpha = 0.08;
        ctx.stroke();

        roundRect(ctx, 26, 26, 1148, 578, 26);
        ctx.strokeStyle = accent;
        ctx.lineWidth = 1;
        ctx.globalAlpha = 0.25;
        ctx.stroke();
      } else if (studioState.frame === 'single') {
        roundRect(ctx, 24, 24, 1152, 582, 24);
        ctx.strokeStyle = accent;
        ctx.lineWidth = 1.5;
        ctx.globalAlpha = 0.35;
        ctx.stroke();
      } else if (studioState.frame === 'viewfinder') {
        ctx.strokeStyle = accent;
        ctx.lineWidth = 3;
        ctx.globalAlpha = 0.6;
        // Top-Left
        ctx.beginPath(); ctx.moveTo(32, 60); ctx.lineTo(32, 32); ctx.lineTo(60, 32); ctx.stroke();
        // Top-Right
        ctx.beginPath(); ctx.moveTo(1140, 32); ctx.lineTo(1168, 32); ctx.lineTo(1168, 60); ctx.stroke();
        // Bottom-Left
        ctx.beginPath(); ctx.moveTo(32, 570); ctx.lineTo(32, 598); ctx.lineTo(60, 598); ctx.stroke();
        // Bottom-Right
        ctx.beginPath(); ctx.moveTo(1140, 598); ctx.lineTo(1168, 598); ctx.lineTo(1168, 570); ctx.stroke();
      }

      // Pastikan globalAlpha di-reset kembali ke 1.0 di akhir render
      ctx.globalAlpha = 1.0;
    }

    /* APPLY GENERATED THUMBNAIL TO MAIN FORM */
    function applyThumbnailToArticle() {
      const canvas = document.getElementById('tnmThumbCanvas');
      if (!canvas) return;

      const dataUrl = canvas.toDataURL('image/png');
      const canvasInput = document.getElementById('coverCanvasData');
      const metaInput = document.getElementById('coverMetaJson');
      const previewImg = document.getElementById('coverPreviewImg');
      const statusPill = document.getElementById('customCoverStatusPill');
      const noticeBox = document.getElementById('coverNoticeBox');
      const studioBtnLabel = document.getElementById('studioBtnLabel');

      if (canvasInput) canvasInput.value = dataUrl;
      if (metaInput) metaInput.value = JSON.stringify(studioState);
      if (previewImg) previewImg.src = dataUrl;

      if (statusPill) {
        statusPill.textContent = ' Template Custom (Siap Simpan)';
        statusPill.style.color = '#2ecc71';
        statusPill.style.background = 'rgba(46,204,113,0.15)';
        statusPill.style.borderColor = 'rgba(46,204,113,0.3)';
      }
      if (noticeBox) {
        noticeBox.style.display = 'block';
        noticeBox.textContent = ' Thumbnail Studio berhasil diterapkan! Jangan lupa klik "Terbitkan" / "Perbarui" artikel di atas untuk menyimpan.';
      }
      if (studioBtnLabel) {
        studioBtnLabel.textContent = 'Sunting Thumbnail di Studio ';
      }

      // Show alert inside studio then close
      const alertBox = document.getElementById('studioAlertMsg');
      if (alertBox) {
        alertBox.style.display = 'block';
        alertBox.textContent = ' Cover berhasil diterapkan ke form artikel!';
        setTimeout(() => {
          alertBox.style.display = 'none';
          closeThumbnailStudio();
        }, 600);
      } else {
        closeThumbnailStudio();
      }
    }

    /* DOWNLOAD PNG (1200x630) */
    function downloadThumbnailPng() {
      const canvas = document.getElementById('tnmThumbCanvas');
      if (!canvas) return;
      const dataUrl = canvas.toDataURL('image/png');
      const slugVal = document.getElementById('articleSlug')?.value || 'tnm-cover';
      const a = document.createElement('a');
      a.href = dataUrl;
      a.download = `cover-${slugVal}.png`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
    }

    // Keyboard shortcut: Escape closes modal
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        const modal = document.getElementById('thumbnailStudioModal');
        if (modal && modal.style.display === 'flex') {
          closeThumbnailStudio();
        }
      }
    });
  </script>

<?php else: ?>
  <!-- ================= LIST ARTICLES TABLE ================= -->

  <!-- Filter Bar -->
  <div style="background:var(--surface); border:1px solid var(--ring); border-radius:var(--radius-md); padding:14px 18px; margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
      <span style="font-size:12.5px; font-weight:700; color:var(--muted);">Filter Kategori:</span>
      <a href="articles.php" class="btn sm <?= $categoryFilter === 'all' ? 'red' : 'ghost' ?>" style="padding:4px 10px; font-size:12px;">
        Semua (<?= count(getAllPosts('all', '', true)) ?>)
      </a>
      <?php foreach ($categories as $cat): 
        $catCount = count(getAllPosts($cat, '', true));
      ?>
        <a href="articles.php?cat=<?= urlencode($cat) ?>" class="btn sm <?= $categoryFilter === $cat ? 'red' : 'ghost' ?>" style="padding:4px 10px; font-size:12px;">
          <?= e($cat) ?> (<?= $catCount ?>)
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Search Form -->
    <form action="articles.php" method="GET" style="display:flex; gap:6px;">
      <?php if ($categoryFilter !== 'all'): ?>
        <input type="hidden" name="cat" value="<?= e($categoryFilter) ?>">
      <?php endif; ?>
      <input type="text" name="q" value="<?= e($searchTerm) ?>" placeholder="Cari judul catatan..."
             style="background:var(--bg); border:1px solid var(--ring); border-radius:var(--radius-sm); padding:6px 12px; font-size:12.5px; color:var(--ink); outline:none;">
      <button type="submit" class="btn sm ghost" style="padding:6px 12px;">Cari</button>
    </form>
  </div>

  <div class="admin-table-container">
    <?php if (empty($allPosts)): ?>
      <div style="padding: 48px; text-align: center; color: var(--muted);">
        <p style="font-size:15px; margin-bottom:12px;">Belum ada artikel catatan yang ditemukan.</p>
        <a href="articles.php?action=create" class="btn red sm">Mulai Tulis Catatan Baru</a>
      </div>
    <?php else: ?>
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width:70px;">Cover</th>
            <th>Judul & Ringkasan</th>
            <th>Kategori</th>
            <th>Penulis</th>
            <th>Status</th>
            <th>Views</th>
            <th>Tanggal</th>
            <th style="text-align:right;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($allPosts as $p): ?>
            <tr>
              <td>
                <img src="<?= e(blogAsset($p['cover_image'])) ?>" alt="" 
                     style="width:54px; height:38px; object-fit:cover; border-radius:6px; border:1px solid var(--ring); background:var(--shell); display:block;">
              </td>
              <td style="max-width:320px;">
                <b style="color:var(--ink); font-size:13.5px; display:block; margin-bottom:3px;">
                  <?= e($p['title']) ?>
                </b>
                <span style="color:var(--muted); font-size:12px; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                  <?= e($p['excerpt'] ?: substr(strip_tags($p['content']), 0, 70) . '...') ?>
                </span>
              </td>
              <td>
                <span style="font-size:12px; font-weight:700; color:var(--ink); background:var(--shell); padding:3px 8px; border-radius:6px; border:1px solid var(--ring); white-space:nowrap;">
                  <?= e($p['category']) ?>
                </span>
              </td>
              <td style="font-size:12px; white-space:nowrap;"><?= e($p['author']) ?></td>
              <td>
                <span class="status-badge <?= $p['status'] === 'published' ? 'completed' : 'pending' ?>">
                  <?= $p['status'] === 'published' ? 'Diterbitkan' : 'Draf' ?>
                </span>
              </td>
              <td>
                <span style="font-weight:700; color:var(--ink); font-size:12.5px;">
                  <?= number_format($p['views'] ?? 0) ?>
                </span>
              </td>
              <td style="font-size:12px; white-space:nowrap;">
                <?= formatBlogDate($p['created_at']) ?>
              </td>
              <td style="text-align:right; white-space:nowrap;">
                <a href="../../blog.php?slug=<?= urlencode($p['slug']) ?>" target="_blank" class="btn sm ghost" style="padding:4px 8px; font-size:12px;" title="Lihat di Blog">
                  Lihat ↗
                </a>
                <a href="articles.php?action=edit&edit=<?= $p['id'] ?>" class="btn sm ghost" style="padding:4px 10px; font-size:12px;">
                  Sunting
                </a>
                <a href="articles.php?delete=<?= $p['id'] ?>&csrf_token=<?= urlencode(getMarketplaceCsrfToken()) ?>" class="btn sm ghost" style="padding:4px 8px; font-size:12px; color:var(--red-ink);" 
                   onclick="return confirm('Apakah Anda yakin ingin menghapus catatan ini?')">
                  Hapus
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
