<!doctype html>
<html lang="en">
<head>
    <meta name="viewport" content="initial-scale=1"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="$description">
    <meta content="width=device-width, initial-scale=1, user-scalable=no" name="viewport">

    {{! charset("utf-8") !}}
    {{! pageTitle($title) !}}

    <link rel="shortcut icon" type="image/png" href="/favicon.png">

    {{! css("https://fonts.googleapis.com/css?family=Material+Icons") !}}

    {{! assetCss('admin-layout') !}}
    <% if ($page) %>
    {{! assetCss($page) !}}
    <% endif %>

    {{! assetJs('admin-layout-header') !}}
    <% if ($pageHeader) %>
    {{! assetJs($pageHeader) !}}
    <% endif %>
</head>

<body class="body-custom">
    <% show("content") %>

    <!-- Scripts Starts -->
    {{! assetJs('admin-layout-footer') !}}
    <% if ($pageFooter) %>
    {{! assetJs($pageFooter) !}}
    <% endif %>
    <!-- Scripts Ends -->
</body>
</html>