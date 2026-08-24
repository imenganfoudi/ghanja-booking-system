<!doctype html>
<html lang="fr" dir="ltr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
  <script src="https://cdn.tailwindcss.com"></script>

  <title>Prendre rendez-vous — Ghanja</title>

  <style>
    .bg-blue-teal-gradient {
      background: rgb(49, 130, 206);
      background: linear-gradient(90deg,rgba(126, 34, 206, 1) 0%,rgba(56, 178, 172, 1) 100%);
    }
  </style>
</head>

<body class="antialiased bg-gray-100 font-sans text-gray-900">

  <header class="bg-blue-teal-gradient px-4 sm:px-8 lg:px-16 xl:px-40 2xl:px-64 py-6">
    <div class="flex items-center justify-between">
      <a href="{{ route('home') }}" class="text-white font-bold text-2xl">Ghanja</a>
      <a href="{{ route('home') }}" class="text-white font-semibold hover:underline">&larr; Retour à l'accueil</a>
    </div>
  </header>

  <main class="px-4 sm:px-8 lg:px-16 xl:px-40 2xl:px-64 py-16">
    <div class="max-w-xl mx-auto">
      <h1 class="text-3xl font-bold text-gray-800 mb-1">Prenez rendez-vous</h1>
      <p class="text-gray-500 mb-8">Choisissez le service, la date et l'heure qui vous conviennent.</p>

      @if ($errors->any())
        <div class="mb-6 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
          @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
          @endforeach
        </div>
      @endif

      <form method="POST" action="{{ route('booking.store') }}" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-5">
        @csrf

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Service</label>
          <select name="service_id" id="service_id" required
            class="w-full rounded-lg border border-gray-300 focus:ring-teal-500 focus:border-teal-500">
            <option value="">-- Choisir un service --</option>
            @foreach ($services as $service)
              <option value="{{ $service->id }}" data-duration="{{ $service->duration_minutes }}"
                {{ old('service_id') == $service->id ? 'selected' : '' }}>
                {{ $service->name }} ({{ $service->duration_minutes }} min — {{ $service->price }} DT)
              </option>
            @endforeach
          </select>
        </div>

        <div>
          <div>
  <label class="block text-sm font-medium text-gray-700 mb-1">Avec (optionnel)</label>
  <select name="staff_id" id="staff_id"
    class="w-full rounded-lg border border-gray-300 focus:ring-purple-500 focus:border-purple-500">
    <option value="">-- Peu importe --</option>
    @foreach ($staff as $member)
      <option value="{{ $member->id }}" {{ old('staff_id') == $member->id ? 'selected' : '' }}>
        {{ $member->name }}
      </option>
    @endforeach
  </select>
</div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
          <input type="date" name="appointment_date" id="appointment_date" required
            min="{{ now()->toDateString() }}" value="{{ old('appointment_date') }}"
            class="w-full rounded-lg border border-gray-300 focus:ring-teal-500 focus:border-teal-500">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Heure</label>
          <div id="slots" class="grid grid-cols-4 gap-2 text-sm">
            <span class="col-span-4 text-gray-400">Choisissez un service et une date pour voir les créneaux disponibles</span>
          </div>
          <input type="hidden" name="start_time" id="start_time" required>
        </div>

        <hr>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet</label>
          <input type="text" name="customer_name" required value="{{ old('customer_name') }}"
            class="w-full rounded-lg border border-gray-300 focus:ring-teal-500 focus:border-teal-500">
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="customer_email" required value="{{ old('customer_email') }}"
              class="w-full rounded-lg border border-gray-300 focus:ring-teal-500 focus:border-teal-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
            <input type="text" name="customer_phone" required value="{{ old('customer_phone') }}"
              class="w-full rounded-lg border border-gray-300 focus:ring-teal-500 focus:border-teal-500">
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Remarques (facultatif)</label>
          <textarea name="notes" rows="2" class="w-full rounded-lg border border-gray-300 focus:ring-teal-500 focus:border-teal-500">{{ old('notes') }}</textarea>
        </div>

        <button type="submit"
          class="w-full bg-teal-500 hover:bg-teal-600 text-white font-semibold py-3 rounded-lg transition"t-semibold py-3 rounded-lg transition">
          Confirmer le rendez-vous
        </button>
      </form>
    </div>
  </main>

  <script>
    const serviceEl = document.getElementById('service_id');
    const dateEl = document.getElementById('appointment_date');
    const staffEl = document.getElementById('staff_id');
    const slotsEl = document.getElementById('slots');
    const startTimeEl = document.getElementById('start_time');

    async function loadSlots() {
      const serviceId = serviceEl.value;
      const date = dateEl.value;
      if (!serviceId || !date) return;

      slotsEl.innerHTML = '<span class="col-span-4 text-gray-400">Chargement...</span>';

      const params = new URLSearchParams({ service_id: serviceId, date: date });
      if (staffEl.value) params.append('staff_id', staffEl.value);
      const res = await fetch(`{{ route('booking.slots') }}?${params.toString()}`);
      const data = await res.json();

      slotsEl.innerHTML = '';
      startTimeEl.value = '';

      if (!data.slots.length) {
        slotsEl.innerHTML = '<span class="col-span-4 text-red-400">Aucun créneau disponible ce jour-là</span>';
        return;
      }

      data.slots.forEach(slot => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = slot;
        btn.className = 'border rounded-lg py-2 hover:bg-teal-50 hover:border-teal-400 transition';
        btn.onclick = () => {
          startTimeEl.value = slot;
          document.querySelectorAll('#slots button').forEach(b => b.classList.remove('bg-teal-500', 'text-white'));
          btn.classList.add('bg-teal-500', 'text-white');
        };
        slotsEl.appendChild(btn);
      });
    }

   serviceEl.addEventListener('change', loadSlots);
   dateEl.addEventListener('change', loadSlots);
   staffEl.addEventListener('change', loadSlots);
  </script>

</body>
</html>