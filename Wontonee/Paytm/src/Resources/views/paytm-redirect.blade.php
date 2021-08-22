<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Redirecting to paytm gateway</title>
</head>
<body>
    <center>
        <h1>Please do not refresh this page...</h1>
    </center>
    <form method='post' action='{{ $url }}' name='paytm_form'>
        @foreach($paytmParams as $key => $value)
        <input type="hidden" value="{{ $value }}" name="{{ $key }}">
        @endforeach
        <input type="hidden" name="CHECKSUMHASH" value="{{ $checksum }}">
    </form>
    <script type="text/javascript">
   document.paytm_form.submit();
    </script>
</body>
</html>