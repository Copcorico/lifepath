<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0">-->
    <title>Profil - LifePath</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="icon" type="image/png" href="images/logos/personnagesansfondombre.png">
</head>

<body>
    <!-- Overlay -->
    <div class="overlay" id="overlay"></div>

    <!-- Navbar -->
    <nav class="navbar" id="navbar">
        <div class="hamburger" onclick="toggleNav()">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <div class="nav-profile-section">
            <img  class="nav-profile-icon" src="images/profils/profile.png" alt="logo lifePath">
            <div class="nav-profile-info">
                <h2><a href="profil.php">NOM COMPLET</a></h2>
                <p class="navbar-pilote">NOM pilote</p>
            </div>
        </div>

        <div class="divider"></div>

        <ul class="nav-menu">
            <li class="nav-menu-item">
                <div class="row" onclick="toggleRow(this)">
                    <div class="nav-container-menu">
                        <a href="index.html">Accueil </a>
                        <div class="arrow">></div>
                    </div>
                    <ul class="nav-submenu">
                        <li><a href="#offres">Offres</a></li>
                        <li><a href="#apropos">Entreprises</a></li>
                        <li><a href="#compte">Comptes</a></li>
                    </ul>
                </div>
            </li>
            <li class="nav-menu-item"><a href="inscription.html">Inscription</a></li>
            <li class="nav-menu-item"><a href="connexion.html">Connexion</a></li>
            <li class="nav-menu-item"><a href="offres.html">Nos offres</a></li>
            <li class="nav-menu-item"><a href="entreprise.html">Entreprise</a></li>
            <li class="nav-menu-item"><a href="avis.html">Avis</a></li>
            <li class="nav-menu-item"><a href="legale.html">Mention légal</a></li>
        </ul>
    </nav>

    <div class="main-content">
        <!-- Header -->
        <header class="header">
            <div class="header-hamburger" onclick="toggleNav()">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <h1>LifePath</h1>
            <img  src="images/logos/personnagesansfondombre.png" alt="logo lifePath" class="header-icon" >
        </header>
    </div>

    <main>
        <center><h1>Profil</h1></center>
        
        <form class ="profile-form">
            <div class="profile-section">
                <div>
                    <img class="profil-icon" src="images/profils/profile.png" alt="Photo de profil">
                </div>
                
                <div class="profile-name">
                    <div class="profil-info">
                        <label for="name">Nom:</label>
                        <input class="profile-input" type="text" name="name" value="NOM COMPLET">
                    </div>
                    
                    <div class="profil-info">
                        <label for="pilot">Prénom :</label>
                        <input class="profile-input" type="text" id="pilot" name="pilot" value="NOM PILOTE">
                    </div>
                </div>
            </div>
        
    </main>

    <script src="script.js"></script>
</body>