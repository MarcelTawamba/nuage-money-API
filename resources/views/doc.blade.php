
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, shrink-to-fit=no"
            />

    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link rel="shortcut icon" href="icon-48x48.png" />

    <link rel="canonical" href="https://demo-basic.adminkit.io/" />

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap"
        rel="stylesheet"
            />
    <title>{{ config('app.name') }}</title>

</head>

<body >
    <div >
        <redoc spec-url="{{url('api-docs/api-docs.json')}}"></redoc>

    </div>
</body>

<script src="https://cdn.redoc.ly/redoc/latest/bundles/redoc.standalone.js"> </script>
</html>


