@extends('user.layouts.app')

@section('content')
<div class="max-w-5xl mx-auto bg-white p-6 rounded shadow">
  <h1 class="text-2xl font-semibold mb-4">Keranjang Pesanan</h1>

  @if (session('success'))
    <div class="mb-3 rounded bg-green-50 text-green-700 p-3">{{ session('success') }}</div>
  @endif

  @php
    $fmt = fn($n) => number_format((int)$n, 0, ',', '.');
    $total = 0;
    $isEmpty = empty($keranjang) || (is_array($keranjang) && count($keranjang) === 0);
  @endphp

  @if ($isEmpty)
    <div class="text-gray-600">Keranjang masih kosong.</div>
  @else
    <div class="space-y-3">
      @foreach ($keranjang as $item)
        @php
          $type   = $item['type'] ?? 'prod';
          $nama   = $item['nama'] ?? 'Produk Tidak Ditemukan';
          $harga  = (int)($item['harga'] ?? 0);
          $qty    = (int)($item['qty'] ?? 1);        // utk produk biasa
          $jumlah = (int)($item['jumlah'] ?? 1);     // utk order custom
          $rid    = $item['id'] ?? '';

          // qty yang ditampilkan (prod pakai qty, custom pakai jumlah)
          $displayQty = $type === 'oc' ? $jumlah : $qty;

          // hitung line & unit + nama untuk tampilan
          if ($type === 'prod') {
              $unit = $harga;
              $line = $harga * $qty;
          } else {
              $unit = (int)($item['prices']['total_price'] ?? 0);
              $line = (int)($item['prices']['grand_total'] ?? ($unit * $jumlah));
              $nama = ($item['jenis_pakaian'] ?? 'Order Custom').' — '.($item['jenis_kain'] ?? '-');
          }

          $total += $line;
        @endphp

        {{-- Produk biasa --}}
        @if ($type === 'prod')
          <div class="flex items-center justify-between border rounded-md p-3 bg-white">
            <input type="checkbox"
                   class="row-check"
                   value="{{ $rid }}"
                   data-name="{{ e($nama) }}"
                   data-price="{{ $unit }}"
                   data-qty="{{ $displayQty }}">

            <div class="flex items-center gap-3">
              <img src="{{ $item['gambar'] ?? asset('images/placeholder.png') }}"
                   alt="{{ $nama }}"
                   class="w-20 h-20 object-cover rounded">
              <div>
                <div class="font-medium">{{ $nama }}</div>
                <div class="text-sm text-gray-600">Rp {{ $fmt($harga) }}</div>
              </div>
            </div>

            <div class="flex items-center gap-2 justify-center">
              <form method="POST" action="{{ route('keranjang.update') }}">
                @csrf
                <input type="hidden" name="id" value="{{ $rid }}">
                <input type="hidden" name="aksi" value="dec">
                <button class="px-3 py-1 border rounded hover:bg-gray-50" title="Kurangi">−</button>
              </form>

              <div class="w-10 text-center">{{ $displayQty }}</div>

              <form method="POST" action="{{ route('keranjang.update') }}">
                @csrf
                <input type="hidden" name="id" value="{{ $rid }}">
                <input type="hidden" name="aksi" value="inc">
                <button class="px-3 py-1 border rounded hover:bg-gray-50" title="Tambah">+</button>
              </form>
            </div>

            <div class="w-36 text-right font-medium">Rp {{ $fmt($line) }}</div>

            <form method="POST" action="{{ route('keranjang.remove', $rid) }}" onsubmit="return confirm('Hapus item ini?')">
              @csrf @method('DELETE')
              <button class="ml-3 px-3 py-1 border rounded text-red-600 hover:bg-red-50">Hapus</button>
            </form>
          </div>

        {{-- Order Custom --}}
        @else
          <div class="flex items-center justify-between border rounded-md p-3 bg-white">
            <input type="checkbox"
                   class="row-check"
                   value="{{ $rid }}"
                   data-name="{{ e($nama) }}"
                   data-price="{{ $unit }}"
                   data-qty="{{ $displayQty }}">

            <div class="flex flex-col">
              <div class="font-medium">Order Custom</div>
              <div class="text-sm text-gray-700">{{ $item['jenis_pakaian'] ?? '-' }} — {{ $item['jenis_kain'] ?? '-' }}</div>
              @if (!empty($item['request']))
                <div class="text-xs text-gray-500 mt-1">Catatan: {{ $item['request'] }}</div>
              @endif
            </div>

            <div class="flex items-center gap-2 justify-center">
              <form method="POST" action="{{ route('keranjang.update') }}">
                @csrf
                <input type="hidden" name="id" value="{{ $rid }}">
                <input type="hidden" name="aksi" value="dec">
                <button class="px-3 py-1 border rounded hover:bg-gray-50" title="Kurangi">−</button>
              </form>

              <div class="w-10 text-center">{{ $displayQty }}</div>

              <form method="POST" action="{{ route('keranjang.update') }}">
                @csrf
                <input type="hidden" name="id" value="{{ $rid }}">
                <input type="hidden" name="aksi" value="inc">
                <button class="px-3 py-1 border rounded hover:bg-gray-50" title="Tambah">+</button>
              </form>
            </div>

            <div class="w-48 text-right">
              <div class="font-medium">Rp {{ $fmt($line) }}</div>
              <div class="text-xs text-gray-500">Rp {{ $fmt($unit) }} / item</div>
            </div>

            <form method="POST" action="{{ route('keranjang.remove', $rid) }}" onsubmit="return confirm('Hapus item ini?')">
              @csrf @method('DELETE')
              <button class="ml-3 px-3 py-1 border rounded text-red-600 hover:bg-red-50">Hapus</button>
            </form>
          </div>
        @endif
      @endforeach
    </div>

    {{-- Bagian bawah --}}
    <div class="mt-6 border-t pt-4 flex items-center justify-between">
      <form method="POST" action="{{ route('keranjang.clear') }}" onsubmit="return confirm('Kosongkan semua item di keranjang?')">
        @csrf @method('DELETE')
        <button type="submit" class="px-4 py-2 border rounded hover:bg-gray-50">
          Kosongkan Keranjang
        </button>
      </form>

      <div class="text-right">
        <div class="text-xl font-bold text-gray-900">
          Total: Rp <span id="selectedTotal">{{ $fmt($total) }}</span>
        </div>

        <form id="payForm" method="POST" action="{{ route('keranjang.pay') }}" class="mt-2 inline-block">
          @csrf
          <input type="hidden" name="total" id="payTotal" value="0">
          <input type="hidden" name="items" id="payItems" value="">
          <input type="hidden" name="selected_ids" id="paySelectedIds" value="">
          <button type="submit"
                  class="px-5 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white">
            Pesan Sekarang
          </button>
        </form>
      </div>
    </div>
  @endif
