<?php
$mot = $_GET['mot'];
if(!isset($mot) || empty($mot)) {
    // redirect to index.php if no word is provided
    header("Location: index.php");
    exit;
}
// check si le dossier 'entries' contient le fichier %mot%.md :
$entriesDir = 'entries/';
$entriesFiles = scandir($entriesDir);
$jsonPath = $entriesDir . $mot . '.json';
if (file_exists($jsonPath)) {
    $objetData = json_decode(file_get_contents($jsonPath), true);
}else{
    header("HTTP/1.0 404 Not Found");
    echo "Le mot '$mot' n'existe pas.";
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🪢 Genealogos - Dictionnaire généalogique de la philosophie pour les lycéens</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&family=Georgia&display=swap');

        :root {
            --bg-color: #0D0D0D;
            --card-bg-color: #1A1A1A; /* Slightly darker than original image's #1E1E1E to enhance contrast with arcs */
            --text-color: #FFFFFF;
            --text-muted-color: #AAAAAA;
            --text-soft-color: #E0E0E0;
            --accent-color: #79A6DC; /* For synonyms, as hinted by OCR */
            --divider-color: #3A3A3A;
            --pronunciation-bg: #2D2D2D;
            --arc-color: rgba(255, 255, 255, 0.1);

            --font-sans-serif: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Ubuntu, "Helvetica Neue", sans-serif;
            --font-serif: 'Georgia', serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: var(--font-sans-serif);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start; /* Align to top for long content */
            min-height: 100vh;
            padding-top: 50px; /* Space from top */
            padding-bottom: 50px; /* Space at bottom */
            position: relative;
            overflow-x: hidden; /* Prevent horizontal scroll from arcs */
        }

        .arc {
            position: absolute;
            border-radius: 50%;
            border-style: dashed;
            border-width: 1px;
            border-color: transparent; /* Default for sides not shown */
            z-index: 0; /* Behind main card */
        }

        /* Approximating arcs from the image */
        .arc1 { /* Top-left largest */
            width: 700px; height: 700px;
            top: -300px; left: -350px;
            border-top-color: var(--arc-color);
            border-right-color: var(--arc-color);
            transform: rotate(25deg);
        }
        .arc2 { /* Top-left inner */
            width: 550px; height: 550px;
            top: -250px; left: -300px;
            border-top-color: var(--arc-color);
            border-right-color: var(--arc-color);
            transform: rotate(35deg);
        }
        .arc3 { /* Top-right */
            width: 600px; height: 600px;
            top: -250px; right: -320px;
            border-top-color: var(--arc-color);
            border-left-color: var(--arc-color);
            transform: rotate(-20deg);
        }
         .arc4 { /* Bottom-left */
            width: 500px; height: 500px;
            bottom: -250px; left: -200px;
            border-bottom-color: var(--arc-color);
            border-right-color: var(--arc-color);
            transform: rotate(-40deg);
        }
        .arc5 { /* Bottom-right largest */
            width: 750px; height: 750px;
            bottom: -350px; right: -380px;
            border-bottom-color: var(--arc-color);
            border-left-color: var(--arc-color);
            transform: rotate(30deg);
        }
        .arc6 { /* Bottom-right inner */
            width: 600px; height: 600px;
            bottom: -300px; right: -330px;
            border-bottom-color: var(--arc-color);
            border-left-color: var(--arc-color);
            transform: rotate(40deg);
        }


        .container {
            background-color: var(--card-bg-color);
            border-radius: 12px;
            padding: 35px 45px;
            max-width: 900px;
            width: 90%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 1; /* Above arcs */
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            font-size: 14px;
            color: var(--text-soft-color);
        }

        .logo {
            font-weight: 500;
        }

        nav a {
            color: var(--text-soft-color);
            text-decoration: none;
            margin-left: 25px;
            font-size: 14px;
        }

        nav a:hover {
            text-decoration: underline;
        }

        .word-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .word-section h1 {
            font-family: var(--font-serif);
            font-size: 64px; /* Slightly reduced for better fit */
            font-weight: normal; /* Georgia is bold by default */
            margin: 0;
            color: var(--text-color);
        }

        .pronunciation {
            background-color: var(--pronunciation-bg);
            color: var(--text-soft-color);
            border: 1px solid #444;
            border-radius: 20px;
            padding: 8px 18px;
            font-size: 14px;
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .pronunciation svg {
            margin-right: 10px;
            fill: var(--text-soft-color);
        }
        
        .section-divider {
            border: none;
            border-top: 1px solid var(--divider-color);
            margin-top: 0;
            margin-bottom: 25px;
        }

        .content-grid {
            display: flex;
            gap: 30px; /* Gap between columns */
        }

        .definitions-column {
            flex: 3; /* Takes more space */
            padding-right: 30px;
            border-right: 1px solid var(--divider-color);
        }

        .sidebar-column {
            flex: 2; /* Takes less space */
        }

        .part-of-speech {
            font-size: 14px;
            color: var(--text-muted-color);
            margin-bottom: 15px;
            font-weight: 500;
        }

        .definition-item {
            margin-bottom: 25px;
        }

        .definition-item > p:first-child {
            font-size: 16px;
            line-height: 1.6;
            margin-top: 0;
            margin-bottom: 8px;
        }
        
        .definition-item .bullet-point::before {
            content: "•";
            margin-right: 8px;
            color: var(--text-color);
        }


        .example {
            font-style: italic;
            color: var(--text-muted-color);
            font-size: 14px;
            margin-top: 5px;
            margin-bottom: 15px;
        }

        .synonyms-block {
            font-size: 14px;
        }

        .synonyms-label {
            color: var(--text-muted-color);
            margin-right: 8px;
            font-weight: 500;
        }

        .synonyms-block span {
            color: var(--accent-color);
            cursor: pointer;
        }
        .synonyms-block span:hover {
            text-decoration: underline;
        }


        .sidebar-column h3 {
            font-size: 18px;
            font-weight: 500;
            color: var(--text-soft-color);
            margin-top: 0;
            margin-bottom: 15px;
        }
        
        .phrases-section {
            margin-bottom: 25px;
        }

        .phrase-item h4 {
            font-size: 16px;
            font-weight: 500;
            margin-top: 0;
            margin-bottom: 8px;
            color: var(--text-color);
        }
        
        .phrase-item .informal {
            font-size: 0.85em;
            color: var(--text-muted-color);
            font-weight: normal;
            margin-left: 5px;
        }

        .phrase-item p {
            font-size: 14px;
            line-height: 1.6;
            color: var(--text-muted-color);
            margin-top: 0;
            margin-bottom: 0;
        }
        
        .sidebar-divider {
            border: none;
            border-top: 1px solid var(--divider-color);
            margin-top: 25px;
            margin-bottom: 25px;
        }

        .origin-section p {
            font-size: 14px;
            line-height: 1.6;
            color: var(--text-muted-color);
            margin-top: 0;
            margin-bottom: 0;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                padding: 25px 20px;
                width: 95%;
            }

            .word-section {
                flex-direction: column;
                align-items: flex-start;
                margin-bottom: 20px;
            }

            .word-section h1 {
                font-size: 48px;
                margin-bottom: 10px;
            }

            .pronunciation {
                margin-bottom: 20px;
            }
            
            header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            nav {
                display: flex;
                flex-wrap: wrap;
                gap: 15px;
            }
            nav a {
                margin-left: 0;
            }

            .content-grid {
                flex-direction: column;
                gap: 0; /* Remove gap, use margin/padding on items */
            }

            .definitions-column {
                padding-right: 0;
                border-right: none;
                margin-bottom: 30px; /* Space before sidebar content stacks */
            }
            
            .arc { /* Hide some arcs or make them smaller on mobile to reduce clutter */
                display: none; /* Simplest solution for mobile */
            }
            .arc1, .arc5 { /* Show only a couple, if desired */
                 display: block;
                 width: 300px; height: 300px;
            }
            .arc1 { top: -100px; left: -150px; }
            .arc5 { bottom: -100px; right: -150px; }
        }
         @media (max-width: 480px) {
             .word-section h1 {
                font-size: 40px;
            }
            header nav {
                font-size: 13px;
                gap: 10px;
            }
            .pronunciation {
                padding: 6px 14px;
                font-size: 13px;
            }
         }

    </style>
</head>
<body>
    <!-- Background decorative arcs -->
    <div class="arc arc1"></div>
    <div class="arc arc2"></div>
    <div class="arc arc3"></div>
    <div class="arc arc4"></div>
    <div class="arc arc5"></div>
    <div class="arc arc6"></div>

    <div class="container">
        <header>
            <div class="logo">Genealogos</div>
            <nav>
                <a href="index.php">Retour à l'index</a>
                <a href="#" id="share-link">Partager</a>
                <span id="share-message" style="display:none; color:#79A6DC; margin-left:10px;">Lien copié !</span>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var shareLink = document.getElementById('share-link');
                        var shareMessage = document.getElementById('share-message');
                        shareLink.addEventListener('click', function(e) {
                            e.preventDefault();
                            navigator.clipboard.writeText(window.location.href).then(function() {
                                shareMessage.style.display = 'inline';
                                setTimeout(function() {
                                    shareMessage.style.display = 'none';
                                }, 2000);
                            });
                        });
                    });
                </script>
            </nav>
        </header>

        <div class="word-section">
            <h1><?php echo htmlspecialchars($mot); ?></h1>
            <button class="pronunciation" onclick="window.open('https://github.com/XenocodeRCE/Genealogos', '_blank')">
                Sources
            </button>
        </div>

        <hr class="section-divider">

        <div class="content-grid">
            <div class="definitions-column">
                <?php if ($objetData): ?>
                    <?php foreach ($objetData['periodes'] as $periode): ?>
                        <div class="definition-item">
                            <h2 style="font-size:1.3em;color:#79A6DC;margin-bottom:8px;"><?php echo htmlspecialchars($periode['periode']); ?></h2>
                            <p><?php echo htmlspecialchars($periode['description_generale']); ?></p>
                            <?php if (!empty($periode['penseurs_cles'])): ?>
                                <ul style="margin-top:10px;">
                                    <?php foreach ($periode['penseurs_cles'] as $penseur): ?>
                                        <li>
                                            <strong><?php echo htmlspecialchars($penseur['nom']); ?> :</strong>
                                            <?php echo htmlspecialchars($penseur['contribution']); ?>
                                            <?php if (!empty($penseur['extrait'])): ?>
                                                <div class="example" style="margin-top:4px;"><?php echo htmlspecialchars($penseur['extrait']); ?></div>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <?php if (!empty($periode['usages_et_debats'])): ?>
                                <div style="margin-top:8px;">
                                    <span class="synonyms-label">Usages et débats :</span>
                                    <?php echo htmlspecialchars($periode['usages_et_debats']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($periode['changements_de_signification'])): ?>
                                <div style="margin-top:8px;">
                                    <span class="synonyms-label">Changements de signification :</span>
                                    <?php echo htmlspecialchars($periode['changements_de_signification']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($periode['liens_avec_autres_notions'])): ?>
                                <div style="margin-top:8px;">
                                    <span class="synonyms-label">Liens avec d'autres notions :</span>
                                    <ul>
                                        <?php foreach ($periode['liens_avec_autres_notions'] as $lien): ?>
                                            <li>
                                                <strong><?php echo htmlspecialchars($lien['notion']); ?> :</strong>
                                                <?php echo htmlspecialchars($lien['explication_lien']); ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                        <hr class="section-divider">
                    <?php endforeach; ?>
                <?php else: ?>
                <?php endif; ?>
            </div>

            <div class="sidebar-column">
                <div class="phrases-section">
                    <h3>Citations</h3>
                    <div class="phrase-item">
                        <ul>
                            <?php foreach ($objetData['citations'] as $citation): ?>
                                <li>
                                    <span style="font-style:italic;">"<?php echo htmlspecialchars($citation['citation']); ?>"</span>
                                    <br>
                                    <span style="color:#AAAAAA;">
                                        — <?php echo htmlspecialchars($citation['auteur']); ?>, <em><?php echo htmlspecialchars($citation['oeuvre']); ?></em>
                                    </span>
                                    <?php if (!empty($citation['commentaire'])): ?>
                                        <div style="font-size:0.95em;color:#E0E0E0;"><?php echo htmlspecialchars($citation['commentaire']); ?></div>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <hr class="sidebar-divider">
                <div class="origin-section">
                    <h3>Étymologie</h3>
                        <p><?php echo htmlspecialchars($objetData['etymologie']); ?></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>