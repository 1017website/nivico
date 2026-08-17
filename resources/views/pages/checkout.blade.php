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
          <div class="fr full"><div class="fg"><label>Email (untuk invoice)</label><input type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" placeholder="email@contoh.com" required></div></div>
          <div class="fr full">
            <div class="fg destination-field">
              <label for="dest_search">Cari Kecamatan atau Kode Pos <span class="required-mark">*</span></label>
              <p class="field-help">Ketik minimal 3 karakter, lalu pilih alamat dari daftar agar ongkir dihitung otomatis.</p>
              <div class="dest-search-wrap">
                <span class="dest-search-icon">⌕</span>
                <input type="text" id="dest_search" placeholder="Contoh: Medokan Semampir atau 60119" value="{{ old('city') }}" autocomplete="off" role="combobox" aria-controls="dest_results" aria-expanded="false">
                <span class="dest-search-state" id="dest_search_state"></span>
              </div>
              <div class="dest-results" id="dest_results"></div>
            </div>
          </div>
          <div class="destination-selected" id="destination_selected" hidden>
            <span class="destination-selected-icon">✓</span>
            <div><small>Tujuan pengiriman terpilih</small><strong id="destination_selected_label"></strong></div>
            <button type="button" id="destination_change">Ganti</button>
          </div>
          <div class="fr full"><div class="fg"><label>Alamat Lengkap <span class="required-mark">*</span></label><textarea name="address" placeholder="Nama jalan, nomor rumah, RT/RW, patokan..." required>{{ old('address') }}</textarea><p class="field-help">Cukup isi detail jalan dan nomor rumah. Kecamatan, kota, dan kode pos diisi otomatis dari pilihan di atas.</p></div></div>
          <div class="fr">
            <div class="fg"><label>Kota / Kabupaten</label><input type="text" id="city_display" value="{{ old('city') }}" placeholder="Terisi otomatis" readonly></div>
            <div class="fg"><label>Kode Pos</label><input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code') }}" placeholder="Terisi otomatis" readonly></div>
          </div>
          <input type="hidden" name="city" id="city_hidden" value="{{ old('city') }}">
          <input type="hidden" name="province" id="province_hidden" value="{{ old('province') }}">
          <input type="hidden" name="district" id="district_hidden" value="{{ old('district') }}">
        </div>

        <div class="co-card"><h3>🚚 Pilih Pengiriman</h3>
          <div id="ship-placeholder" style="font-size:13px;color:var(--muted)">Pilih kecamatan atau kode pos di atas untuk melihat tarif pengiriman.</div>
          <div class="ship-loading" id="ship-loading" style="display:none">Menghitung ongkir...</div>
          <div id="shipping-chooser" hidden>
            <div class="ship-step">
              <div class="ship-step-head"><span>1</span><div><strong>Pilih Kurir</strong><small>Pilih perusahaan pengiriman yang Anda inginkan.</small></div></div>
              <div class="courier-options" id="courier-options" role="radiogroup" aria-label="Pilihan kurir"></div>
            </div>
            <div class="ship-step ship-service-step" id="ship-service-step" hidden>
              <div class="ship-step-head"><span>2</span><div><strong>Pilih Paket <b id="selected-courier-name"></b></strong><small>Pilih tarif dan estimasi waktu pengiriman.</small></div></div>
              <div class="radio-grp" id="ship-options"></div>
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
                <div class="co-iimg"><img src="{{ ($it->variant->image ?? null) ?: ($it->product->image ?: asset('images/placeholder-product.svg')) }}" onerror="this.onerror=null;this.src='/images/placeholder-product.svg'"></div>
                <div class="co-iname">{{ $it->product->name }}@if($it->variant) ({{ $it->variant->name }})@endif x{{ $it->qty }}</div>
                <div class="co-iprice">
                  Rp{{ number_format($it->effectivePrice() * $it->qty, 0, ',', '.') }}
                  @if($it->product->hasActiveDiscount())
                    <del style="display:block;font-size:10.5px;color:var(--muted);font-weight:400">Rp{{ number_format($it->basePrice() * $it->qty, 0, ',', '.') }}</del>
                  @endif
                </div>
              </div>
            @endforeach
          </div>
          <div class="sum-row" style="margin-top:10px"><span>Subtotal</span><span>Rp{{ number_format($subtotal, 0, ',', '.') }}</span></div>
          <div class="sum-row"><span>Berat Pengiriman</span><span>{{ number_format($weight) }} gram</span></div>
          <div class="sum-row"><span>Ongkos Kirim</span><span id="sum-ship">—</span></div>
          <div class="sum-row"><span id="sum-discount-label">Diskon{{ $cart->promo ? ' ('.$cart->promo->code.')' : '' }}</span><span id="sum-discount" style="color:var(--green)">−Rp{{ number_format($discount, 0, ',', '.') }}</span></div>
          <div class="sum-row"><span>Biaya Layanan</span><span id="sum-service-fee">Rp{{ number_format($estimatedServiceFee, 0, ',', '.') }}</span></div>

          <div class="checkout-promo">
            <label class="checkout-promo-label" for="checkout-promo-code">
              <span>🏷️ Punya kode promo?</span>
              <a href="{{ route('promo') }}">Lihat promo</a>
            </label>
            <div class="promo-inp">
              <input type="text" id="checkout-promo-code" placeholder="Masukkan kode promo" value="{{ optional($cart->promo)->code }}" autocomplete="off">
              <button type="button" id="checkout-promo-apply">Pakai</button>
            </div>
            <div class="checkout-promo-feedback" id="checkout-promo-feedback" @if(!$cart->promo) hidden @endif>
              <span id="checkout-promo-message">@if($cart->promo)✓ {{ $cart->promo->code }} aktif — {{ $cart->promo->title }}@endif</span>
              <button class="checkout-promo-remove" type="button" id="checkout-promo-remove">Hapus</button>
            </div>
          </div>

          <div class="sum-row tot"><span>Total Bayar</span><span id="sum-total">Rp{{ number_format($subtotal - $discount + $estimatedServiceFee, 0, ',', '.') }}</span></div>
          <button class="btn-bayar" type="submit" id="btn-checkout">Buat Pesanan</button>
          <p style="font-size:11px;color:var(--muted);text-align:center;margin-top:10px">Setelah pesanan dibuat, Anda akan diarahkan ke halaman pembayaran yang aman.</p>
        </div>
      </div>
    </div>
  </form>
