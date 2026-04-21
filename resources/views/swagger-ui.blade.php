<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vocaseek Swagger UI</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
    <style>
        body {
            margin: 0;
            background: #f8fafc;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 20px;
            background: linear-gradient(90deg, #1d4ed8, #f59e0b);
            color: #fff;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        .title {
            font-size: 18px;
            font-weight: 700;
        }

        .links {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .links a {
            color: #fff;
            text-decoration: none;
            padding: 8px 12px;
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
        }

        #swagger-ui {
            max-width: 1280px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="title">Vocaseek Swagger UI</div>
        <div class="links">
            <a href="{{ route('docs.api') }}">Docs Ringkas</a>
            <a href="{{ route('docs.openapi') }}" target="_blank" rel="noopener noreferrer">Raw OpenAPI YAML</a>
            <a href="{{ route('docs.api-readme') }}" target="_blank" rel="noopener noreferrer">API README</a>
        </div>
    </div>

    <div id="swagger-ui"></div>

    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function () {
            window.ui = SwaggerUIBundle({
                url: "{{ route('docs.openapi') }}",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                layout: "BaseLayout",
                docExpansion: "list",
                persistAuthorization: true,
                displayRequestDuration: true,
                tryItOutEnabled: true,
            });
        };
    </script>
</body>
</html>
