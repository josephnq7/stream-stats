<html>
<head>
  <meta charset="utf-8">
  <title>{{ config('app.name') }}</title>
  <script>
    window.opener.postMessage({ token: "{{ $token }}", expire_at: {{$expireAt}} }, "{{ url('/') }}")
    window.close()
  </script>
</head>
<body>
</body>
</html>
