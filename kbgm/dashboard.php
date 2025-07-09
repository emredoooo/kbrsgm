<?php 
// Panggil header.php yang sudah berisi template, session, dan auth
require_once 'includes/header.php'; 
?>

<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">Dashboard</h1>
      </div></div></div></div>
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
          <div class="inner">
            <h3>150</h3>
            <p>Data Penduduk</p>
          </div>
          <div class="icon">
            <i class="ion ion-bag"></i>
          </div>
          <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
      </div>
      <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
          <div class="inner">
            <h3>53</h3>
            <p>Data Kartu Keluarga</p>
          </div>
          <div class="icon">
            <i class="ion ion-stats-bars"></i>
          </div>
          <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
      </div>
      </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Selamat Datang, <?= htmlspecialchars($_SESSION["username"]); ?>!</h3>
                </div>
                <div class="card-body">
                    <p>Template AdminLTE berhasil diterapkan. Dari sini lo bisa mulai membangun fitur-fitur keren lainnya. Semangat, bro!</p>
                </div>
            </div>
        </div>
    </div>
  </div></section>
<?php 
// Panggil footer.php
require_once 'includes/footer.php'; 
?>