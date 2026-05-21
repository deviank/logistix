<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Game Idea Generator</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1><?php echo APP_NAME; ?></h1>
            <nav class="nav">
                <a href="?page=dashboard" class="nav-link">Dashboard</a>
                <a href="?page=companies" class="nav-link">Companies</a>
                <a href="?page=loadsheets" class="nav-link">Load Sheets</a>
                <a href="?page=invoices" class="nav-link">Invoices</a>
                <a href="?page=statements" class="nav-link">Statements</a>
                <a href="?page=game_ideas" class="nav-link active">Game Ideas</a>
            </nav>
        </header>

        <main class="main">
            <div class="page-header game-generator-header">
                <div>
                    <h2>Addictive Game Idea Generator</h2>
                    <p>Built around the mechanics that make top games hard to put down: clear loops, mastery, social tension, and variable rewards.</p>
                </div>
                <button id="generate-game-idea-btn" class="btn btn-primary">Generate New Idea</button>
            </div>

            <div class="content-section">
                <div class="generator-layout">
                    <section class="generator-card">
                        <h3>Why great games feel addictive</h3>
                        <ul class="hook-list">
                            <li><strong>Fast first win:</strong> Give the player a success in the first minute.</li>
                            <li><strong>Visible progression:</strong> Always show the next reachable upgrade.</li>
                            <li><strong>Uncertain rewards:</strong> Keep outcomes surprising but fair.</li>
                            <li><strong>Social stakes:</strong> Rivalry, cooperation, and shared status raise commitment.</li>
                            <li><strong>Skill expression:</strong> Better play should clearly outperform average play.</li>
                        </ul>
                    </section>

                    <section class="generator-card result-card" id="game-idea-output">
                        <h3 id="idea-title"><?php echo htmlspecialchars($idea['title']); ?></h3>
                        <p class="idea-one-liner" id="idea-one-liner"><?php echo htmlspecialchars($idea['one_liner']); ?></p>

                        <div class="idea-meta">
                            <span><strong>Genre:</strong> <span id="idea-genre"><?php echo htmlspecialchars($idea['genre']); ?></span></span>
                            <span><strong>Theme:</strong> <span id="idea-theme"><?php echo htmlspecialchars($idea['theme']); ?></span></span>
                            <span><strong>Meta Goal:</strong> <span id="idea-meta-system"><?php echo htmlspecialchars($idea['meta_system']); ?></span></span>
                        </div>

                        <div class="idea-block">
                            <h4>Core Loop</h4>
                            <ol id="idea-core-loop">
                                <?php foreach ($idea['core_loop'] as $step): ?>
                                    <li><?php echo htmlspecialchars($step); ?></li>
                                <?php endforeach; ?>
                            </ol>
                        </div>

                        <div class="idea-block">
                            <h4>Compulsion Elements</h4>
                            <div id="idea-compulsion-elements">
                                <?php foreach ($idea['compulsion_elements'] as $label => $description): ?>
                                    <p><strong><?php echo htmlspecialchars($label); ?>:</strong> <?php echo htmlspecialchars($description); ?></p>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="idea-block">
                            <h4>Signature System</h4>
                            <p id="idea-signature-system"><?php echo htmlspecialchars($idea['signature_system']); ?></p>
                        </div>

                        <div class="idea-block">
                            <h4>Retention Safeguards</h4>
                            <ul id="idea-retention-safeguards">
                                <?php foreach ($idea['retention_safeguards'] as $safeguard): ?>
                                    <li><?php echo htmlspecialchars($safeguard); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
