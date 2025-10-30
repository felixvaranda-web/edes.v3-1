<?PHP
$logo = "logo.png";
$seconds = 5;
$title = "Mantenimiento en curso"
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?=$title?></title>
<style>
body {
font-family: 'Arial', sans-serif;
background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
margin: 0;
padding: 0;
height: 100vh;
display: flex;
justify-content: center;
align-items: center;
color: #333;
text-align: center;
}
.maintenance-container {
background-color: white;
border-radius: 15px;
box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
padding: 40px;
max-width: 550px;
width: 90%;
}
.logo {
max-width: 200px;
margin-bottom: 30px;
}
h1 {
color: #2c3e50;
margin-bottom: 20px;
}
p {
font-size: 18px;
line-height: 1.6;
margin-bottom: 30px;
}
.countdown {
font-size: 24px;
font-weight: bold;
color: #3498db;
margin: 20px 0;
}
.contact {
display:none;
margin-top: 30px;
font-style: italic;
color: #7f8c8d;
}
.spinner {
border: 5px solid #f3f3f3;
border-top: 5px solid #3498db;
border-radius: 50%;
width: 50px;
height: 50px;
animation: spin 2s linear infinite;
margin: 0 auto 30px;
}
@keyframes spin {
0% { transform: rotate(0deg); }
100% { transform: rotate(360deg); }
}
</style>
</head>
<body>
<div class="maintenance-container">
<img src="<?=$logo?>" alt="Logo de la empresa" class="logo">
<div class="spinner"></div>
<h1>Estamos mejorando para usted</h1>
<p>Nuestro servicio se encuentra actualmente en mantenimiento para realizar actualizaciones y mejoras que le brindarán una mejor experiencia.</p>
<div class="countdown" id="countdown">
Tiempo restante: calculando...
</div>
<p>Lamentamos los inconvenientes que esto pueda causar y agradecemos su comprensión.</p>
<div class="contact">
soporte@empresa.com | Tel: +1 234 567 890
</div>
</div>
<script>
function startCountdown(totalSeconds){
const countdownElement = document.getElementById('countdown');
function updateCountdown() {
const hours = Math.floor(totalSeconds / 3600);
const minutes = Math.floor((totalSeconds % 3600) / 60);
const seconds = totalSeconds % 60;
const formattedTime = [
hours.toString().padStart(2, '0'),
minutes.toString().padStart(2, '0'),
seconds.toString().padStart(2, '0')
].join(':');
countdownElement.textContent = `Tiempo restante: ${formattedTime}`;
if( totalSeconds <= 0 ){
clearInterval(countdownInterval);
countdownElement.textContent = "¡El mantenimiento ha finalizado!";
}else{
totalSeconds--;
}
}
const countdownInterval = setInterval(updateCountdown, 1000);
updateCountdown();
}
startCountdown(<?=$seconds?>);
</script>
</body>
</html>