<!DOCTYPE html>
<html>

<head>
    <title>Google OAuth Success</title>
</head>

<body>
    <script>
        window.opener.postMessage({
                token: "{{ $token }}",
                user: @json($user)
            },
            "http://localhost:5173"
        );
        window.close();
    </script>
</body>

</html>
