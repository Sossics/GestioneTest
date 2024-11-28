<?php
    
    $pagina = substr($_SERVER["SCRIPT_FILENAME"], strrpos($_SERVER["SCRIPT_FILENAME"], '/') + 1);

?>
<style>
            .nav-link {
                transition: background-color 0.3s, color 0.3s;
                padding: 12px 20px;
                border-radius: 8px;
                min-width: 8vh;
                text-align: center;
            }

            .active{
                color: #fff;
            }
    
            .nav-link:hover {
                background-color: rgba(255, 255, 255, 0.2);
                color: #000 !important;
            }
    
            .logout-btn {
                background-color: #dc3545 !important;
                color: #fff !important;
                transition: background-color 0.3s ease-in-out, transform 0.2s;
                padding: 12px 20px;
                border-radius: 8px;
            }
    
            .logout-btn:hover {
                background-color: #c82333 !important;
                transform: scale(1.05);
            }
    
            .welcome-section {
                margin-top: 80px;
                padding: 40px;
                border-radius: 12px;
                background-color: #fff;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }
    
            @media (min-width: 992px) {
                .nav-link {
                    margin-right: 10px;
                }
            }
    
        </style>
            <nav class="navbar navbar-expand-lg navbar-light shadow" style="background-color: #fff;">
                <div class="container d-flex justify-content-around align-items-center">
                    <a class="navbar-brand" href="#">
                        <img src="src/images/icons8-test-100.png" width="50" height="50" class="d-inline-block" alt="">
                        Piattaforma Test
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-auto">
                            <?php if ($_SESSION['user']['ruolo'] === 'STUDENTE'): ?>
                                <li class="nav-item">
                                    <a class="nav-link <?php if ($pagina == "index.php") echo 'active'; ?>" href="index.php">Home</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php if ($pagina == "test.php") echo 'active'; ?>" href="test.php">Test</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php if ($pagina == "profilo.php") echo 'active'; ?>" href="profilo.php">Profilo</a>
                                </li>
                            <?php elseif ($_SESSION['user']['ruolo'] === 'DOCENTE'): ?>
                                <li class="nav-item">
                                    <a class="nav-link <?php if ($pagina == "index.php") echo 'active'; ?>" href="index.php">Home</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php if ($pagina == "classi.php") echo 'active'; ?>" href="classi.php">Classi</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php if ($pagina == "studenti.php") echo 'active'; ?>" href="studenti.php">Studenti</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php if ($pagina == "sessioni.php") echo 'active'; ?>" href="sessioni.php">Sessioni</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php if ($pagina == "test.php") echo 'active'; ?>" href="test.php">Test</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php if ($pagina == "profilo.php") echo 'active'; ?>" href="profilo.php">Profilo</a>
                                </li>
                            <?php endif; ?>
                            <li class="nav-item">
                                <a class="nav-link logout-btn" href="logout.php">Logout</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>