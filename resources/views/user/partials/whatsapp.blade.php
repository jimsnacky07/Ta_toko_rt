@php
  $msg = isset($p) ? 'Halo, saya tertarik dengan produk: '.$p['name']
                   : 'Halo, saya ingin bertanya';
  $wa  = 'https://wa.me/6282169878747?text='.urlencode($msg);
@endphp

<a href="{{ $wa }}"
   class="fixed bottom-6 right-6 z-[99999] inline-flex items-center gap-2 px-4 py-2
          rounded-full bg-green-500 text-white shadow-lg hover:shadow-xl"
   target="_blank" rel="noreferrer">
  <img src="{{ asset('images/wa.png') }}" alt="WhatsApp" class="w-6 h-6">
  Chat Sekarang
</a>
