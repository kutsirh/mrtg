
<?php
require_once 'api.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?= APP_NAME ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">

<style>

body{
    background:#eef2f7;
}

.navbar-brand{
    font-weight:bold;
}

.card-summary{
    border:0;
    border-radius:15px;
    box-shadow:0 5px 15px rgba(0,0,0,.10);
    transition:.25s;
}

.card-summary:hover{
    transform:translateY(-4px);
}

.summary-number{
    font-size:38px;
    font-weight:bold;
}

.device-card{
    border:0;
    border-radius:15px;
    box-shadow:0 5px 15px rgba(0,0,0,.10);
    transition:.25s;
}

.device-card:hover{
    transform:translateY(-4px);
}

.led{

    width:14px;

    height:14px;

    border-radius:50%;

    display:inline-block;

    margin-right:6px;

}

.led-online{

    background:#16a34a;

    animation:blink 1s infinite;

}

.led-offline{

    background:#dc3545;

}

.led-stale{

    background:#ffc107;

}

@keyframes blink{

0%{opacity:1;}

50%{opacity:.2;}

100%{opacity:1;}

}

.progress{

height:18px;

}

.search-box{

max-width:350px;

}

.footer{

font-size:13px;

color:#666;

margin-top:30px;

}

</style>

</head>

<body>

<nav class="navbar navbar-dark bg-primary">

<div class="container-fluid">

<span class="navbar-brand">

<i class="fa-solid fa-network-wired"></i>

<?= APP_NAME ?>

</span>

<span class="text-white" id="lastUpdate">

Memuat data...

</span>

</div>

</nav>

<div class="container mt-4">

<div class="row g-3">

<div class="col-md-3">

<div class="card card-summary">

<div class="card-body text-center">

<div class="summary-number text-primary" id="totalDevice">

0

</div>

<div>Total Perangkat</div>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card card-summary">

<div class="card-body text-center">

<div class="summary-number text-success" id="onlineDevice">

0

</div>

<div>Online</div>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card card-summary">

<div class="card-body text-center">

<div class="summary-number text-danger" id="offlineDevice">

0

</div>

<div>Offline</div>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card card-summary">

<div class="card-body text-center">

<div class="summary-number text-warning" id="staleDevice">

0

</div>

<div>Tidak Update</div>

</div>

</div>

</div>

</div>

<div class="card mt-4">

<div class="card-body">

<div class="d-flex justify-content-between align-items-center flex-wrap">

<div>

<b>Persentase Online</b>

</div>

<div>

<span id="progressText">

0%

</span>

</div>

</div>

<div class="progress mt-2">

<div
id="progressBar"
class="progress-bar bg-success"
style="width:0%;">

</div>

</div>

</div>

</div>

<div class="d-flex justify-content-between align-items-center mt-4 flex-wrap">

<input
type="text"
class="form-control search-box"
id="search"
placeholder="Cari IP atau Nama Perangkat...">

<button
class="btn btn-primary mt-2 mt-md-0"
id="btnRefresh">

<i class="fa-solid fa-rotate"></i>

Refresh

</button>

</div>

<div class="row mt-4" id="deviceContainer">

<!-- Device Card -->

</div>

<div class="footer text-center">

Kutsi Rhofik © <?= date('Y') ?>

</div>

<script>

let devices=[];

const API="api.php";


// ============================================
// Ambil Data dari API
// ============================================

async function loadData(){

    try{

        const response = await fetch(API+"?_="+Date.now());

        const json = await response.json();

        if(!json.success){

            console.error(json.message);

            return;

        }

        devices = json.data;

        render();

    }catch(err){

        console.error(err);

    }

}

// ============================================
// Menentukan Icon Perangkat
// ============================================

function getIcon(nama){

    nama = String(nama).toLowerCase();

    if(nama.includes("gateway")) return "🌐";

    if(nama.includes("switch")) return "📡";

    if(nama.includes("server")) return "🖥️";

    if(nama.includes("ruijie")) return "📶";
	
	if(nama.includes("tenda")) return "📶";

	if(nama.includes("totolink")) return "📶";
	
	if(nama.includes("zte")) return "📶";
	
	if(nama.includes("asus")) return "📶";

    if(nama.includes("hub")) return "🔀";

    if(nama.includes("printer")) return "🖨️";

    if(nama.includes("ycc365")) return "📷";

    if(nama.includes("ezviz")) return "📷";
	
	if(nama.includes("v380")) return "📷";
	
	if(nama.includes("icsee")) return "📷";

    if(nama.includes("pc")) return "💻";

    return "🖧";

}

// ============================================
// Render Dashboard
// ============================================

function render(){

    const keyword = document
        .getElementById("search")
        .value
        .toLowerCase();

    let total = devices.length;

    let online = 0;

    let offline = 0;

    let stale = 0;

    let html = "";

    const now = new Date();

    devices.forEach(function(item){

        let waktu = new Date(item.waktu);

        let selisih = (now - waktu) / 1000;

        let status = item.status;

        let badge = "success";

        let led = "led-online";

        if(selisih > 120){

            status = "STALE";

            badge = "warning";

            led = "led-stale";

            stale++;

        }
        else if(item.status === "ONLINE"){

            online++;

        }
        else{

            offline++;

            badge = "danger";

            led = "led-offline";

        }

        if(keyword){

            if(

                item.nama.toLowerCase().indexOf(keyword) === -1 &&

                item.ip.toLowerCase().indexOf(keyword) === -1

            ){

                return;

            }

        }

        html += `

<div class="col-lg-4 col-md-6 mb-4">

<div class="card device-card h-100">

<div class="card-body">

<h5>

${getIcon(item.nama)}

${item.nama}

</h5>

<div class="text-muted">

${item.ip}

</div>

<hr>

<span class="led ${led}"></span>

<span class="badge bg-${badge}">

${status}

</span>

<div class="mt-3">

<small>

<i class="fa-regular fa-clock"></i>

${waktu.toLocaleString("id-ID")}

</small>

</div>

</div>

</div>

</div>

`;

    });

    document.getElementById("deviceContainer").innerHTML = html;

    document.getElementById("totalDevice").innerHTML = total;

    document.getElementById("onlineDevice").innerHTML = online;

    document.getElementById("offlineDevice").innerHTML = offline;

    document.getElementById("staleDevice").innerHTML = stale;

    let persen = 0;

    if(total > 0){

        persen = Math.round((online / total) * 100);

    }

    document.getElementById("progressBar").style.width = persen + "%";

    document.getElementById("progressText").innerHTML = persen + "%";

    document.getElementById("lastUpdate").innerHTML =
        now.toLocaleString("id-ID");


}

// ============================================
// Event Pencarian
// ============================================

document
.getElementById("search")
.addEventListener("keyup",function(){

    render();

});

// ============================================
// Tombol Refresh
// ============================================

document
.getElementById("btnRefresh")
.addEventListener("click",function(){

    loadData();

});

// ============================================
// Auto Refresh
// ============================================

loadData();

setInterval(function(){

    loadData();

},5000);

</script>

</body>

</html>


