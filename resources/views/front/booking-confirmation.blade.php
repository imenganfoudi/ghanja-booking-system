<!doctype html>
<html lang="fr" dir="ltr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
  <script src="https://cdn.tailwindcss.com"></script>

  <title>Rendez-vous confirmé — Ghanja</title>

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
    </div>
  </header>

  <main class="px-4 sm:px-8 lg:px-16 xl:px-40 2xl:px-64 py-16">
    <div class="max-w-lg mx-auto text-center bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
      <div class="text-5xl mb-4">✅</div>
      <h1 class="text-2xl font-bold text-gray-800 mb-2">Demande de rendez-vous envoyée !</h1>
      <p class="text-gray-500 mb-6">Nous vous contacterons pour confirmer le rendez-vous.</p>

      <div class="text-left bg-gray-50 rounded-xl p-4 space-y-2 text-sm">
        <div><span class="text-gray-500">Service :</span> <strong>{{ $appointment->service->name }}</strong></div>
        <div><span class="text-gray-500">Date :</span> <strong>{{ $appointment->appointment_date->format('d/m/Y') }}</strong></div>
        <div><span class="text-gray-500">Heure :</span> <strong>{{ \Carbon\Carbon::parse($appointment->start_time)->format('H:i') }}</strong></div>
        <div><span class="text-gray-500">Statut :</span>
          <span class="inline-block px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700 text-xs">En attente</span>
        </div>
      </div>

      <a href="{{ route('home') }}" class="inline-block mt-6 text-teal-600 hover:underline text-sm">
        &larr; Retour à l'accueil
      </a>
    </div>
  </main>

</body>
</html>