@extends('user.layouts.app')

@section('content')
<div class="p-6 bg-white shadow rounded-lg max-w-7xl mx-auto">
  <div class="flex items-center gap-2 mb-6">
    <a href="/dashboard" class="hover:opacity-80">
      <img src="{{ asset('images/panah.png') }}" alt="Kembali" class="w-6 h-6">
    </a>
    <h1 class="text-2xl font-bold">Orders Custom</h1>
  </div>

  <form
    method="POST"
    action="{{ route('oc.simpan') }}"
    enctype="multipart/form-data"
    class="grid grid-cols-1 md:grid-cols-3 gap-6"
    x-data="orderForm()"
    x-init="onPakaianChange(jenis)"
  >
    @csrf

    {{-- Jenis Pakaian + Preview --}}
    <div>
      <label class="block font-semibold mb-1">Jenis Pakaian</label>
      <select id="jenis_pakaian" name="jenis_pakaian"
              class="w-full border px-3 py-2 rounded bg-white"
              x-model="jenis" @change="onPakaianChange($event.target.value)">
        <option value="jas">Jas</option>
        <option value="blezer">Blezer</option>
        <option value="dress">Dress</option>
        <option value="baju_dinas">Baju Dinas</option>
        <option value="baju_seragam">Baju Seragam</option>
        <option value="kebaya">Kebaya</option>
        <option value="kemeja">Kemeja Cowok</option>
        <option value="kemeja_cewek">Kemeja Cewek</option>
        <option value="baju_melayu">Baju Melayu</option>
        <option value="baju_couple">Baju Couple Keluarga</option>
        <option value="rok">Rok</option>
        <option value="celana_bahan">Celana Bahan</option>
        <option value="celana_levis">Celana Levis</option>
      </select>

      <div class="w-full h-48 border border-dashed mt-4 flex items-center justify-center bg-gray-50">
        <img id="preview_pakaian" src="" alt="Preview Pakaian" class="h-full object-contain hidden">
        <span id="preview_placeholder" class="text-gray-400">Belum ada gambar</span>
      </div>
    </div>

    {{-- Jenis Kain + Upload + Checkbox --}}
    <div>
      <label class="block font-semibold mb-1">Jenis Kain</label>
      <select id="jenis_kain" name="jenis_kain" class="w-full border px-3 py-2 rounded bg-white" x-model="kain">
        {{-- Diisi lewat JS --}}
      </select>

      <div class="mt-4">
        <label class="block font-semibold mb-1">Upload Gambar Tambahan (Opsional)</label>
        <input type="file" name="gambar_custom"
               class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
      </div>

      <div class="mt-4 flex items-center gap-2">
        <input type="checkbox" x-model="kainCustomer" class="w-5 h-5">
        <label class="font-semibold">Kain & Aksesoris dari Customer</label>
      </div>

      <input type="hidden" name="kain_aksesoris_customer" :value="kainCustomer ? 1 : 0">
    </div>

    {{-- Jenis Ukuran + Input Dinamis --}}
    <div>
      <label class="block font-semibold mb-1">Jenis Ukuran</label>
      <select id="jenis_ukuran" name="jenis_ukuran"
              class="w-full border px-3 py-2 rounded mb-4 bg-white"
              x-model="jenisUkuran">
        {{-- Diisi lewat JS --}}
      </select>

      <div x-show="jenisUkuran" class="grid grid-cols-2 gap-2 max-h-[400px] overflow-auto border p-2 rounded">
        {{-- Baju --}}
        <template x-if="jenisUkuran === 'baju'">
          <div class="grid grid-cols-2 gap-2 col-span-2">
            <div class="flex items-center border rounded"><input type="text" name="panjang_baju" placeholder="Panjang Baju" class="w-full px-3 py-2"><span class="px-2 text-gray-500">cm</span></div>
            <div class="flex items-center border rounded"><input type="text" name="panjang_bahu" placeholder="Panjang Bahu" class="w-full px-3 py-2"><span class="px-2 text-gray-500">cm</span></div>
            <div class="flex items-center border rounded"><input type="text" name="panjang_tangan" placeholder="Panjang Tangan" class="w-full px-3 py-2"><span class="px-2 text-gray-500">cm</span></div>
            <div class="flex items-center border rounded"><input type="text" name="lingkaran_pangkal_lengan" placeholder="Lingkar Pangkal Lengan" class="w-full px-3 py-2"><span class="px-2 text-gray-500">cm</span></div>
            <div class="flex items-center border rounded"><input type="text" name="lingkar_siku" placeholder="Lingkar Siku" class="w-full px-3 py-2"><span class="px-2 text-gray-500">cm</span></div>
            <div class="flex items-center border rounded"><input type="text" name="lingkar_ujung_tangan" placeholder="Lingkar Ujung Tangan" class="w-full px-3 py-2"><span class="px-2 text-gray-500">cm</span></div>
            <div class="flex items-center border rounded"><input type="text" name="lingkar_dada" placeholder="Lingkar Dada" class="w-full px-3 py-2"><span class="px-2 text-gray-500">cm</span></div>
            <div class="flex items-center border rounded"><input type="text" name="lingkar_pingga" placeholder="Lingkar Pingga" class="w-full px-3 py-2"><span class="px-2 text-gray-500">cm</span></div>
            <div class="flex items-center border rounded"><input type="text" name="lingkar_pinggul" placeholder="Lingkar Pinggul" class="w-full px-3 py-2"><span class="px-2 text-gray-500">cm</span></div>
            <div class="flex items-center border rounded"><input type="text" name="lingkar_leher" placeholder="Lingkar Leher" class="w-full px-3 py-2"><span class="px-2 text-gray-500">cm</span></div>
          </div>
        </template>

        {{-- Celana --}}
        <template x-if="jenisUkuran === 'celana'">
          <div class="grid grid-cols-2 gap-2 col-span-2">
            <div class="flex items-center border rounded"><input type="text" name="panjang_celana" placeholder="Panjang Celana" class="w-full px-3 py-2"><span class="px-2 text-gray-500">cm</span></div>
            <div class="flex items-center border rounded"><input type="text" name="lingkar_pinggang_celana" placeholder="Lingkar Pinggang" class="w-full px-3 py-2"><span class="px-2 text-gray-500">cm</span></div>
            <div class="flex items-center border rounded"><input type="text" name="lingkar_pinggul_celana" placeholder="Lingkar Pinggul" class="w-full px-3 py-2"><span class="px-2 text-gray-500">cm</span></div>
            <div class="flex items-center border rounded"><input type="text" name="lingkar_paha" placeholder="Lingkar Paha" class="w-full px-3 py-2"><span class="px-2 text-gray-500">cm</span></div>
            <div class="flex items-center border rounded"><input type="text" name="lingkar_lutut" placeholder="Lingkar Lutut" class="w-full px-3 py-2"><span class="px-2 text-gray-500">cm</span></div>
            <div class="flex items-center border rounded"><input type="text" name="lingkar_kaki_bawah" placeholder="Lingkar Kaki Bawah" class="w-full px-3 py-2"><span class="px-2 text-gray-500">cm</span></div>
            <div class="flex items-center border rounded"><input type="text" name="panjang_pisak" placeholder="Panjang Pisak" class="w-full px-3 py-2"><span class="px-2 text-gray-500">cm</span></div>
          </div>
        </template>

        {{-- Rok --}}
        <template x-if="jenisUkuran === 'rok'">
          <div class="grid grid-cols-2 gap-2 col-span-2">
            <div class="flex items-center border rounded"><input type="text" name="panjang_rok" placeholder="Panjang Rok" class="w-full px-3 py-2"><span class="px-2 text-gray-500">cm</span></div>
            <div class="flex items-center border rounded"><input type="text" name="lingkar_pinggang_rok" placeholder="Lingkar Pinggang" class="w-full px-3 py-2"><span class="px-2 text-gray-500">cm</span></div>
            <div class="flex items-center border rounded"><input type="text" name="lingkar_pinggul_rok" placeholder="Lingkar Pinggul" class="w-full px-3 py-2"><span class="px-2 text-gray-500">cm</span></div>
          </div>
        </template>
      </div>
    </div>

    {{-- Perkiraan Harga --}}
    <div class="md:col-span-3">
      <div class="border rounded-lg p-4 bg-gray-50">
        <h2 class="font-bold text-lg mb-3">Perkiraan Harga</h2>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-start">
          <div>
            <div class="text-sm text-gray-600">Base / item</div>
            <div class="text-xl font-semibold" x-text="rupiah(base)"></div>
          </div>

          <div>
            <div class="text-sm text-gray-600">Tambahan Kain / item</div>
            <div class="text-xl font-semibold" x-text="kainCustomer ? '—' : rupiah(fabricAdd)"></div>
            <div class="text-xs text-gray-500" x-show="!kainCustomer">
              Multiplier: <span class="font-medium" x-text="multiplier() + '×'"></span>
            </div>
          </div>

          <div>
            <div class="text-sm text-gray-600">Total / item</div>
            <div class="text-xl font-semibold" x-text="rupiah(unitTotal)"></div>
          </div>

          <div>
            <div class="text-sm text-gray-600">Jumlah</div>
            <div class="inline-flex rounded-md border overflow-hidden w-[160px]">
              <button type="button" @click="dec()" :disabled="qtySafe <= 1"
                      class="px-3 py-2 font-bold disabled:opacity-50 disabled:cursor-not-allowed">−</button>
              <input x-model.number="qty" name="jumlah" type="text" inputmode="numeric" pattern="[0-9]*"
                     class="w-full text-center px-2 py-2 border-x outline-none" @input="qty = qtySafe">
              <button type="button" @click="inc()" class="px-3 py-2 font-bold">+</button>
            </div>
            <div class="text-xs text-gray-500 mt-1">Min 1</div>
          </div>

          <div>
            <div class="text-sm text-gray-600">Total (× jumlah)</div>
            <div class="text-2xl font-bold" x-text="rupiah(total)"></div>
            <p class="text-xs text-gray-500 mt-1">*Jika mencentang “Kain & Aksesoris dari Customer”, tambahan kain = 0.</p>
          </div>
        </div>
      </div>
    </div>

    {{-- Pick-up --}}
    <div class="md:col-span-3 grid md:grid-cols-3 gap-6">
      <div>
        <label class="block font-semibold mb-1">Pick-up</label>
        <select name="pickup" class="w-full border px-3 py-2 rounded bg-white">
          <option value="jemput_toko">Jemput ke toko</option>
          <option value="kurir">Kirim via kurir</option>
        </select>
      </div>
    </div>

    {{-- Request Customer --}}
    <div class="md:col-span-3">
      <label for="request" class="block font-semibold mb-1">Request Customer</label>
      <input type="text" id="request" name="request" placeholder="Tulis permintaan khusus..."
             class="w-full border px-3 py-2 rounded">
    </div>

    {{-- ======= HIDDEN: KIRIM ANGKA FINAL KE SERVER ======= --}}
    <input type="hidden" name="base_price"   :value="base">
    <input type="hidden" name="fabric_add"   :value="kainCustomer ? 0 : fabricAdd">
    <input type="hidden" name="unit_total"   :value="unitTotal">
    <input type="hidden" name="grand_total"  :value="total">
    <input type="hidden" name="jumlah"       :value="qtySafe">
    {{-- ================================================ --}}

    <div class="md:col-span-3 flex justify-end">
      <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded shadow">
        Simpan
      </button>
    </div>
  </form>
