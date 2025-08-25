{{-- Snap.js (Sandbox) --}}
<script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('midtrans.client_key') }}"></script>

{{-- tombol --}}
<button id="btn-pay"
        data-order-id="{{ $order->id }}"
        class="btn btn-success btn-lg">
  Lanjut ke Pembayaran
</button>

{{-- CSRF meta kalau belum ada --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
const btn = document.getElementById('btn-pay');
btn.addEventListener('click', async () => {
  // cegah double click
  if (btn.disabled) return;
  btn.disabled = true;
  btn.innerText = 'Memproses...';

  try {
    const id = btn.dataset.orderId;

    // minta snap token ke server
    const res = await fetch(`/user/pay/${id}`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json'
      }
    });
    const data = await res.json();

    if (data.already_paid) {
      alert('Pesanan sudah dibayar.');
      location.reload();
      return;
    }

    // munculkan popup
    window.snap.pay(data.token, {
      onSuccess:  function(){ window.location.href = "{{ route('midtrans.finish') }}"; },
      onPending:  function(){ window.location.href = "{{ route('midtrans.finish') }}"; },
      onError:    function(){ window.location.href = "{{ route('midtrans.error') }}"; },
      onClose:    function(){ btn.disabled = false; btn.innerText = 'Lanjut ke Pembayaran'; }
    });
  } catch (e) {
    console.error(e);
    alert('Gagal memulai pembayaran.');
    btn.disabled = false;
    btn.innerText = 'Lanjut ke Pembayaran';
  }
});
</script>
