<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Acceso Bloqueado</title>
<style>
body {
font-family: Arial, sans-serif;
background-color: #f4f4f4;
color: #333;
margin: 0;
display: flex;
justify-content: center;
align-items: center;
min-height: 100vh;
padding: 20px;
box-sizing: border-box;
}
.container {
background-color: #fff;
padding: 30px 50px;
border-radius: 8px;
box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
max-width: 550px;
width: 100%;
text-align: center;
}
h1 {
color: #d9534f;
margin-bottom: 20px;
}
p {
font-size: 1.1em;
line-height: 1.6em;
}
.timer {
font-size: 1.3em;
font-weight: bold;
color: #55ac55;
margin-top: 20px;
padding: 0 0.5rem;
letter-spacing: 1px;
}
</style>
</head>
<body>
<div class="container">
<h1>Acceso Temporalmente Bloqueado</h1>
<p>Tu acceso ha sido bloqueado debido a múltiples intentos fallidos o actividad sospechosa.</p>
<p>Por favor, espera <span class="timer" id="countdown"></span> antes de intentar nuevamente.</p>
<p>Si crees que esto es un error, por favor, ponte en contacto con el soporte técnico.</p>
</div>
<script>
var totalSecondsRemaining = <?= $blockedTime ?>;
function updateCountdown() {
if( totalSecondsRemaining < 0 ){
totalSecondsRemaining = 0;
}
var  currentMinutes = Math.floor(totalSecondsRemaining / 60)
,currentSeconds = totalSecondsRemaining % 60
,formattedMinutes = currentMinutes < 10 ? "0" + currentMinutes : currentMinutes
,formattedSeconds = currentSeconds < 10 ? "0" + currentSeconds : currentSeconds;
document.getElementById('countdown').textContent = formattedMinutes + ":" + formattedSeconds;
if( totalSecondsRemaining <= 0 ){
clearInterval(countdownInterval);
var obj = document.getElementById('countdown').parentNode;
obj.className = "timer";
obj.textContent = "Puedes intentar de nuevo.";
}else{
totalSecondsRemaining--;
}
}
var countdownInterval = setInterval(updateCountdown, 1000);
updateCountdown();
</script>
</body>
</html>