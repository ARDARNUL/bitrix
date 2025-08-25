<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Balance API Documentation</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@3.25.0/swagger-ui.css" />
    <style>
        body { margin: 0; padding: 0; }
        #swagger-ui { padding: 20px; }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>

    <script src="https://unpkg.com/swagger-ui-dist@3.25.0/swagger-ui-bundle.js"></script>
    <script>
        const ui = SwaggerUIBundle({
            url: '/local/api/swagger.php',
            dom_id: '#swagger-ui',
            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIBundle.presets.standalone
            ],
            layout: "BaseLayout"
        });
    </script>
</body>
</html>
<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
?>