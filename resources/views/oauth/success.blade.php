<!DOCTYPE html>
<html>

<head>
    <title>OAuth Success</title>
</head>

<body>
    <script>
        const data = {
            token: @json($token),
            user: @json($user),
        };

        window.opener.postMessage(data, "http://localhost:5173");
        window.close();
    </script>
</body>

</html>
