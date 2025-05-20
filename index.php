<?php
$entriesDir = 'entries/';
$allFiles = @scandir($entriesDir); // @ pour supprimer l'avertissement si le dossier n'existe pas
$jsonWords = [];

if ($allFiles) {
    foreach ($allFiles as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) == 'json') {
            // Extraire le nom du mot sans l'extension .json
            $jsonWords[] = basename($file, '.json');
        }
    }
}
shuffle($jsonWords);
// Sélectionner jusqu'à 5 mots aléatoires pour l'affichage
$random_words_to_display = array_slice($jsonWords, 0, 5);

// Rendre tous les mots disponibles pour JavaScript
$allJsonWordsForJs = json_encode(array_values($jsonWords)); // array_values pour s'assurer que c'est un tableau JS
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche - Genealogos</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&family=Georgia&display=swap');
        /* Ajout pour l'icône de recherche */
        @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');

        *, *::before, *::after {
            box-sizing: border-box;
        }

        :root {
            --bg-color: #0D0D0D;
            --card-bg-color: #1A1A1A;
            --text-color: #FFFFFF;
            --text-muted-color: #AAAAAA;
            --text-soft-color: #E0E0E0;
            --accent-color: #79A6DC;
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
            /* padding: 0; */ /* Retiré car box-sizing gère cela, et padding-top/bottom sont spécifiques */
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding-top: 50px;
            padding-bottom: 50px;
            position: relative;
            overflow-x: hidden;
        }

        .arc {
            position: absolute;
            border-radius: 50%;
            border-style: dashed;
            border-width: 1px;
            border-color: transparent;
            z-index: 0;
        }

        .arc1 { width: 700px; height: 700px; top: -300px; left: -350px; border-top-color: var(--arc-color); border-right-color: var(--arc-color); transform: rotate(25deg); }
        .arc2 { width: 550px; height: 550px; top: -250px; left: -300px; border-top-color: var(--arc-color); border-right-color: var(--arc-color); transform: rotate(35deg); }
        .arc3 { width: 600px; height: 600px; top: -250px; right: -320px; border-top-color: var(--arc-color); border-left-color: var(--arc-color); transform: rotate(-20deg); }
        .arc4 { width: 500px; height: 500px; bottom: -250px; left: -200px; border-bottom-color: var(--arc-color); border-right-color: var(--arc-color); transform: rotate(-40deg); }
        .arc5 { width: 750px; height: 750px; bottom: -350px; right: -380px; border-bottom-color: var(--arc-color); border-left-color: var(--arc-color); transform: rotate(30deg); }
        .arc6 { width: 600px; height: 600px; bottom: -300px; right: -330px; border-bottom-color: var(--arc-color); border-left-color: var(--arc-color); transform: rotate(40deg); }

        .container {
            background-color: var(--card-bg-color);
            border-radius: 12px;
            padding: 35px 45px;
            max-width: 900px;
            width: 90%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 1;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            font-size: 14px;
            color: var(--text-soft-color);
        }

        .logo { font-weight: 500; }

        nav a {
            color: var(--text-soft-color);
            text-decoration: none;
            margin-left: 25px;
            font-size: 14px;
        }
        nav a:hover { text-decoration: underline; }

        .section-divider {
            border: none;
            border-top: 1px solid var(--divider-color);
            margin-top: 25px; 
            margin-bottom: 25px; 
        }

        /* Styles pour la barre de recherche */
        .search-bar-container {
            display: flex;
            margin-bottom: 20px;
            gap: 10px;
            position: relative; 
            align-items: center; 
        }

        .search-input-wrapper {
            flex-grow: 1;
            position: relative; /* Nécessaire pour ancrer #suggestionsContainer */
            display: flex;
            align-items: center;
        }

        .search-input {
            flex-grow: 1;
            padding: 12px 18px 12px 45px; /* Augmenter le padding gauche pour l'icône */
            font-size: 16px;
            border-radius: 25px; /* Plus arrondi */
            border: 1px solid var(--divider-color);
            background-color: var(--pronunciation-bg);
            color: var(--text-soft-color);
            outline: none;
            transition: border-color 0.3s ease, box-shadow 0.3s ease; /* Transition douce */
        }
        .search-input::placeholder {
            color: var(--text-muted-color);
        }
        .search-input:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(121, 166, 220, 0.3); /* Ombre douce au focus */
        }

        .search-input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted-color);
            font-size: 16px;
        }

        .search-button {
            padding: 12px 22px; /* Léger ajustement du padding */
            font-size: 16px;
            border-radius: 25px; /* Plus arrondi, comme l'input */
            border: none;
            background-color: var(--accent-color);
            color: var(--bg-color); /* Changé pour un meilleur contraste avec l'accent */
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s ease, opacity 0.2s ease-in-out; /* Transition pour le fond */
        }
        .search-button:hover {
            background-color: #608ac7; /* Couleur d'accent légèrement plus foncée au survol */
            opacity: 1; /* Enlever l'opacité pour un effet de couleur directe */
        }
        
        /* Styles pour le conteneur de suggestions */
        #suggestionsContainer {
            position: absolute;
            top: calc(100% + 6px); /* Ajusté pour être sous l'input, à l'intérieur du wrapper */
            left: 0;
            right: 0; /* Prendra la largeur de .search-input-wrapper */
            /* margin-right: calc(var(--button-width, 120px) + 10px); */ /* Supprimé, géré par le parent */
            background-color: var(--card-bg-color);
            border: 1px solid var(--divider-color);
            border-top: none; /* Pour un look plus intégré avec l'input */
            border-radius: 0 0 8px 8px; /* Seulement les coins inférieurs arrondis */
            max-height: 250px; /* Un peu plus de hauteur */
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 0 8px 16px rgba(0,0,0,0.3); /* Ombre plus prononcée */
            opacity: 0; /* Caché par défaut pour la transition */
            visibility: hidden; /* Caché par défaut */
            transform: translateY(-10px); /* Position initiale pour l'animation */
            transition: opacity 0.2s ease-out, transform 0.2s ease-out, visibility 0.2s;
        }

        #suggestionsContainer.visible {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        /* Personnalisation de la barre de défilement pour les suggestions */
        #suggestionsContainer::-webkit-scrollbar {
            width: 8px;
        }
        #suggestionsContainer::-webkit-scrollbar-track {
            background: var(--pronunciation-bg);
            border-radius: 8px;
        }
        #suggestionsContainer::-webkit-scrollbar-thumb {
            background-color: var(--accent-color);
            border-radius: 8px;
            border: 2px solid var(--pronunciation-bg);
        }


        .suggestion-item {
            padding: 12px 20px; /* Plus d'espacement */
            color: var(--text-soft-color);
            cursor: pointer;
            font-size: 15px;
            border-bottom: 1px solid var(--divider-color); /* Séparateur subtil */
            transition: background-color 0.2s ease;
        }
        .suggestion-item:last-child {
            border-bottom: none; /* Pas de bordure pour le dernier élément */
        }

        .suggestion-item:hover, .suggestion-item.active {
            background-color: var(--accent-color);
            color: var(--bg-color); /* Contraste élevé */
        }
        .suggestion-item.active {
             font-weight: 500;
        }

        /* Styles pour la section des mots aléatoires */
        .random-words-section {
            margin-top: 25px;
        }

        .random-words-section h3 {
            font-size: 18px;
            font-weight: 500;
            color: var(--text-soft-color);
            margin-top: 0;
            margin-bottom: 20px;
        }

        .random-words-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .pronunciation-button-link {
            text-decoration: none;
        }
        
        /* Style pour les boutons de suggestion (classe .pronunciation) */
        .pronunciation {
            background-color: var(--pronunciation-bg);
            color: var(--text-soft-color);
            border: 1px solid #444;
            border-radius: 20px;
            padding: 8px 18px;
            font-size: 14px;
            display: inline-flex; /* Pour que le bouton s'adapte au contenu */
            align-items: center;
            cursor: pointer;
            transition: background-color 0.2s ease-in-out, border-color 0.2s ease-in-out;
        }
        .pronunciation:hover {
            background-color: #3a3a3a; /* Légèrement plus clair au survol */
            border-color: #555;
        }


        /* Responsive adjustments */
        @media (max-width: 768px) {
            body {
                padding-top: 30px; /* Réduit */
                padding-bottom: 30px; /* Réduit */
            }
            .container { 
                padding: 25px 15px; /* Padding horizontal réduit */
                width: 95%; 
            }
            header { 
                flex-direction: column; 
                align-items: flex-start; 
                gap: 10px; 
                margin-bottom: 20px; /* Réduit */
            }
            nav { display: flex; flex-wrap: wrap; gap: 15px; }
            nav a { margin-left: 0; }
            .arc { display: none; }
            .arc1, .arc5 { display: block; width: 300px; height: 300px; }
            .arc1 { top: -100px; left: -150px; }
            .arc5 { bottom: -100px; right: -150px; }
            
            .search-bar-container {
                flex-direction: column; 
                margin-bottom: 15px; /* Réduit */
            }
            .search-input-wrapper {
                width: 100%; 
            }
            .search-button {
                width: 100%; 
            }
            #suggestionsContainer {
                border-top: 1px solid var(--divider-color); 
                border-radius: 0 0 8px 8px; /* Assurer que le radius est correct */
            }
            .section-divider {
                margin-top: 20px; /* Réduit */
                margin-bottom: 20px; /* Réduit */
            }
        }
         @media (max-width: 480px) {
            body {
                padding-top: 20px; /* Encore plus réduit pour très petits écrans */
                padding-bottom: 20px;
            }
            .container {
                padding: 20px 10px; /* Encore plus réduit */
            }
            header nav { font-size: 13px; gap: 10px; }
            .search-input, .search-button { font-size: 15px; }
            .pronunciation { padding: 6px 14px; font-size: 13px; }
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
                <a target = "_blank" href="https://github.com/XenocodeRCE/Genealogos">Sources</a>
            </nav>
        </header>

        <form action="index.php" method="GET" class="search-bar-container" id="searchForm">
            <div class="search-input-wrapper">
                <i class="fas fa-search search-input-icon"></i>
                <input type="text" name="mot" id="searchInput" placeholder="Rechercher un mot..." class="search-input" required autocomplete="off">
                <div id="suggestionsContainer"></div> <!-- Déplacé ici -->
            </div>
            <button type="submit" class="search-button">Rechercher</button>
            <!-- <div id="suggestionsContainer"></div> ANCIEN EMPLACEMENT -->
        </form>

        <hr class="section-divider">

        <?php if (!empty($random_words_to_display)): ?>
        <div class="random-words-section">
            <h3>Quelques suggestions :</h3>
            <div class="random-words-buttons">
                <?php foreach ($random_words_to_display as $word): ?>
                    <a href="entry.php?mot=<?php echo urlencode($word); ?>" class="pronunciation-button-link">
                        <button class="pronunciation" type="button"><?php echo htmlspecialchars($word); ?></button>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="random-words-section">
            <p>Aucun mot à suggérer pour le moment. Le dictionnaire est peut-être vide.</p>
        </div>
        <?php endif; ?>
    </div>

    <script>
        const searchInput = document.getElementById('searchInput');
        const suggestionsContainer = document.getElementById('suggestionsContainer');
        const searchForm = document.getElementById('searchForm');
        // const searchButton = document.querySelector('.search-button'); // Plus nécessaire pour adjustSuggestionsContainerWidth
        const allWords = <?php echo $allJsonWordsForJs; ?>;
        let activeSuggestionIndex = -1;

        // La fonction adjustSuggestionsContainerWidth n'est plus nécessaire
        // window.addEventListener('resize', adjustSuggestionsContainerWidth);
        // adjustSuggestionsContainerWidth(); 


        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            suggestionsContainer.innerHTML = '';
            activeSuggestionIndex = -1;

            if (query.length === 0) {
                suggestionsContainer.classList.remove('visible');
                return;
            }

            const filteredWords = allWords.filter(word => word.toLowerCase().includes(query)).slice(0, 7); // Recherche "contient" et limite à 7

            if (filteredWords.length > 0) {
                filteredWords.forEach((word, index) => {
                    const suggestionItem = document.createElement('div');
                    suggestionItem.classList.add('suggestion-item');
                    
                    // Mettre en évidence la partie correspondante
                    const matchStartIndex = word.toLowerCase().indexOf(query);
                    const matchEndIndex = matchStartIndex + query.length;
                    suggestionItem.innerHTML = word.substring(0, matchStartIndex) +
                                               '<strong>' + word.substring(matchStartIndex, matchEndIndex) + '</strong>' +
                                               word.substring(matchEndIndex);

                    suggestionItem.addEventListener('click', function() {
                        searchInput.value = word; // Utiliser le mot complet, pas le HTML
                        suggestionsContainer.classList.remove('visible');
                        searchForm.action = 'entry.php'; 
                        searchForm.submit();
                    });
                    suggestionsContainer.appendChild(suggestionItem);
                });
                suggestionsContainer.classList.add('visible');
            } else {
                suggestionsContainer.classList.remove('visible');
            }
        });

        searchInput.addEventListener('keydown', function(e) {
            const items = suggestionsContainer.querySelectorAll('.suggestion-item');
            if (items.length === 0 || !suggestionsContainer.classList.contains('visible')) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeSuggestionIndex = (activeSuggestionIndex + 1) % items.length;
                updateActiveSuggestion(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeSuggestionIndex = (activeSuggestionIndex - 1 + items.length) % items.length;
                updateActiveSuggestion(items);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeSuggestionIndex > -1 && items[activeSuggestionIndex]) {
                    // Extraire le texte brut car l'item peut contenir du HTML (<strong>)
                    searchInput.value = items[activeSuggestionIndex].textContent; 
                    items[activeSuggestionIndex].click();
                } else {
                    if (searchInput.value.trim() !== '') {
                         searchForm.action = 'entry.php'; 
                         searchForm.submit();
                    }
                }
            } else if (e.key === 'Escape') {
                suggestionsContainer.classList.remove('visible');
                activeSuggestionIndex = -1;
            }
        });

        function updateActiveSuggestion(items) {
            items.forEach(item => item.classList.remove('active'));
            if (activeSuggestionIndex > -1 && items[activeSuggestionIndex]) {
                items[activeSuggestionIndex].classList.add('active');
                // Optionnel: faire défiler pour voir l'élément actif
                items[activeSuggestionIndex].scrollIntoView({ block: 'nearest' });
            }
        }

        // Cacher les suggestions si on clique en dehors
        document.addEventListener('click', function(e) {
            if (!searchForm.contains(e.target)) {
                suggestionsContainer.classList.remove('visible');
                activeSuggestionIndex = -1;
            }
        });

        // Ajustement pour que le clic sur le bouton de recherche standard fonctionne
        // et redirige vers entry.php si un mot est tapé.
        // Le formulaire soumettra à index.php par défaut, mais si on veut que la recherche
        // via le bouton aille toujours à entry.php, il faut le spécifier.
        searchForm.addEventListener('submit', function(e) {
            if (searchInput.value.trim() !== '') {
                // Si l'action n'est pas déjà entry.php (par ex. via suggestion cliquée)
                if (searchForm.action.endsWith('index.php')) { 
                    searchForm.action = 'entry.php';
                }
            }
            // Si le champ est vide, la soumission est bloquée par `required` sur l'input.
            // Si on voulait permettre une soumission vide vers index.php, il faudrait enlever `required`.
        });

    </script>
</body>
</html>