</div>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
var GUIDE_BASE  = "{{ asset('images') }}";
var PLACEHOLDER = "{{ asset('images/placeholder.png') }}";

var GUIDE_MAP = {
  jas:"ukuran jas.png", blezer:"ukuran jas.png",
  dress:"baju.png", baju_dinas:"baju.png", baju_seragam:"baju.png",
  kebaya:"baju.png", kemeja:"baju.png", kemeja_cewek:"baju.png",
  baju_melayu:"baju.png", baju_couple:"baju.png",
  rok:"ukuran rok.png", celana_bahan:"ukuran celana.png", celana_levis:"ukuran celana.png"
};

const BASE_PRICE = {
  jas:200000, blezer:180000, dress:120000, baju_dinas:150000, baju_seragam:100000,
  kebaya:210000, kemeja:80000, kemeja_cewek:95000, baju_melayu:170000,
  baju_couple:270000, rok:50000, celana_bahan:100000, celana_levis:150000
};

const FABRIC_ADD = {
  "american drill":38000, "nagata drill":48500, "america drill 1919":45000, "twist drill":36000,
  "katun":45000, "linen":90000, "rayon":40000, "polyester":30000, "wolfis":28000,
  "batik":55000, "kopri":60000, "jeans":80000, "sutra":150000, "brokat":100000,
  "satin":38000, "velvet":70000
};

