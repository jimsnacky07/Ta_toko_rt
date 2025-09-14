@extends('admin.layouts.app')
@section('title','Daftar Pesanan')

@section('content')
<div class="max-w-7xl mx-auto">

  @if(session('ok'))
  <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('ok') }}</div>
  @endif

  <h1 class="text-2xl font-semibold mb-6 text-center">Kelola Pesanan</h1>

  {{-- Tabs untuk jenis pesanan --}}
  <div class="mb-6">
    <div class="border-b border-gray-200">
      <nav class="-mb-px flex space-x-8">
        <a href="{{ request()->fullUrlWithQuery(['type' => 'all']) }}"
          class="py-2 px-1 border-b-2 font-medium text-sm {{ $type === 'all' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
          Semua Pesanan ({{ $allCount }})
        </a>
        <a href="{{ request()->fullUrlWithQuery(['type' => 'product']) }}"
          class="py-2 px-1 border-b-2 font-medium text-sm {{ $type === 'product' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
          Pesanan Produk ({{ $productCount }})
        </a>
        <a href="{{ request()->fullUrlWithQuery(['type' => 'custom']) }}"
          class="py-2 px-1 border-b-2 font-medium text-sm {{ $type === 'custom' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
          Order Custom ({{ $customCount }})
        </a>
      </nav>
    </div>
  </div>

  {{-- Pencarian sederhana (nama/email) --}}
  <form method="get" class="mb-6 bg-white p-4 rounded-lg shadow">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
      <input type="hidden" name="type" value="{{ $type }}">
      <input type="text" name="q" value="{{ request('q') }}" class="border rounded p-2" placeholder="Cari nama atau email...">
      <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 w-max">Cari</button>
    </div>
  </form>

  <div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-amber-100 text-gray-700">
          <tr class="border-b">
            <th class="p-3 text-left w-16">No</th>
            <th class="p-3 text-left">Pemesan</th>
            <th class="p-3 text-left">Kode Pesanan</th>
            <th class="p-3 text-left">Jenis Pesanan</th>
            <th class="p-3 text-left">Detail Produk</th>
            <th class="p-3 text-left">Total</th>
            <th class="p-3 text-left">Status</th>
            <th class="p-3 text-left">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($orders as $i => $o)
          @php
          $namaUser = $o->user->nama ?? $o->user->name ?? '—';
          $emailUser = $o->user->email ?? '';
          $kodeOrder = $o->order_code ?? $o->kode_pesanan ?? '#' . $o->id;
          $statusBayar = $o->status ?? '-';
          // Skip jika status dibatalkan/cancelled
          if (in_array(strtolower($statusBayar), ['dibatalkan','cancelled','canceled','batal'])) { continue; }

          // Tentukan jenis pesanan dgn prioritas prefix kode (OC- / OP-),
          // fallback ke inspeksi item jika prefix tidak ada
          $prefix = is_string($kodeOrder) ? strtolower(substr($kodeOrder, 0, 3)) : '';

          $orderDetails = [];
          $isCustomByItems = false;
          if ($o->orderItems && $o->orderItems->count() > 0) {
          foreach ($o->orderItems as $item) {
          if (!$item->product_id || str_contains(strtolower($item->garment_type ?? ''), 'custom')) {
          $isCustomByItems = true;
          }
          $orderDetails[] = $item;
          }
          }

          if ($prefix === 'oc-') {
          $isCustomOrder = true;
          } elseif ($prefix === 'op-') {
          $isCustomOrder = false;
          } else {
          $isCustomOrder = $isCustomByItems; // fallback lama
          }

          $orderType = $isCustomOrder ? 'Order Custom' : 'Pesanan Produk';
          $typeClass = $isCustomOrder ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800';

          // Status badge colors
          $statusColors = [
          'menunggu' => 'bg-yellow-100 text-yellow-800',
          'diproses' => 'bg-blue-100 text-blue-800',
          'selesai' => 'bg-green-100 text-green-800',
          'siap-diambil' => 'bg-purple-100 text-purple-800',
          'dibatalkan' => 'bg-red-100 text-red-800',
          ];
          $badgeClass = $statusColors[$statusBayar] ?? 'bg-gray-100 text-gray-800';
          @endphp

          <tr class="border-b last:border-0 hover:bg-gray-50">
            <td class="p-3">{{ $orders->firstItem() + $i }}</td>

            <td class="p-3">
              <div class="font-medium">{{ $namaUser }}</div>
              @if($emailUser)
              <div class="text-xs text-gray-500">{{ $emailUser }}</div>
              @endif
            </td>

            <td class="p-3">
              <span class="font-mono text-sm">{{ $kodeOrder }}</span>
              <div class="text-xs text-gray-500">{{ $o->created_at->format('d/m/Y H:i') }}</div>
            </td>

            <td class="p-3">
              <span class="px-2 py-1 rounded-full text-xs font-medium {{ $typeClass }}">
                {{ $orderType }}
              </span>
            </td>

            <td class="p-3">
              @if($orderDetails)
              @foreach($orderDetails as $item)
              <div class="mb-1">
                <span class="font-medium">{{ $item->garment_type }}</span>
                @if($item->fabric_type)
                <span class="text-gray-600">- {{ $item->fabric_type }}</span>
                @endif
                @if($item->size)
                <span class="text-gray-500">({{ $item->size }})</span>
                @endif
                <span class="text-sm text-gray-500">x{{ $item->quantity }}</span>
              </div>
              @endforeach
              @else
              <span class="text-gray-400">-</span>
              @endif
            </td>

            <td class="p-3">
              <span class="font-semibold">Rp {{ number_format($o->total_amount ?? 0, 0, ',', '.') }}</span>
            </td>

            <td class="p-3">
              <span class="px-2 py-1 rounded-full text-xs font-medium {{ $badgeClass }}">
                {{ ucfirst($statusBayar) }}
              </span>
            </td>

            {{-- Kolom Tailor dihapus sesuai permintaan --}}

            <td class="p-3">
              <div class="flex gap-2">
                <button class="text-blue-600 hover:text-blue-800 text-sm" onclick="showOrderDetails({{ $o->id }})">
                  👁️ Detail
                </button>
                @if($statusBayar !== 'selesai')
                <button class="text-green-600 hover:text-green-800 text-sm" onclick="updateOrderStatus({{ $o->id }}, 'selesai')">
                  ✅ Selesai
                </button>
                @endif
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="9" class="px-4 py-8 text-center text-gray-500">
              @if($type === 'product')
              Belum ada pesanan produk.
              @elseif($type === 'custom')
              Belum ada order custom.
              @else
              Belum ada pesanan.
              @endif
              <div class="mt-2">
                <a href="/test-create-order" class="text-blue-600 hover:underline">Buat pesanan test</a> |
                <a href="/test-create-custom-order" class="text-blue-600 hover:underline">Buat order custom test</a>
              </div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if(method_exists($orders, 'links'))
    <div class="px-4 py-3 border-t bg-gray-50">
      {{ $orders->withQueryString()->links() }}
    </div>
    @endif
  </div>
