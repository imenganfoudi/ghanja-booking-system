<!doctype html>
<html lang="fr" dir="ltr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Employés — Admin</title>
</head>

<body class="bg-gray-100 min-h-screen flex">

  <aside class="w-56 bg-gray-900 text-gray-300 flex flex-col shrink-0">
    <div class="px-5 py-5 text-white font-bold text-lg border-b border-gray-800">
      ✂️ Admin
    </div>
    <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
      <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-lg hover:bg-gray-800">📊 Tableau de bord</a>
      <a href="{{ route('admin.appointments.index') }}" class="block px-3 py-2 rounded-lg hover:bg-gray-800">📅 Rendez-vous</a>
      <a href="{{ route('admin.services.index') }}" class="block px-3 py-2 rounded-lg hover:bg-gray-800">🛠️ Services</a>
      <a href="{{ route('admin.staff.index') }}" class="block px-3 py-2 rounded-lg bg-gray-800 text-white">👥 Employés</a>
    </nav>
    <form method="POST" action="{{ route('logout') }}" class="px-3 py-4 border-t border-gray-800">
      @csrf
      <button class="w-full text-left px-3 py-2 rounded-lg hover:bg-gray-800 text-sm">🚪 Déconnexion</button>
    </form>
  </aside>

  <div class="flex-1 flex flex-col">
    <header class="bg-white border-b px-6 py-4 flex items-center justify-between">
      <h1 class="text-lg font-semibold text-gray-800">Employés</h1>
      <span class="text-sm text-gray-500">{{ auth()->user()->name }}</span>
    </header>

    <main class="flex-1 p-6">
      @if (session('success'))
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 text-green-700 px-4 py-3 text-sm">
          {{ session('success') }}
        </div>
      @endif

      @if ($errors->any())
        <div class="mb-6 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
          @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
          @endforeach
        </div>
      @endif

      {{-- Formulaire d'ajout --}}
      <div class="bg-white rounded-2xl border shadow-sm p-6 mb-6">
        <h2 class="font-semibold text-gray-700 mb-4">Ajouter un employé</h2>
        <form method="POST" action="{{ route('admin.staff.store') }}" class="grid grid-cols-2 md:grid-cols-4 gap-3 items-end">
          @csrf
          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Nom</label>
            <input type="text" name="name" required class="w-full rounded-lg border border-gray-300 text-sm">
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
            <input type="email" name="email" class="w-full rounded-lg border border-gray-300 text-sm">
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Téléphone</label>
            <input type="text" name="phone" class="w-full rounded-lg border border-gray-300 text-sm">
          </div>
          <button class="bg-blue-600 hover:bg-purple-800 text-white text-sm px-4 py-2 rounded-lg">Ajouter</button>
        </form>
      </div>

      {{-- Liste des employés --}}
      <div class="bg-white rounded-2xl border shadow-sm">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-500">
            <tr>
              <th class="text-left px-5 py-2">Nom</th>
              <th class="text-left px-5 py-2">Email</th>
              <th class="text-left px-5 py-2">Téléphone</th>
              <th class="text-left px-5 py-2">Actif</th>
              <th class="text-left px-5 py-2">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            @forelse ($staff as $member)
              <tr>
                <form method="POST" action="{{ route('admin.staff.update', $member) }}">
                  @csrf @method('PUT')
                  <td class="px-5 py-3">
                    <input type="text" name="name" value="{{ $member->name }}" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:ring-purple-500 focus:border-purple-500">
                  </td>
                  <td class="px-5 py-3">
                    <input type="email" name="email" value="{{ $member->email }}" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:ring-purple-500 focus:border-purple-500">
                  </td>
                  <td class="px-5 py-3">
                    <input type="text" name="phone" value="{{ $member->phone }}" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:ring-purple-500 focus:border-purple-500">
                  </td>
                  <td class="px-5 py-3">
                    <input type="checkbox" name="is_active" value="1" {{ $member->is_active ? 'checked' : '' }}>
                  </td>
                  <td class="px-5 py-3 flex gap-3">
                    <button type="submit" class="text-purple-700 hover:underline text-xs">Enregistrer</button>
                </form>
                    <form method="POST" action="{{ route('admin.staff.destroy', $member) }}" onsubmit="return confirm('Confirmer la suppression ?')">
                      @csrf @method('DELETE')
                      <button class="text-red-500 hover:underline text-xs">Supprimer</button>
                    </form>
                  </td>
              </tr>
            @empty
              <tr><td colspan="5" class="px-5 py-6 text-center text-gray-400">Aucun employé</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </main>
  </div>

</body>
</html>