const KAIN_BY_CLOTHES = {
  jas:['american drill','twist drill','velvet'],
  blezer:['american drill','twist drill','velvet'],
  dress:['katun','wolfis','sutra','linen','brokat','batik'],
  baju_dinas:['kopri','katun','nagata drill','america drill 1919'],
  baju_seragam:['katun','polyester','batik','rayon'],
  kebaya:['sutra','brokat','satin'],
  kemeja:['katun','linen','batik'],
  kemeja_cewek:['katun','linen','batik'],
  baju_melayu:['katun','batik','linen','satin'],
  baju_couple:['katun','batik','linen'],
  rok:['linen','polyester','jeans','katun','satin'],
  celana_bahan:['linen','polyester','jeans','katun','satin'],
  celana_levis:['jeans'],
};

const SIZE_KIND_BY_CLOTHES = {
  jas:'baju', blezer:'baju', dress:'baju', baju_dinas:'baju', baju_seragam:'baju',
  kebaya:'baju', kemeja:'baju', kemeja_cewek:'baju', baju_melayu:'baju', baju_couple:'baju',
  rok:'rok', celana_bahan:'celana', celana_levis:'celana'
};
const SIZE_KIND_LABEL = { baju:'Ukuran Baju', celana:'Ukuran Celana', rok:'Ukuran Rok' };