</div>

<script>
(function () {
  function rp(n) {
    try { return new Intl.NumberFormat('id-ID').format(n || 0); }
    catch(e){ return String(n || 0).replace(/\B(?=(\d{3})+(?!\d))/g,'.'); }
  }

  function getCheckedRows() {
    return Array.from(document.querySelectorAll('.row-check:checked'));
  }

  function computeTotal() {
    let sum = 0;
    getCheckedRows().forEach(cb => {
      const price = parseInt(cb.dataset.price || '0', 10);
      const qty   = parseInt(cb.dataset.qty   || '0', 10);
      if (!isNaN(price) && !isNaN(qty)) sum += (price * qty);
    });
    return sum;
  }

  function buildItemDetails() {
    return getCheckedRows().map(cb => {
      return {
        id: cb.value,
        price: parseInt(cb.dataset.price || '0', 10),
        quantity: parseInt(cb.dataset.qty || '0', 10),
        name: cb.dataset.name || 'Item'
      };
    });
  }

  function updateSelectedTotal() {
    const out = document.getElementById('selectedTotal');
    if (out) out.textContent = rp(computeTotal());
  }

  document.addEventListener('change', e => {
    if (e.target && e.target.classList.contains('row-check')) {
      updateSelectedTotal();
    }
  });

  updateSelectedTotal();

  const payForm = document.getElementById('payForm');
  if (payForm) {
    payForm.addEventListener('submit', function (e) {
      const rows = getCheckedRows();
      if (rows.length === 0) {
        e.preventDefault();
        alert('Silakan pilih minimal satu item.');
        return;
      }

      const total = computeTotal();
      const items = buildItemDetails();
      const ids   = rows.map(cb => cb.value);

      document.getElementById('payTotal').value = String(total);
      document.getElementById('payItems').value = JSON.stringify(items);
      document.getElementById('paySelectedIds').value = JSON.stringify(ids);
    });
  }
})();
</script>
@endsection
