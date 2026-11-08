@props(['title'])

<!DOCTYPE html>
<html lang="id">
<head>
<x-layouts.head :title="$title" />
</head>
<body class="min-h-screen bg-canvas font-sans text-ink antialiased">
{{ $slot }}
@stack('scripts')
</body>
</html>