function setPreview(jenis) {
  var imgEl = document.getElementById('preview_pakaian');
  var ph    = document.getElementById('preview_placeholder');
  if (!imgEl) return;

  var file = GUIDE_MAP[(jenis||'').toLowerCase()];
  imgEl.onerror = function () { imgEl.onerror=null; imgEl.src = PLACEHOLDER; imgEl.classList.remove('hidden'); if (ph) ph.classList.add('hidden'); };
  imgEl.src = file ? (GUIDE_BASE + '/' + file) : PLACEHOLDER;
  imgEl.onload = function () { imgEl.classList.remove('hidden'); if (ph) ph.classList.add('hidden'); };
}

function fillOptions(el, items) {
  el.innerHTML = (items || []).map(function (it) { return '<option value="'+it.value+'">'+it.label+'</option>'; }).join('');
}

document.addEventListener('alpine:init', function () {
  Alpine.data('orderForm', function () {
    return {
      jenis: 'blezer', // default contoh
      kain: '',
      kainCustomer: false,
      jenisUkuran: '',
      qty: 1,

      onPakaianChange(val) {
        var kainSel = document.getElementById('jenis_kain');
        var ukrSel  = document.getElementById('jenis_ukuran');

        var allowed = (KAIN_BY_CLOTHES[val] || []).map(function(k){
          return { value:k, label:k.charAt(0).toUpperCase()+k.slice(1) };
        });
        fillOptions(kainSel, allowed);
        this.kain = allowed.length ? allowed[0].value : '';

        var kind = SIZE_KIND_BY_CLOTHES[val] || 'baju';
        fillOptions(ukrSel, [{ value:kind, label: SIZE_KIND_LABEL[kind] || 'Ukuran' }]);
        this.jenisUkuran = kind;

        setPreview(val);
      },

      get base(){ return Number(BASE_PRICE[this.jenis] || 0); },
      get fabricAddRaw(){ return Number(FABRIC_ADD[this.kain] || 0); },
      multiplier(){
        var c = this.jenis;
        if (c === 'dress' || c === 'baju_couple') return 3;
        if (['blezer','kebaya','baju_melayu','celana_levis','celana_bahan'].indexOf(c) !== -1) return 2;
        return 1.5;
      },
      get fabricAdd(){ return Math.round(this.fabricAddRaw * this.multiplier()); },
      get unitTotal(){ return this.base + (this.kainCustomer ? 0 : this.fabricAdd); },
      get qtySafe(){ var n = parseInt(this.qty || 1, 10); return isNaN(n) || n < 1 ? 1 : n; },
      get total(){ return this.unitTotal * this.qtySafe; },

      inc(){ this.qty = this.qtySafe + 1; },
      dec(){ this.qty = this.qtySafe > 1 ? this.qtySafe - 1 : 1; },

      rupiah(n){
        try { return new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',minimumFractionDigits:0}).format(n||0); }
        catch(e){ return 'Rp' + String(n||0).replace(/\B(?=(\d{3})+(?!\d))/g,'.'); }
      }
    }
  });
});

document.addEventListener('DOMContentLoaded', function () {
  var select = document.getElementById('jenis_pakaian');
  var awal   = (select && select.value) ? select.value : 'blezer';
  setPreview(awal);

  var kainSel = document.getElementById('jenis_kain');
  var ukrSel  = document.getElementById('jenis_ukuran');

  var arr = KAIN_BY_CLOTHES[awal] || [];
  fillOptions(kainSel, arr.map(k => ({value:k, label:k.charAt(0).toUpperCase()+k.slice(1)})));

  var kind = SIZE_KIND_BY_CLOTHES[awal] || 'baju';
  fillOptions(ukrSel, [{ value:kind, label: SIZE_KIND_LABEL[kind] || 'Ukuran' }]);
});
</script>
@endsection
