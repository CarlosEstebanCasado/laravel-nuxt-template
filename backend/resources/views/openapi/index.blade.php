<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>OpenAPI Docs</title>
    <link rel="stylesheet" href="/openapi/swagger-ui.css" />
    <style>
      body {
        margin: 0;
      }
    </style>
  </head>
  <body>
    <div id="swagger-ui"></div>
    <script src="/openapi/swagger-ui-bundle.js"></script>
    <script>
      window.ui = SwaggerUIBundle({
        url: "{{ route('openapi.spec') }}",
        dom_id: "#swagger-ui",
      });
    </script>
  </body>
</html>
