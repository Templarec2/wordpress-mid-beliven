<html>
<head>
    <style>
        .options_log_box {
            border: 1px solid gray;
            padding: 10px;
        }
        .form {
        
        }
    </style>
</head>
<body>
<h1>Options</h1>
<?php
  $del_logs = get_option('del_logs');
  if ($del_logs=== 'true') {
    $del_logs_r = 'Sì';
  } else {
    $del_logs_r = 'No';
  }
  $ret_datetime_logs = get_option('retention_datetime_logs');

?>
<div class="options_log_box">
    <p>
        Cancellazione log alla disattivazione del plugin: <b><?php echo $del_logs_r ?></b>
    </p>
    <form method="POST">

        <p>Cancella log alla disattivazione del plugin?</p>
        <input type="radio" id="si" name="del_logs" value="true">
        <label for="si">Sì</label><br>
        <input type="radio" id="no" name="del_logs" value="false">
        <label for="no">No</label><br>
        <input type="submit" value="Salva" class="button button-primary button-large">
    </form>
</div>


<br>
<div class="options_log_box">
    <p>
        Cancellare log più vecchi di: <b><?php echo $ret_datetime_logs ?></b> giorni
    </p>
    <form method="POST">

        <p>Numero di giorni dei log da tenere: </p>
        <input type="number" id="date_ret" name="retention_datetime_logs">

        <input type="submit" value="Salva" class="button button-primary button-large">
    </form>
</div>
</body>
</html>

