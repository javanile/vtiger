<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>vtiger | loading...</title>
<meta name="author" content="">
<meta name="description" content="">
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family:arial;font-size:15px">
<div style="text-align:center;margin-top:50px">
<img src="/layouts/v7/resources/Images/vtiger.png" alt="vtiger" width="300" />
<p>
    <img src="/libraries/jquery/select2/spinner.gif"
         alt="loading..."
         style="vertical-align:baseline;margin-bottom:-2px" />
    %%MESSAGE%%
</p>
</div>
<script>
    setTimeout(function() {
        window.location.replace('/index.php')
    }, 15000)
</script>
</body>
</html>
