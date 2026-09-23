<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk Sistem — SIM-PEP Dinas Pendidikan</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>
<body style="background:#edf2f7;">

<div class="login-wrap">
  <div class="login-card">
    
    <div class="login-header">
      <span class="login-badge">PEP</span>
      <h1>Sistem Informasi Manajemen Dokumen</h1>
      <p>Perencanaan, Evaluasi, dan Pelaporan (PEP)</p>
    </div>

    <div class="login-body">
      @if (session('success'))
        <div style="background:#dcfce7;border:1px solid #86efac;color:#166534;padding:10px 14px;border-radius:8px;font-size:12px;margin-bottom:14px;font-weight:600;">
          {{ session('success') }}
        </div>
      @endif

      @if ($errors->any())
        <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:10px 14px;border-radius:8px;font-size:12px;margin-bottom:14px;font-weight:600;">
          {{ $errors->first() }}
        </div>
      @endif

      <form action="{{ route('login.post') }}" method="POST">
        @csrf
        
        <div class="form-group">
          <label>Pilih Peran Akses <span class="req">*</span></label>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:4px;">
            <label style="border:1px solid var(--border);padding:10px 12px;border-radius:4px;cursor:pointer;display:flex;flex-direction:column;gap:4px;background:#f8fafc;" id="roleCardAdmin">
              <div style="display:flex;align-items:center;gap:6px;">
                <input type="radio" name="loginRole" value="admin" checked onchange="updateRoleUi()">
                <strong style="font-size:12px;color:var(--text-dark);">Administrator</strong>
              </div>
              <span style="font-size:11px;color:var(--text-muted);padding-left:20px;">Kelola & Import/Export PDF (Sidebar Kiri)</span>
            </label>

            <label style="border:1px solid var(--border);padding:10px 12px;border-radius:4px;cursor:pointer;display:flex;flex-direction:column;gap:4px;background:#fff;" id="roleCardUser">
              <div style="display:flex;align-items:center;gap:6px;">
                <input type="radio" name="loginRole" value="user" onchange="updateRoleUi()">
                <strong style="font-size:12px;color:var(--text-dark);">Pegawai / User</strong>
              </div>
              <span style="font-size:11px;color:var(--text-muted);padding-left:20px;">Portal Dokumen Baca-Saja (Navbar Atas)</span>
            </label>
          </div>
        </div>

        <div class="form-group">
          <label>Alamat Email / NIP Pegawai</label>
          <input 
            type="email" 
            name="email"
            class="form-control" 
            id="loginEmail" 
            value="{{ old('email', 'admin@pep.go.id') }}" 
            required 
            style="width:100%;"
          >
        </div>

        <div class="form-group">
          <label>Kata Sandi</label>
          <div style="position:relative;">
            <input 
            type="password" 
            name="password"
            class="form-control" 
            id="loginPassword" 
            value="password123" 
            required 
            style="width:100%;padding-right:60px;"
          >
          <button 
            type="button" 
            onclick="togglePassword()" 
            style="position:absolute;right:8px;top:50%;transform:translateY(-50%);font-size:11px;font-weight:700;color:var(--text-muted);border:none;background:none;cursor:pointer;"
          >
            LIHAT
          </button>
        </div>
      </div>

      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;font-size:12px;">
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;color:var(--text-muted);">
          <input type="checkbox" name="remember" value="1">
          <span>Ingat Sesi Ini</span>
        </label>
        <span style="font-size:11px;color:#94a3b8;">T.A. 2026 Aktif</span>
      </div>

      <button type="submit" class="btn btn-primary" id="btnLoginSubmit" style="width:100%;padding:10px;font-size:13px;font-weight:700;">
        Masuk ke Workspace Admin
      </button>

      </form>

      <div style="margin-top:20px;padding:12px;background:#f7fafc;border:1px dashed var(--border);border-radius:4px;font-size:12px;color:var(--text-muted);text-align:center;">
        <strong style="color:var(--text-dark);display:block;margin-bottom:2px;">Akses Prototype Sistem PEP</strong>
        Pilih <strong>Administrator</strong> untuk ke Workspace Admin (Sidebar Kiri)<br>
        atau pilih <strong>Pegawai / User</strong> untuk ke Portal Pengguna (Navbar Atas).
      </div>
    </div>

  </div>
</div>

<script>
function togglePassword() {
  const pwd = document.getElementById('loginPassword');
  if (!pwd) return;
  pwd.type = pwd.type === 'password' ? 'text' : 'password';
}

function updateRoleUi() {
  const selectedRole = document.querySelector('input[name="loginRole"]:checked')?.value || 'admin';
  const btn = document.getElementById('btnLoginSubmit');
  const cardAdmin = document.getElementById('roleCardAdmin');
  const cardUser = document.getElementById('roleCardUser');
  const emailInput = document.getElementById('loginEmail');

  if (selectedRole === 'admin') {
    btn.textContent = 'Masuk ke Workspace Admin';
    cardAdmin.style.background = '#ebf8ff';
    cardAdmin.style.borderColor = 'var(--primary)';
    cardUser.style.background = '#fff';
    cardUser.style.borderColor = 'var(--border)';
    if (emailInput.value === 'user@pep.go.id') emailInput.value = 'admin@pep.go.id';
  } else {
    btn.textContent = 'Masuk ke Portal Pengguna';
    cardUser.style.background = '#ebf8ff';
    cardUser.style.borderColor = 'var(--primary)';
    cardAdmin.style.background = '#fff';
    cardAdmin.style.borderColor = 'var(--border)';
    if (emailInput.value === 'admin@pep.go.id') emailInput.value = 'user@pep.go.id';
  }
}

// Pasang indikator loading saat submit
document.querySelector('form')?.addEventListener('submit', function () {
  const btn = document.getElementById('btnLoginSubmit');
  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Memverifikasi Akun...';
  }
});

// Inisialisasi tampilan peran
updateRoleUi();
</script>

</body>
</html>