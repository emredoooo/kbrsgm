<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="/kbgm-v2/kbgm/dashboard.php">KBGM</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>" href="/kbgm-v2/kbgm/dashboard.php">Dashboard</a>
                </li>
                
                <li class="nav-item dropdown me-2"> 
                    <a class="nav-link dropdown-toggle <?= (basename($_SERVER['PHP_SELF']) === 'add_member.php' || basename($_SERVER['PHP_SELF']) === 'list_member.php' || basename($_SERVER['PHP_SELF']) === 'edit_member.php') ? 'active' : '' ?>" href="#" id="navbarDropdownMember" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Member
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownMember">
                        <li><a class="dropdown-item <?= basename($_SERVER['PHP_SELF']) === 'add_member.php' ? 'active' : '' ?>" href="/kbgm-v2/kbgm/member/add_member.php">Tambah Member</a></li>
                        <li><a class="dropdown-item <?= basename($_SERVER['PHP_SELF']) === 'list_member.php' ? 'active' : '' ?>" href="/kbgm-v2/kbgm/member/list_member.php">List Member</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= (basename($_SERVER['PHP_SELF']) === 'manage_kk.php' || basename($_SERVER['PHP_SELF']) === 'list_kk.php') ? 'active' : '' ?>" href="#" id="navbarDropdownKartuKeluarga" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Kartu Keluarga
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownKartuKeluarga">
                        <li><a class="dropdown-item <?= basename($_SERVER['PHP_SELF']) === 'manage_kk.php' && !isset($_GET['no_kk']) ? 'active' : '' ?>" href="/kbgm-v2/kbgm/kartu_keluarga/manage_kk.php">Tambah KK Baru</a></li>
                        <li><a class="dropdown-item <?= basename($_SERVER['PHP_SELF']) === 'list_kk.php' ? 'active' : '' ?>" href="/kbgm-v2/kbgm/kartu_keluarga/list_kk.php">List Kartu Keluarga</a></li>
                    </ul>
                </li>
            </ul>

            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Halo, <?php echo $_SESSION['username']; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownMenuLink">
                        <li><a class="dropdown-item" href="/kbgm-v2/kbgm/buku_saku.php" target="_blank" rel="noopener noreferrer">Bantuan (?)</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger fw-bold" href="/kbgm-v2/kbgm/logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>