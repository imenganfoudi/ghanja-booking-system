<!doctype html>
<html lang="fr" dir="ltr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Tableau de bord — Admin</title>
</head>

<body class="bg-gray-100 min-h-screen flex">

  <aside class="w-56 bg-gray-900 text-gray-300 flex flex-col shrink-0">
    <div class="px-5 py-5 text-white font-bold text-lg border-b border-gray-800">
      ✂️ Admin
    </div>
    <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
      <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-lg bg-gray-800 text-white">📊 Tableau de bord</a>
      <a href="{{ route('admin.appointments.index') }}" class="block px-3 py-2 rounded-lg hover:bg-gray-800">📅 Rendez-vous</a>
      <a href="{{ route('admin.services.index') }}" class="block px-3 py-2 rounded-lg hover:bg-gray-800">🛠️ Services</a>
      <a href="{{ route('admin.staff.index') }}" class="block px-3 py-2 rounded-lg hover:bg-gray-800">👥 Équipe</a>
    </nav>
    <form method="POST" action="{{ route('logout') }}" class="px-3 py-4 border-t border-gray-800">
      @csrf
      <button class="w-full text-left px-3 py-2 rounded-lg hover:bg-gray-800 text-sm">🚪 Déconnexion</button>
    </form>
  </aside>

  <div class="flex-1 flex flex-col">
    <header class="bg-white border-b px-6 py-4 flex items-center justify-between">
      <h1 class="text-lg font-semibold text-gray-800">Tableau de bord</h1>
      <span class="text-sm text-gray-500">{{ auth()->user()->name }}</span>
    </header>

    <main class="flex-1 p-6">
      @if (session('success'))
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 text-green-700 px-4 py-3 text-sm">
          {{ session('success') }}
        </div>
      @endif

      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="rounded-2xl p-5 bg-teal-50 text-teal-700">
          <div class="text-2xl font-bold">{{ $stats['today'] }}</div>
          <div class="text-sm mt-1">Aujourd'hui</div>
        </div>
        <div class="rounded-2xl p-5 bg-yellow-50 text-yellow-700">
          <div class="text-2xl font-bold">{{ $stats['pending'] }}</div>
          <div class="text-sm mt-1">En attente</div>
        </div>
        <div class="rounded-2xl p-5 bg-green-50 text-green-700">
          <div class="text-2xl font-bold">{{ $stats['confirmed'] }}</div>
          <div class="text-sm mt-1">Confirmés</div>
        </div>
        <div class="rounded-2xl p-5 bg-purple-50 text-purple-700">
          <div class="text-2xl font-bold">{{ $stats['services'] }}</div>
          <div class="text-sm mt-1">Services</div>
        </div>
      </div>

      <div class="bg-white rounded-2xl border shadow-sm">
        <div class="px-5 py-4 border-b font-semibold text-gray-700">Rendez-vous d'aujourd'hui</div>
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-500">
            <tr>
              <th class="text-left px-5 py-2">Heure</th>
              <th class="text-left px-5 py-2">Client</th>
              <th class="text-left px-5 py-2">Service</th>
              <th class="text-left px-5 py-2">Statut</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            @forelse ($todayAppointments as $apt)
              <tr>
                <td class="px-5 py-3">{{ \Carbon\Carbon::parse($apt->start_time)->format('H:i') }}</td>
                <td class="px-5 py-3">{{ $apt->customer_name }}</td>
                <td class="px-5 py-3">{{ $apt->service->name }}</td>
                <td class="px-5 py-3">{{ $apt->status }}</td>
              </tr>
            @empty
              <tr><td colspan="4" class="px-5 py-6 text-center text-gray-400">Aucun rendez-vous aujourd'hui</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </main>
  </div>

</body>
</html>