<!DOCTYPE html>
<html lang="fr">

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
        <img  class="nav-profile-icon" src="images/profile.png" alt="logo lifePath">
        <div class="nav-profile-info">
            <h2>NOM COMPLET</h2>
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
            <a href="inscription.html">
                Inscription
            </a>
            <a href="connexion.html">
                Connexion
            </a>
            <a href="offres.html">
                Nos offres
            </a>
            <a href="entreprise.html">
                Entreprise
            </a>
            <a href="avis.html">
                Avis
            </a>
            <a href="legale.html">
                Mention légal
            </a>
        </li>
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
        <h1 class="title">LifePath</h1>
        <img  src="images/personnagesansfondombre.png" alt="logo lifePath" class="header-icon" >
    </header>
</div>

<script src="js/navbar.js"></script>
</html>