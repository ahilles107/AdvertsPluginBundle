{{ dynamic }}
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>{{block title}}Ogłoszenia{{/block}}</title>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
    {{block head}}
    <!-- Bootstrap core CSS -->
    <link href="/public/bundles/ahsadvertsplugin/css/bootstrap.min.css" rel="stylesheet">
    <link href="/public/bundles/ahsadvertsplugin/css/main.css" rel="stylesheet">
    {{/block}}
  </head>

  <body>
    <div class="container">
    {{block body}}{{/block}}
    {{block sidebar}}{{/block}}
    </div>
  </body>
</html>
{{ /dynamic }}