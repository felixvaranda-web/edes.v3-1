<?PHP
$mapFields = explode(",", eNsp($_MAP[0]));
$lastField = $mapFields[count($mapFields)-1];
$coordinateFields = explode(",", eNsp($_MAP[1]));
$sizeMap = explode(",", eNsp($_MAP[2]));
if( !isset($sizeMap[1]) ){
$sizeMap[1] = "50%";
$_JSEND .= <<<EOD
function loadingCustomize(){
S("#map").css({ height: S("#TABBorder").height() });
mapViewPoint();
}
EOD;
}
$initialCoordinates = "40.4168, -3.7038";  // Madrid por defecto
$zoomMap = 13;
$zoomMapDetail = 16;
eLngLoad( DIREDES."lng/map" );
eLngLoad( "../langs/map", "", 9 );
foreach($_Lng as $key=>$value){
${"lng".$key} = $value;
}
$_WINFORM = ["100%", "100%", "", $_TITLE];
$_HTMINI .= '<div class="BOX-MAP">';
$_HTMEND .= '<div id="map" style="width:{$sizeMap[0]}; height:{$sizeMap[1]};"></div></div>';
if( in_array($_Mode, ['a', 'mR'], true) ){
eCall_ICON(true, "a,mR | {$lastField} | gps    | onclick='seekAddres(\"{$_MAP[0]}\")' title='{$lngCalculatePoint}'");
eCall_ICON(true, "a,mR | {$lastField} | &#112; | onclick='deleteGPS()' title='{$lngDeletePoint}'");
$_JSEND .= <<<EOD
function seekAddres(seek){
var datas = S.nsp(seek).split(","), i, xSeek=[];
for(i=0; i<datas.length; i++){
xSeek[i] = eval(`\$\${datas[i]}`);
}
xSeek = xSeek.join(",");
deleteGPS();
searchAddress(xSeek);
}
function deleteGPS(){
if( !currentMarker ){
return;
}
map.removeLayer(currentMarker);
currentMarker = null;
S(`:\${_pointLatitude}`).val(0);
S(`:\${_pointLongitude}`).val(0);
}
EOD;
}
$_JSINI .= file_get_contents("lib/leaflet/leaflet.js");
$_JSEND .= <<<EOD
const _pointLatitude = "{$coordinateFields[0]}", _pointLongitude="{$coordinateFields[1]}";
const map = L.map('map').setView([{$initialCoordinates}], {$zoomMap});
L.tileLayer(
'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
{
attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}
).addTo(map);
function createCustomIcon() {
const customIconBase64 = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABkAAAApCAYAAADAk4LOAAAFgUlEQVR4Aa1XA5BjWRTN2oW17d3YaZtr2962HUzbDNpjszW24mRt28p47v7zq/bXZtrp/lWnXr337j3nPCe85NcypgSFdugCpW5YoDAMRaIMqRi6aKq5E3YqDQO3qAwjVWrD8Ncq/RBpykd8oZUb/kaJutow8r1aP9II0WmLKLIsJyv1w/kqw9Ch2MYdB++12Onxee/QMwvf4/Dk/Lfp/i4nxTXtOoQ4pW5Aj7wpici1A9erdAN2OH64x8OSP9j3Ft3b7aWkTg/Fm91siTra0f9on5sQr9INejH6CUUUpavjFNq1B+Oadhxmnfa8RfEmN8VNAsQhPqF55xHkMzz3jSmChWU6f7/XZKNH+9+hBLOHYozuKQPxyMPUKkrX/K0uWnfFaJGS1QPRtZsOPtr3NsW0uyh6NNCOkU3Yz+bXbT3I8G3xE5EXLXtCXbbqwCO9zPQYPRTZ5vIDXD7U+w7rFDEoUUf7ibHIR4y6bLVPXrz8JVZEql13trxwue/uDivd3fkWRbS6/IA2bID4uk0UpF1N8qLlbBlXs4Ee7HLTfV1j54APvODnSfOWBqtKVvjgLKzF5YdEk5ewRkGlK0i33Eofffc7HT56jD7/6U+qH3Cx7SBLNntH5YIPvODnyfIXZYRVDPqgHtLs5ABHD3YzLuespb7t79FY34DjMwrVrcTuwlT55YMPvOBnRrJ4VXTdNnYug5ucHLBjEpt30701A3Ts+HEa73u6dT3FNWwflY86eMHPk+Yu+i6pzUpRrW7SNDg5JHR4KapmM5Wv2E8Tfcb1HoqqHMHU+uWDD7zg54mz5/2BSnizi9T1Dg4QQXLToGNCkb6tb1NU+QAlGr1++eADrzhn/u8Q2YZhQVlZ5+CAOtqfbhmaUCS1ezNFVm2imDbPmPng5wmz+gwh+oHDce0eUtQ6OGDIyR0uUhUsoO3vfDmmgOezH0mZN59x7MBi++WDL1g/eEiU3avlidO671bkLfwbw5XV2P8Pzo0ydy4t2/0eu33xYSOMOD8hTf4CrBtGMSoXfPLchX+J0ruSePw3LZeK0juPJbYzrhkH0io7B3k164hiGvawhOKMLkrQLyVpZg8rHFW7E2uHOL888IBPlNZ1FPzstSJM694fWr6RwpvcJK60+0HCILTBzZLFNdtAzJaohze60T8qBzyh5ZuOg5e7uwQppofEmf2++DYvmySqGBuKaicF1blQjhuHdvCIMvp8whTTfZzI7RldpwtSzL+F1+wkdZ2TBOW2gIF88PBTzD/gpeREAMEbxnJcaJHNHrpzji0gQCS6hdkEeYt9DF/2qPcEC8RM28Hwmr3sdNyht00byAut2k3gufWNtgtOEOFGUwcXWNDbdNbpgBGxEvKkOQsxivJx33iow0Vw5S6SVTrpVq11ysA2Rp7gTfPfktc6zhtXBBC+adRLshf6sG2RfHPZ5EAc4sVZ83yCN00Fk/4kggu40ZTvIEm5g24qtU4KjBrx/BTTH8ifVASAG7gKrnWxJDcU7x8X6Ecczhm3o6YicvsLXWfh3Ch1W0k8x0nXF+0fFxgt4phz8QvypiwCCFKMqXCnqXExjq10beH+UUA7+nG6mdG/Pu0f3LgFcGrl2s0kNNjpmoJ9o4B29CMO8dMT4Q5ox8uitF6fqsrJOr8qnwNbRzv6hSnG5wP+64C7h9lp30hKNtKdWjtdkbuPA19nJ7Tz3zR/ibgARbhb4AlhavcBebmTHcFl2fvYEnW0ox9xMxKBS8btJ+KiEbq9zA4RthQXDhPa0T9TEe69gWupwc6uBUphquXgf+/FrIjweHQS4/pduMe5ERUMHUd9xv8ZR98CxkS4F2n3EUrUZ10EYNw7BWm9x1GiPssi3GgiGRDKWRYZfXlON+dfNbM+GgIwYdwAAAAASUVORK5CYII=";
return L.icon({
iconUrl: customIconBase64,
iconSize: [25, 41],
iconAnchor: [12, 41],
popupAnchor: [1, -34],
shadowSize: [41, 41]
});
}
let currentMarker = null;
function searchAddress(address) {
fetch(`https://nominatim.openstreetmap.org/search?format=json&q=\${encodeURIComponent(address)}`)
.then(response => response.json())
.then(data => {
if (data.length > 0) {
const lat = parseFloat(data[0].lat);
const lng = parseFloat(data[0].lon);
map.setView([lat, lng], {$zoomMapDetail});
S(`:\${_pointLatitude}`).val(lat);
S(`:\${_pointLongitude}`).val(lng);
if (currentMarker) {
map.removeLayer(currentMarker);
}
currentMarker = L.marker([lat, lng], {
draggable: true
,icon: createCustomIcon()
}).addTo(map);
currentMarker.bindPopup(`<b>{$lngUbicacion}:</b><br>\${data[0].display_name}`).openPopup();
currentMarker.on('dragend', function() {
const newPosition = currentMarker.getLatLng();
S(`:\${_pointLatitude}`).val(newPosition.lat);
S(`:\${_pointLongitude}`).val(newPosition.lng);
});
}else{
S(window.event).info('{$lngAddressNotFound}')
}
}).catch(error => {
console.error('Error:', error);
S(window.event).warning('{$lngErrorSearchingAddress}');
});
}
function mapViewPoint(){
var  lat = S(`:\${_pointLatitude}`).val()
,lng = S(`:\${_pointLongitude}`).val();
if( lat=="" ){
return;
}
currentMarker = L.marker([lat, lng], {
draggable: "{$_Mode}"=="mR"
,icon: createCustomIcon()
}).addTo(map);
map.setView([lat, lng], {$zoomMapDetail});
}
EOD;
$css = file_get_contents("lib/leaflet/leaflet.css");
$_CSSAFTER .= <<<EOD
<style>
{$css}
#delete-marker {
padding: 8px 12px;
background: #dc3545;
color: white;
border: none;
border-radius: 4px;
cursor: pointer;
}
.leaflet-popup-content-wrapper {
white-space: normal !important;
}
.BOX-MAP {
width:100%;
height:100%;
display: flex;
justify-content: center;
align-items: center;
justify-content: space-evenly;
}
</style>
EOD;