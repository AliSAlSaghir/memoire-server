<!DOCTYPE html>
<html>

<head>
    <title>Capsule Revealed</title>
</head>

<body>
    <h1>Your Capsule is Revealed!</h1>
    <p>Title: {{ $capsule->title }}</p>
    <p>Message: {{ $capsule->message }}</p>
    <p>Reveal Date: {{ $capsule->reveal_at }}</p>
</body>

</html>