</div>

@php
  $checkoutPromoData = null;
  if ($cart->promo) {
      $checkoutPromoData = [
          'code' => $cart->promo->code,
          'title' => $cart->promo->title,
          'type' => $cart->promo->type,
          'value' => (int) $cart->promo->value,
          'max_discount' => $cart->promo->max_discount ? (int) $cart->promo->max_discount : null,
          'min_purchase' => (int) $cart->promo->min_purchase,
      ];
  }
@endphp

@push('scripts')
<script>
(function(){
  const SUBTOTAL = {{ $subtotal }};
  const SERVICE_FEE_TYPE = @json($serviceFeeSettings['type']);
  const SERVICE_FEE_VALUE = Number(@json($serviceFeeSettings['value']));
  const csrf = document.querySelector('meta[name=csrf-token]').content;
  let activePromo = @json($checkoutPromoData);
  const COURIER_LOGOS = {
    jne: @json(asset('images/couriers/jne.svg')),
    jnt: @json(asset('images/couriers/jnt.png')),
    sicepat: @json(asset('images/couriers/sicepat.svg')),
    anteraja: @json(asset('images/couriers/anteraja.png')),
    ninja: @json(asset('images/couriers/ninja.webp'))
  };

  const search = document.getElementById('dest_search');
  const results = document.getElementById('dest_results');
  const destId = document.getElementById('destination_id');
  const searchState = document.getElementById('dest_search_state');
  const selectedDestination = document.getElementById('destination_selected');
  const selectedLabel = document.getElementById('destination_selected_label');
  const changeDestination = document.getElementById('destination_change');
  const cityDisplay = document.getElementById('city_display');
  const postalCode = document.getElementById('postal_code');
  const shippingChooser = document.getElementById('shipping-chooser');
  const courierWrap = document.getElementById('courier-options');
  const shipWrap = document.getElementById('ship-options');
  const shipServiceStep = document.getElementById('ship-service-step');
  const selectedCourierName = document.getElementById('selected-courier-name');
  const shipPlaceholder = document.getElementById('ship-placeholder');
  const shipLoading = document.getElementById('ship-loading');
  const sumShip = document.getElementById('sum-ship');
  const sumDiscount = document.getElementById('sum-discount');
  const sumDiscountLabel = document.getElementById('sum-discount-label');
  const sumServiceFee = document.getElementById('sum-service-fee');
  const sumTotal = document.getElementById('sum-total');
  const shipOption = document.getElementById('shipping_option');
  const promoCode = document.getElementById('checkout-promo-code');
  const promoApply = document.getElementById('checkout-promo-apply');
  const promoFeedback = document.getElementById('checkout-promo-feedback');
  const promoMessage = document.getElementById('checkout-promo-message');
  const promoRemove = document.getElementById('checkout-promo-remove');
  let shippingOptions = [];
  let activeCourier = '';
  let activeShippingCost = null;

  const rupiah = n => 'Rp' + Number(n).toLocaleString('id-ID');

  function promoDiscount(shippingCost = 0){
    if(!activePromo || SUBTOTAL < Number(activePromo.min_purchase || 0)) return 0;

    if(activePromo.type === 'fixed'){
      return Math.min(Number(activePromo.value || 0), SUBTOTAL);
    }
    if(activePromo.type === 'percent'){
      let amount = Math.floor(SUBTOTAL * Number(activePromo.value || 0) / 100);
      if(activePromo.max_discount) amount = Math.min(amount, Number(activePromo.max_discount));
      return Math.min(amount, SUBTOTAL);
    }
    if(activePromo.type === 'free_shipping'){
      return Number(shippingCost || 0);
    }
    return 0;
  }

  function updateSummary(){
    const shipping = activeShippingCost === null ? 0 : Number(activeShippingCost);
    const discount = promoDiscount(shipping);
    const beforeFee = Math.max(0, SUBTOTAL + shipping - discount);
    const serviceFee = SERVICE_FEE_TYPE === 'percent'
      ? Math.round(beforeFee * Math.min(100, SERVICE_FEE_VALUE) / 100)
      : Math.round(Math.max(0, SERVICE_FEE_VALUE));

    sumDiscountLabel.textContent = activePromo ? `Diskon (${activePromo.code})` : 'Diskon';
    sumDiscount.textContent = '−' + rupiah(discount);
    sumServiceFee.textContent = rupiah(serviceFee);
    sumTotal.textContent = rupiah(beforeFee + serviceFee);

    if(activeShippingCost === null){
      sumShip.textContent = '—';
      sumShip.classList.remove('sum-saving');
    }else if(activePromo?.type === 'free_shipping'){
      sumShip.textContent = `GRATIS (hemat ${rupiah(shipping)})`;
      sumShip.classList.add('sum-saving');
    }else{
      sumShip.textContent = rupiah(shipping);
      sumShip.classList.remove('sum-saving');
    }
  }

  // ── cari tujuan ──
  let t;
  search.addEventListener('input', function(){
    clearTimeout(t);
    if(destId.value) resetDestination();
    const q = this.value.trim();
    if(q.length < 3){
      results.classList.remove('show');
      search.setAttribute('aria-expanded','false');
      searchState.textContent='';
      return;
    }
    t = setTimeout(()=>findDest(q), 350);
  });

  function findDest(q){
    searchState.textContent='Mencari...';
    results.innerHTML='<div class="dest-feedback">Mencari kecamatan dan kode pos...</div>';
    results.classList.add('show');
    search.setAttribute('aria-expanded','true');
    fetch(@json(route('checkout.destination')), {
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
      body: JSON.stringify({q})
    }).then(r=>{
      if(!r.ok) throw new Error('Gagal mencari tujuan');
      return r.json();
    }).then(rows=>{
      searchState.textContent='';
      results.innerHTML='';
      if(!rows.length){
        results.innerHTML='<div class="dest-feedback">Alamat tidak ditemukan. Coba nama kecamatan atau kode pos lain.</div>';
        return;
      }
      rows.forEach(row=>{
        const d=document.createElement('button');
        d.type='button';
        d.className='dest-item';
        const title=document.createElement('strong');
        title.textContent=[row.subdistrict,row.district].filter(Boolean).join(', ') || row.label;
        const meta=document.createElement('span');
        meta.textContent=[row.city,row.province,row.postal_code].filter(Boolean).join(' · ');
        d.append(title,meta);
        d.onclick=()=>pickDest(row);
        results.appendChild(d);
      });
    }).catch(()=>{
      searchState.textContent='';
      results.innerHTML='<div class="dest-feedback error">Pencarian gagal. Periksa koneksi lalu coba lagi.</div>';
    });
  }

  function pickDest(row){
    search.value = row.label;
    destId.value = row.id;
    document.getElementById('city_hidden').value = row.city || row.label;
    document.getElementById('province_hidden').value = row.province || '';
    document.getElementById('district_hidden').value = row.district || '';
    cityDisplay.value = row.city || '';
    postalCode.value = row.postal_code || '';
    selectedLabel.textContent = row.label;
    selectedDestination.hidden = false;
    search.classList.add('is-selected');
    results.classList.remove('show');
    search.setAttribute('aria-expanded','false');
    loadShipping(row.id);
  }

  function resetDestination(){
    destId.value='';
    shipOption.value='';
    selectedDestination.hidden=true;
    search.classList.remove('is-selected');
    cityDisplay.value='';
    postalCode.value='';
    document.getElementById('city_hidden').value='';
    document.getElementById('province_hidden').value='';
    document.getElementById('district_hidden').value='';
    shippingOptions=[];
    activeCourier='';
    shippingChooser.hidden=true;
    courierWrap.innerHTML='';
    shipWrap.innerHTML='';
    shipServiceStep.hidden=true;
    shipPlaceholder.style.display='block';
    shipPlaceholder.textContent='Pilih kecamatan atau kode pos di atas untuk melihat tarif pengiriman.';
    activeShippingCost=null;
    updateSummary();
  }

  changeDestination.addEventListener('click', function(){
    resetDestination();
    search.value='';
    search.focus();
  });

  // ── hitung ongkir ──
  function loadShipping(id){
    shipPlaceholder.style.display='none';
    shippingChooser.hidden=true;
    courierWrap.innerHTML='';
    shipWrap.innerHTML='';
    shipServiceStep.hidden=true;
    resetShippingChoice();
    shipLoading.style.display='block';
    fetch(@json(route('checkout.shipping')), {
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
      body: JSON.stringify({destination_id:String(id)})
    }).then(r=>{
      if(!r.ok) throw new Error('Gagal menghitung ongkir');
      return r.json();
    }).then(data=>{
      shipLoading.style.display='none';
      shippingOptions = data.options || [];
      if(!shippingOptions.length){
        shipPlaceholder.style.display='block';
        shipPlaceholder.textContent='Tidak ada layanan reguler yang tersedia untuk tujuan ini.';
        return;
      }
      renderCouriers();
    }).catch(()=>{
      shipLoading.style.display='none';
      shipPlaceholder.style.display='block';
      shipPlaceholder.textContent='Gagal memuat ongkir. Silakan pilih ulang tujuan atau coba lagi.';
    });
  }

  function renderCouriers(){
    const groups = new Map();
    shippingOptions.forEach(option=>{
      const code = String(option.courier || '').toLowerCase();
      if(!groups.has(code)){
        groups.set(code, {
          code,
          name: option.courier_name || code.toUpperCase(),
          options: []
        });
      }
      groups.get(code).options.push(option);
    });

    courierWrap.innerHTML='';
    groups.forEach(group=>{
      const lowest = Math.min(...group.options.map(option=>Number(option.cost)));
      const label = document.createElement('label');
      label.className='courier-opt';

      const radio = document.createElement('input');
      radio.type='radio';
      radio.name='courier_pick';
      radio.value=group.code;

      const logo = createCourierLogo(group.code, group.name);

      const info = document.createElement('span');
      info.className='courier-opt-info';
      const name = document.createElement('strong');
      name.textContent=group.name;
      const detail = document.createElement('small');
      detail.textContent=`${group.options.length} paket · mulai ${rupiah(lowest)}`;
      info.append(name,detail);

      radio.addEventListener('change',()=>renderServices(group));
      label.append(radio,logo,info);
      courierWrap.appendChild(label);
    });

    shippingChooser.hidden=false;
  }

  function createCourierLogo(code, name){
    const frame = document.createElement('span');
    frame.className=`courier-logo courier-logo-${code}`;

    const fallback = ()=>{
      frame.innerHTML='';
      frame.classList.add('courier-logo-fallback');
      frame.textContent=String(code || name).slice(0,3).toUpperCase();
    };

    if(!COURIER_LOGOS[code]){
      fallback();
      return frame;
    }

    const image = document.createElement('img');
    image.src=COURIER_LOGOS[code];
    image.alt='';
    image.loading='lazy';
    image.addEventListener('error',fallback,{once:true});
    frame.appendChild(image);

    return frame;
  }

  function renderServices(group){
    activeCourier=group.code;
    resetShippingChoice();
    selectedCourierName.textContent=group.name;
    shipWrap.innerHTML='';

    group.options.forEach(option=>{
      const value = [option.courier, option.service, option.cost, option.etd, option.description].join('|');
      const label = document.createElement('label');
      label.className='radio-opt';

      const radio = document.createElement('input');
      radio.type='radio';
      radio.name='ship_pick';
      radio.value=value;

      const icon = document.createElement('span');
      icon.className='ro-ico';
      icon.textContent='📦';

      const info = document.createElement('div');
      info.className='ro-inf';
      const name = document.createElement('strong');
      name.textContent=option.service;
      const detail = document.createElement('span');
      detail.textContent=[option.description, option.etd ? `• ${option.etd}` : ''].filter(Boolean).join(' ');
      info.append(name,detail);

      const price = document.createElement('span');
      price.className='ro-price';
      price.textContent=rupiah(option.cost);

      radio.addEventListener('change',()=>applyShip(option));
      label.append(radio,icon,info,price);
      shipWrap.appendChild(label);
    });

    shipServiceStep.hidden=false;
  }

  function resetShippingChoice(){
    shipOption.value='';
    activeShippingCost=null;
    updateSummary();
  }

  function applyShip(o){
    shipOption.value = [o.courier, o.service, o.cost, o.etd, o.description].join('|');
    activeShippingCost=Number(o.cost);
    updateSummary();
  }

  // ── promo tanpa me-reset alamat dan pilihan pengiriman ──
  promoApply.addEventListener('click', function(){
    const code=promoCode.value.trim();
    if(!code){
      showPromoError('Masukkan kode promo terlebih dahulu.');
      promoCode.focus();
      return;
    }

    setPromoBusy(true);
    fetch(@json(route('cart.promo')), {
      method:'POST',
      headers:{
        'Content-Type':'application/json',
        'X-CSRF-TOKEN':csrf,
        'Accept':'application/json'
      },
      body:JSON.stringify({code})
    }).then(async response=>{
      const data=await response.json();
      if(!response.ok) throw new Error(data.errors?.code?.[0] || data.message || 'Kode promo tidak dapat digunakan.');
      return data;
    }).then(data=>{
      activePromo=data.promo;
      promoCode.value=activePromo.code;
      promoFeedback.hidden=false;
      promoFeedback.classList.remove('checkout-promo-error');
      promoMessage.textContent=`✓ ${activePromo.code} aktif — ${activePromo.title}`;
      promoRemove.hidden=false;
      updateSummary();
      toast('✓ '+data.message);
    }).catch(error=>{
      showPromoError(error.message);
    }).finally(()=>setPromoBusy(false));
  });

  promoRemove.addEventListener('click', function(){
    setPromoBusy(true);
    fetch(@json(route('cart.promo.remove')), {
      method:'DELETE',
      headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}
    }).then(async response=>{
      const data=await response.json();
      if(!response.ok) throw new Error(data.message || 'Promo tidak dapat dihapus.');
      return data;
    }).then(data=>{
      activePromo=null;
      promoCode.value='';
      promoFeedback.hidden=true;
      promoFeedback.classList.remove('checkout-promo-error');
      updateSummary();
      toast(data.message);
    }).catch(error=>showPromoError(error.message))
      .finally(()=>setPromoBusy(false));
  });

  promoCode.addEventListener('keydown', function(event){
    if(event.key==='Enter'){
      event.preventDefault();
      promoApply.click();
    }
  });

  function showPromoError(message){
    promoFeedback.hidden=false;
    promoFeedback.classList.add('checkout-promo-error');
    promoMessage.textContent=message+(activePromo ? ` Promo ${activePromo.code} tetap aktif.` : '');
    promoRemove.hidden=!activePromo;
  }

  function setPromoBusy(busy){
    promoApply.disabled=busy;
    promoRemove.disabled=busy;
    promoApply.textContent=busy ? 'Memeriksa...' : 'Pakai';
  }

  // ── validasi sebelum submit ──
  document.getElementById('checkout-form').addEventListener('submit', function(e){
    if(!destId.value){
      e.preventDefault();
      toast('Pilih kecamatan dari hasil pencarian terlebih dahulu.');
      search.focus();
      return;
    }
    if(!activeCourier){
      e.preventDefault();
      toast('Pilih kurir pengiriman terlebih dahulu.');
      courierWrap.querySelector('input')?.focus();
      return;
    }
    if(!shipOption.value){
      e.preventDefault();
      toast('Pilih paket pengiriman yang Anda inginkan.');
      shipWrap.querySelector('input')?.focus();
    }
  });

  // klik di luar menutup hasil
  document.addEventListener('click', e=>{
    if(!e.target.closest('.destination-field')){
      results.classList.remove('show');
      search.setAttribute('aria-expanded','false');
    }
  });
})();
</script>
@endpush
@endsection