</div>

{{-- Modal untuk detail pesanan --}}
<div id="orderDetailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-96 overflow-y-auto">
    <div class="p-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Detail Pesanan</h3>
        <button onclick="closeOrderDetails()" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
      <div id="orderDetailContent">
        <!-- Content will be loaded here -->
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  function showOrderDetails(orderId) {
    document.getElementById('orderDetailModal').classList.remove('hidden');
    document.getElementById('orderDetailModal').classList.add('flex');

    // Load order details via AJAX
    fetch(`/admin/orders/${orderId}/details`)
      .then(response => response.json())
      .then(data => {
        let content = `
        <div class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Kode Pesanan</label>
              <p class="mt-1 text-sm text-gray-900">${data.order_code || '#' + data.id}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Status</label>
              <p class="mt-1 text-sm text-gray-900">${data.status}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Pelanggan</label>
              <p class="mt-1 text-sm text-gray-900">${data.user.nama || data.user.name}</p>
              <p class="text-xs text-gray-500">${data.user.email}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Total</label>
              <p class="mt-1 text-sm text-gray-900">Rp ${new Intl.NumberFormat('id-ID').format(data.total_amount)}</p>
            </div>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Item Pesanan</label>
            <div class="space-y-2">
      `;

        data.order_items.forEach(item => {
          const isCustom = !item.product_id || item.garment_type.toLowerCase().includes('custom');
          const typeLabel = isCustom ? 'Order Custom' : 'Produk';
          const typeClass = isCustom ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800';

          content += `
          <div class="border rounded p-3 bg-gray-50">
            <div class="flex justify-between items-start">
              <div>
                <span class="px-2 py-1 rounded text-xs font-medium ${typeClass}">${typeLabel}</span>
                <h4 class="font-medium mt-1">${item.garment_type}</h4>
                ${item.fabric_type ? `<p class="text-sm text-gray-600">Bahan: ${item.fabric_type}</p>` : ''}
                ${item.size ? `<p class="text-sm text-gray-600">Ukuran: ${item.size}</p>` : ''}
                ${item.special_request ? `<p class="text-sm text-gray-600">Catatan: ${item.special_request}</p>` : ''}
              </div>
              <div class="text-right">
                <p class="font-medium">x${item.quantity}</p>
                <p class="text-sm text-gray-600">Rp ${new Intl.NumberFormat('id-ID').format(item.total_price)}</p>
              </div>
            </div>
          </div>
        `;
        });

        content += `
            </div>
          </div>
        </div>
      `;

        document.getElementById('orderDetailContent').innerHTML = content;
      })
      .catch(error => {
        document.getElementById('orderDetailContent').innerHTML = '<p class="text-red-600">Error loading order details</p>';
      });
  }

  function closeOrderDetails() {
    document.getElementById('orderDetailModal').classList.add('hidden');
    document.getElementById('orderDetailModal').classList.remove('flex');
  }

  function updateOrderStatus(orderId, status) {
    if (confirm(`Ubah status pesanan menjadi ${status}?`)) {
      fetch(`/admin/orders/${orderId}/status`, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({
            status: status
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            location.reload();
          } else {
            alert('Error updating status');
          }
        });
    }
  }
</script>
@endpush
@endsection