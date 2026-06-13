@extends('layouts.app')
@section('title', 'Checkout — NIVICO')

@section('content')
<div class="co-wrap">
  <h2 style="font-family:'DM Serif Display',serif;font-size:22px;margin-bottom:18px">Checkout</h2>

  <form method="POST" action="{{ route('checkout.store') }}" id="checkout-form">
    @csrf
    <input type="hidden" name="destination_id" id="destination_id" value="{{ old('destination_id') }}">
    <input type="hidden" name="shipping_option" id="shipping_option" value="{{ old('shipping_option') }}">

    <div class="co-grid">
      <div>
        <div class="co-card"><h3>📍 Alamat Pengiriman</h3>
          <div class="fr">
            <div class="fg"><label>Nama Lengkap</label><input type="text" name="recipient_name" value="{{ old('recipient_name', auth()->user()->name ?? '') }}" placeholder="Nama lengkap penerima" required></div>
            <div class="fg"><label>No. Telepon</label><input type="text" name="phone" value="{{ old('phone', auth()->user()->phone ?? '') }}" placeholder="08xx-xxxx-xxxx" required></div>
          </div>
          <div class="fr full"><div class="fg"><label>Alamat Lengkap</label><textarea name="address" placeholder="Jl. Contoh No. 123, RT/RW, Kelurahan..." required>{{ old('address') }}</textarea></div></div>
          <div class="fr">
            <div class="fg"><label>Kota / Kecamatan Tujuan</label>
              <input type="text" id="dest_search" placeholder="Ketik min. 3 huruf, mis. Surabaya..." value="{{ old('city') }}" autocomplete="off">
              <div class="dest-results" id="dest_results"></div>
            </div>
            <div class="fg"><label>Kode Pos</label><input type="text" name="postal_code" value="{{ old('postal_code') }}" placeholder="60xxx"></div>
          </div>
          <input type="hidden" name="city" id="city_hidden" value="{{ old('city') }}">
          <input type="hidden" name="province" id="province_hidden" value="{{ old('province') }}">
          <input type="hidden" name="district" id="district_hidden" value="{{ old('district') }}">
        </div>

        <div class="co-card"><h3>🚚 Pilih Pengiriman</h3>
          <div id="ship-placeholder" style="font-size:13px;color:var(--muted)">Pilih kota tujuan terlebih dahulu untuk melihat opsi &amp; tarif pengiriman.</div>
          <div class="ship-loading" id="ship-loading" style="display:none">Menghitung ongkir...</div>
          <div class="radio-grp" id="ship-options"></div>
        </div>

        <div class="co-card"><h3>💳 Metode Pembayaran</h3>
          <div class="radio-grp">
            @if($midtransOn)
            <label class="radio-opt"><input type="radio" name="payment_gateway" value="midtrans" {{ old('payment_gateway')==='midtrans' ? 'checked' : '' }}><span class="ro-ico">⚡</span><div class="ro-inf"><strong>Pembayaran Otomatis (Midtrans)</strong><span>Kartu, e-wallet, VA bank, QRIS</span></div></label>
            @endif
            <label class="radio-opt"><input type="radio" name="payment_gateway" value="manual_transfer" {{ old('payment_gateway','manual_transfer')==='manual_transfer' ? 'checked' : '' }}><span class="ro-ico">🏦</span><div class="ro-inf"><strong>Transfer Bank Manual</strong><span>Transfer lalu unggah bukti</span></div></label>
          </div>

          <div id="bank-picker" style="margin-top:12px">
            <label style="font-size:12px;font-weight:600;color:var(--muted)">Pilih Bank Tujuan</label>
            <div class="radio-grp" style="margin-top:6px">
              @forelse($banks as $i => $bank)
                <label class="radio-opt"><input type="radio" name="bank_account_id" value="{{ $bank->id }}" {{ $i===0 ? 'checked' : '' }}><span class="ro-ico">🏦</span><div class="ro-inf"><strong>{{ $bank->bank_name }}</strong><span>{{ $bank->account_number }} — a.n. {{ $bank->account_holder }}</span></div></label>
              @empty
                <div style="font-size:12.5px;color:var(--muted)">Belum ada rekening bank. Admin dapat menambahkannya di panel admin.</div>
              @endforelse
            </div>
          </div>
        </div>

        <div class="co-card"><h3>📝 Catatan Penjual</h3><div class="fg"><textarea name="note" placeholder="Catatan tambahan untuk penjual (opsional)...">{{ old('note') }}</textarea></div></div>
      </div>

      <div>
        <div class="sum-box" style="position:sticky;top:80px">
          <div class="sum-ttl">Ringkasan Pesanan</div>
          <div id="co-items-list">
            @foreach($cart->items as $it)
              <div class="co-item">
                <div class="co-iimg"><img src="{{ $it->product->image }}"></div>
                <div class="co-iname">{{ $it->product->name }} x{{ $it->qty }}</div>
                <div class="co-iprice">Rp{{ number_format($it->product->price * $it->qty, 0, ',', '.') }}</div>
              </div>
            @endforeach
          </div>
          <div class="sum-row" style="margin-top:10px"><span>Subtotal</span><span>Rp{{ number_format($subtotal, 0, ',', '.') }}</span></div>
          <div class="sum-row"><span>Berat Total</span><span>{{ number_format($weight) }} gram</span></div>
          <div class="sum-row"><span>Ongkos Kirim</span><span id="sum-ship">—</span></div>
          <div class="sum-row"><span>Diskon</span><span style="color:var(--green)">−Rp{{ number_format($discount, 0, ',', '.') }}</span></div>
          <div class="sum-row tot"><span>Total Bayar</span><span id="sum-total">Rp{{ number_format($subtotal - $discount, 0, ',', '.') }}</span></div>
          <button class="btn-bayar" type="submit" id="btn-checkout">🔒 Buat Pesanan</button>
          <p style="font-size:11px;color:var(--muted);text-align:center;margin-top:10px">Dengan menekan tombol, Anda menyetujui Syarat &amp; Ketentuan NIVICO</p>
        </div>
      </div>
    </div>
  </form>
