/* ===== NIVICO Electronic Mart - app.js ===== */

// ── TOAST ──
function toast(msg) {
  const t = document.getElementById('toast');
  if (!t) return;
  t.textContent = msg;
  t.style.opacity = '1';
  clearTimeout(t._t);
  t._t = setTimeout(() => (t.style.opacity = '0'), 2400);
}

// ── HERO SLIDER ──
(function () {
  const sls = document.querySelectorAll('.sl');
  const sds = document.querySelectorAll('.sd');
  if (!sls.length) return;

  let idx = 0;
  function goSl(n) {
    sls[idx].classList.remove('on');
    sds[idx] && sds[idx].classList.remove('on');
    idx = (n + sls.length) % sls.length;
    sls[idx].classList.add('on');
    sds[idx] && sds[idx].classList.add('on');
  }
  window.goSl = goSl;
  window.nextSl = () => goSl(idx + 1);
  window.prevSl = () => goSl(idx - 1);
  setInterval(window.nextSl, 4500);
})();

// ── COUNTDOWN FLASH SALE (target waktu dari data-ends) ──
(function () {
  const h = document.getElementById('cdH');
  const m = document.getElementById('cdM');
  const s = document.getElementById('cdS');
  if (!h || !m || !s) return;

  const wrap = h.closest('.cd');
  const ends = wrap && wrap.dataset.ends ? new Date(wrap.dataset.ends).getTime() : null;

  function tick() {
    let secs;
    if (ends) {
      secs = Math.floor((ends - Date.now()) / 1000);
      if (secs <= 0) { h.textContent = m.textContent = s.textContent = '00'; return; }
    } else {
      return;
    }
    h.textContent = String(Math.floor(secs / 3600)).padStart(2, '0');
    m.textContent = String(Math.floor((secs % 3600) / 60)).padStart(2, '0');
    s.textContent = String(secs % 60).padStart(2, '0');
  }
  tick();
  setInterval(tick, 1000);
})();

// ── DETAIL: ganti gambar utama dari thumbnail ──
function swImg(src, el) {
  const main = document.getElementById('det-img');
  if (main) main.src = src;
  document.querySelectorAll('.det-th').forEach((t) => t.classList.remove('on'));
  el.classList.add('on');
}

// ── DETAIL: qty stepper ──
function chQty(d) {
  const e = document.getElementById('qty-v');
  if (!e) return;
  const max = parseInt(e.max || 9999);
  e.value = Math.min(max, Math.max(1, parseInt(e.value || 1) + d));
}

// ── DETAIL: pemilih varian ──
function applyVariant(data, el) {
  if (!data) return;
  document.querySelectorAll('.var-opt').forEach((b) => b.classList.remove('on'));
  if (el) el.classList.add('on');

  const fmt = (n) => 'Rp' + Number(n).toLocaleString('id-ID');

  const priceEl = document.getElementById('det-price');
  if (priceEl) priceEl.textContent = fmt(data.price);
  const rangeEl = document.getElementById('det-price-range');
  if (rangeEl) rangeEl.style.display = 'none';

  const oldEl = document.getElementById('det-old');
  const discEl = document.getElementById('det-disc');
  if (oldEl && discEl) {
    if (data.old && data.disc) {
      oldEl.textContent = fmt(data.old);
      oldEl.style.display = '';
      discEl.textContent = '-' + data.disc + '%';
      discEl.style.display = '';
    } else {
      oldEl.style.display = 'none';
      discEl.style.display = 'none';
    }
  }

  const stockEl = document.getElementById('det-stock');
  if (stockEl) {
    stockEl.innerHTML =
      data.stock > 0
        ? '\u2713 Stok Tersedia (' + data.stock + ')'
        : '<span style="color:var(--red)">\u2717 Stok Habis</span>';
  }

  if (data.image) {
    const main = document.getElementById('det-img');
    if (main) main.src = data.image;
  }

  const hid = document.getElementById('sel-variant-id');
  if (hid) hid.value = data.id;
  const qty = document.getElementById('qty-v');
  if (qty) {
    qty.max = Math.max(1, data.stock);
    if (parseInt(qty.value) > data.stock) qty.value = Math.max(1, data.stock);
  }
  const beli = document.getElementById('btn-beli');
  const cart = document.getElementById('btn-cart');
  const soldOut = data.stock < 1;
  if (beli) beli.disabled = soldOut;
  if (cart) cart.disabled = soldOut;
  const warn = document.getElementById('var-warn');
  if (warn) warn.style.display = 'none';
}

// Pasang listener via data-vid (lebih robust daripada onclick inline)
document.addEventListener('DOMContentLoaded', function () {
  const opts = document.getElementById('var-opts');
  if (!opts) return;
  opts.querySelectorAll('.var-opt').forEach(function (btn) {
    if (btn.disabled) return;
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const vid = parseInt(btn.getAttribute('data-vid'));
      const data = (window.__variantData || []).find((v) => Number(v.id) === vid);
      applyVariant(data, btn);
    });
  });
});

// Guard: produk bervarian wajib pilih varian sebelum submit
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('det-form');
  if (!form || !window.__hasVariants) return;
  form.addEventListener('submit', function (e) {
    const vid = document.getElementById('sel-variant-id');
    if (!vid || !vid.value) {
      e.preventDefault();
      const warn = document.getElementById('var-warn');
      if (warn) warn.style.display = 'block';
      const opts = document.getElementById('var-opts');
      if (opts) opts.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  });
});

// ── PASSWORD TOGGLE ──
function togglePw(id) {
  const e = document.getElementById(id);
  if (e) e.type = e.type === 'password' ? 'text' : 'password';
}

// ── PROMO TABS (di halaman promo cukup link, tab visual saja) ──
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.ptab[data-tab]').forEach((t) => {
    t.addEventListener('click', function () {
      window.location.href = this.dataset.url;
    });
  });
});

// ── POPUP PROMO (muncul sekali per sesi browser tab) ──
document.addEventListener('DOMContentLoaded', function () {
  const overlay = document.getElementById('popup-overlay');
  if (!overlay) return;

  // tampil hanya di beranda & belum pernah ditutup di sesi ini
  if (!window.__popupShown && !sessionStorageGet('nivico_popup_closed')) {
    setTimeout(() => overlay.classList.add('show'), 800);
  }
  overlay.addEventListener('click', function (e) {
    if (e.target === this) window.closePopup();
  });
});

function sessionStorageGet(k) {
  try { return window.sessionStorage.getItem(k); } catch (e) { return null; }
}
function sessionStorageSet(k, v) {
  try { window.sessionStorage.setItem(k, v); } catch (e) {}
}

function closePopup() {
  const overlay = document.getElementById('popup-overlay');
  if (overlay) overlay.classList.remove('show');
  sessionStorageSet('nivico_popup_closed', '1');
}
