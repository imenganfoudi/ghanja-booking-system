<!doctype html>
<html lang="fr" dir="ltr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Rendez-vous — Admin</title>
</head>

<body class="bg-gray-100 min-h-screen flex">

  <aside class="w-56 bg-gray-900 text-gray-300 flex flex-col shrink-0">
    <div class="px-5 py-5 text-white font-bold text-lg border-b border-gray-800">
      ✂️ Admin
    </div>
    <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
      <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-lg hover:bg-gray-800">📊 Tableau de bord</a>
      <a href="{{ route('admin.appointments.index') }}" class="block px-3 py-2 rounded-lg bg-gray-800 text-white">📅 Rendez-vous</a>
      <a href="{{ route('admin.services.index') }}" class="block px-3 py-2 rounded-lg hover:bg-gray-800">🛠️ Services</a>
      <a href="{{ route('admin.staff.index') }}" class="block px-3 py-2 rounded-lg hover:bg-gray-800">👥 Employés</a>
    </nav>
    <form method="POST" action="{{ route('logout') }}" class="px-3 py-4 border-t border-gray-800">
      @csrf
      <button class="w-full text-left px-3 py-2 rounded-lg hover:bg-gray-800 text-sm">🚪 Déconnexion</button>
    </form>
  </aside>

  <div class="flex-1 flex flex-col">
    <header class="bg-white border-b px-6 py-4 flex items-center justify-between">
      <h1 class="text-lg font-semibold text-gray-800">Rendez-vous</h1>
      <span class="text-sm text-gray-500">{{ auth()->user()->name }}</span>
    </header>

    <main class="flex-1 p-6">
      @if (session('success'))
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 text-green-700 px-4 py-3 text-sm">
          {{ session('success') }}
        </div>
      @endif

      <div class="bg-white rounded-2xl border shadow-sm">
        <div class="px-5 py-4 border-b flex items-center justify-between">
          <span class="font-semibold text-gray-700">Tous les rendez-vous</span>
          <form method="GET" class="flex gap-2">
            <select name="status" onchange="this.form.submit()" class="rounded-lg border border-gray-300 text-sm">
              <option value="">Tous les statuts</option>
              @foreach (['pending' => 'En attente', 'confirmed' => 'Confirmé', 'cancelled' => 'Annulé', 'completed' => 'Terminé'] as $key => $label)
                <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
          </form>
        </div>

        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-500">
            <tr>
              <th class="text-left px-5 py-2">Date</th>
              <th class="text-left px-5 py-2">Heure</th>
              <th class="text-left px-5 py-2">Client</th>
              <th class="text-left px-5 py-2">Service</th>
              <th class="text-left px-5 py-2">Employé</th>
              <th class="text-left px-5 py-2">Statut</th>
              <th class="text-left px-5 py-2">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            @forelse ($appointments as $apt)
              <tr>
                <td class="px-5 py-3">{{ $apt->appointment_date->format('d/m/Y') }}</td>
                <td class="px-5 py-3">{{ \Carbon\Carbon::parse($apt->start_time)->format('H:i') }}</td>
                <td class="px-5 py-3">
                  {{ $apt->customer_name }}<br>
                  <span class="text-xs text-gray-400">{{ $apt->customer_phone }}</span>
                </td>
                <td class="px-5 py-3">{{ $apt->service->name }}</td>
                <td class="px-5 py-3">{{ $apt->staff->name ?? '—' }}</td>
                <td class="px-5 py-3">
                  <form method="POST" action="{{ route('admin.appointments.status', $apt) }}">
                    @csrf @method('PATCH')
                    <select name="status" onchange="this.form.submit()" class="text-xs rounded-lg border border-gray-300">
                      @foreach (['pending' => 'En attente', 'confirmed' => 'Confirmé', 'cancelled' => 'Annulé', 'completed' => 'Terminé'] as $key => $label)
                        <option value="{{ $key }}" {{ $apt->status == $key ? 'selected' : '' }}>{{ $label }}</option>
                      @endforeach
                    </select>
                  </form>
                </td>
                <td class="px-5 py-3">
                  <form method="POST" action="{{ route('admin.appointments.destroy', $apt) }}" onsubmit="return confirm('Confirmer la suppression ?')">
                    @csrf @method('DELETE')
                    <button class="text-red-500 hover:underline text-xs">Supprimer</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="7" class="px-5 py-6 text-center text-gray-400">Aucun rendez-vous</td></tr>
            @endforelse
          </tbody>
        </table>

        <div class="px-5 py-4">{{ $appointments->links() }}</div>
      </div>
    </main>
  </div>

</body>
</html>