</div>

@push('scripts')
<script>
(function(){
  const SUBTOTAL = {{ $subtotal }};
  const DISCOUNT = {{ $discount }};
  const csrf = document.querySelector('meta[name=csrf-token]').content;

  const search = document.getElementById('dest_search');
  const results = document.getElementById('dest_results');
  const destId = document.getElementById('destination_id');
  const shipWrap = document.getElementById('ship-options');
  const shipPlaceholder = document.getElementById('ship-placeholder');
  const shipLoading = document.getElementById('ship-loading');
  const sumShip = document.getElementById('sum-ship');
  const sumTotal = document.getElementById('sum-total');
  const shipOption = document.getElementById('shipping_option');

  const rupiah = n => 'Rp' + Number(n).toLocaleString('id-ID');

  // ── cari tujuan ──
  let t;
  search.addEventListener('input', function(){
    clearTimeout(t);
    const q = this.value.trim();
    if(q.length < 3){ results.classList.remove('show'); return; }
    t = setTimeout(()=>findDest(q), 350);
  });

  function findDest(q){
    fetch(@json(route('checkout.destination')), {
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
      body: JSON.stringify({q})
    }).then(r=>r.json()).then(rows=>{
      results.innerHTML='';
      if(!rows.length){ results.innerHTML='<div class="dest-item" style="color:#9ca3af">Tidak ditemukan</div>'; results.classList.add('show'); return; }
      rows.forEach(row=>{
        const d=document.createElement('div');
        d.className='dest-item'; d.textContent=row.label;
        d.onclick=()=>pickDest(row);
        results.appendChild(d);
      });
      results.classList.add('show');
    }).catch(()=>{ results.classList.remove('show'); });
  }

  function pickDest(row){
    search.value = row.label;
    destId.value = row.id;
    document.getElementById('city_hidden').value = row.label;
    results.classList.remove('show');
    loadShipping(row.id);
  }

  // ── hitung ongkir ──
  function loadShipping(id){
    shipPlaceholder.style.display='none';
    shipWrap.innerHTML='';
    shipLoading.style.display='block';
    fetch(@json(route('checkout.shipping')), {
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
      body: JSON.stringify({destination_id:String(id)})
    }).then(r=>r.json()).then(data=>{
      shipLoading.style.display='none';
      const opts = data.options || [];
      if(!opts.length){ shipWrap.innerHTML='<div style="font-size:13px;color:#9ca3af">Tidak ada layanan tersedia.</div>'; return; }
      opts.forEach((o,i)=>{
        const val = [o.courier, o.service, o.cost, o.etd, o.description].join('|');
        const lbl = document.createElement('label');
        lbl.className='radio-opt';
        lbl.innerHTML = `<input type="radio" name="ship_pick" value="${val}" ${i===0?'checked':''}>
          <span class="ro-ico">📦</span>
          <div class="ro-inf"><strong>${o.courier_name||o.courier.toUpperCase()} ${o.service}</strong><span>${o.description||''} ${o.etd?('• '+o.etd):''}</span></div>
          <span class="ro-price">${rupiah(o.cost)}</span>`;
        lbl.querySelector('input').addEventListener('change',()=>applyShip(o));
        shipWrap.appendChild(lbl);
      });
      applyShip(opts[0]);
    }).catch(()=>{ shipLoading.style.display='none'; shipWrap.innerHTML='<div style="font-size:13px;color:var(--red)">Gagal memuat ongkir.</div>'; });
  }

  function applyShip(o){
    shipOption.value = [o.courier, o.service, o.cost, o.etd, o.description].join('|');
    sumShip.textContent = rupiah(o.cost);
    sumTotal.textContent = rupiah(SUBTOTAL - DISCOUNT + Number(o.cost));
  }

  // ── toggle bank picker sesuai gateway ──
  const bankPicker = document.getElementById('bank-picker');
  function toggleBank(){
    const gw = document.querySelector('input[name=payment_gateway]:checked');
    bankPicker.style.display = (gw && gw.value==='manual_transfer') ? 'block' : 'none';
  }
  document.querySelectorAll('input[name=payment_gateway]').forEach(r=>r.addEventListener('change',toggleBank));
  toggleBank();

  // ── validasi sebelum submit ──
  document.getElementById('checkout-form').addEventListener('submit', function(e){
    if(!destId.value || !shipOption.value){
      e.preventDefault();
      toast('✗ Pilih kota tujuan & layanan pengiriman dulu');
    }
  });

  // klik di luar menutup hasil
  document.addEventListener('click', e=>{ if(!e.target.closest('.fg')) results.classList.remove('show'); });
})();
</script>
@endpush
@endsection
