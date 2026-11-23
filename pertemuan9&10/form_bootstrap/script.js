(function () {
  const p = new URLSearchParams(window.location.search);
  const status = p.get('status');
  if (!status) return;

  if (status === 'sukses') {
    alert('✅ BERHASIL! Akun kamu sudah tersimpan di database.');
  } else if (status === 'error') {
    alert('❌ GAGAL!\n' + (p.get('msg') || 'Terjadi kesalahan.'));
  }
  window.history.replaceState({}, document.title, window.location.pathname);
})(); 