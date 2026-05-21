<?php
/**
 * Standalone Game Idea Generator
 * This page is intentionally separate from the logistics app routing.
 */

declare(strict_types=1);

function pickRandom(array $items): string
{
    return $items[array_rand($items)];
}

function pickMany(array $items, int $count): array
{
    shuffle($items);
    return array_slice($items, 0, max(1, min($count, count($items))));
}

function generateTitle(string $genre): string
{
    $adjectives = ['Neon', 'Shadow', 'Quantum', 'Turbo', 'Mythic', 'Echo', 'Hyper', 'Nova', 'Rogue', 'Pixel'];
    $nouns = ['Arena', 'Raid', 'Legends', 'Frontier', 'Rush', 'Realm', 'Odyssey', 'Forge', 'Chronicles', 'Circuit'];
    $genreSuffix = [
        'roguelite' => 'Roguelite',
        'idle' => 'Tycoon',
        'social' => 'Party',
        'strategy' => 'Tactics',
        'simulation' => 'Sim',
        'action' => 'Brawl',
        'puzzle' => 'Puzzler',
        'any' => 'Online'
    ];

    return pickRandom($adjectives) . ' ' . pickRandom($nouns) . ': ' . ($genreSuffix[$genre] ?? 'Online');
}

function generateGameIdea(string $genre, string $platform, string $complexity): array
{
    $genreConcepts = [
        'roguelite' => [
            'core' => ['Run short dungeon raids to bank loot before extraction', 'Fight through procedural rooms where each upgrade changes your build', 'Complete 8-minute survival runs with escalating enemy swarms'],
            'fantasy' => ['becoming an unstoppable build-crafter', 'mastering impossible odds through skill and adaptation', 'risking everything for one more high-value run']
        ],
        'idle' => [
            'core' => ['Automate production lines and prestige for permanent boosts', 'Collect heroes that farm resources while you are offline', 'Stack multipliers across short active bursts and long idle sessions'],
            'fantasy' => ['building an empire that grows 24/7', 'turning tiny gains into absurd numbers', 'optimizing systems like a growth hacker']
        ],
        'social' => [
            'core' => ['Compete in daily team events with rotating mini-games', 'Run asynchronous clan battles where everyone contributes', 'Play co-op heists that require role coordination'],
            'fantasy' => ['becoming the MVP of your friend group', 'pulling off clutch teamwork moments', 'earning status through social mastery']
        ],
        'strategy' => [
            'core' => ['Draft a compact squad and counter enemy synergies in fast rounds', 'Expand territory while balancing economy, defense, and timing', 'Outsmart rivals in short tactical matches with evolving objectives'],
            'fantasy' => ['winning through smart reads and adaptation', 'commanding a clever long-term plan', 'outplaying stronger opponents with strategy']
        ],
        'simulation' => [
            'core' => ['Build and optimize a living world with dynamic citizen needs', 'Design a business sim where each choice affects reputation and growth', 'Manage a creative studio while shipping projects under pressure'],
            'fantasy' => ['building something uniquely yours', 'solving complex systems with elegant setups', 'watching your world react to every decision']
        ],
        'action' => [
            'core' => ['Dash-combat through compact arenas chaining combos and finishers', 'Hunt giant bosses with build-specific weak-point tactics', 'Race through combat courses with leaderboard-driven speed tech'],
            'fantasy' => ['feeling mechanically unstoppable', 'executing stylish high-skill plays', 'mastering reflexes and momentum']
        ],
        'puzzle' => [
            'core' => ['Solve layered logic boards that transform after each move', 'Merge and chain pieces to trigger high-score cascades', 'Decode mystery rooms using clues from prior levels'],
            'fantasy' => ['feeling clever with every breakthrough', 'spotting patterns before anyone else', 'solving impossible-seeming problems elegantly']
        ]
    ];

    if (!isset($genreConcepts[$genre])) {
        $genre = array_rand($genreConcepts);
    }

    $hooks = [
        'A variable-reward chest system drops rare build-changing perks',
        'A mastery track unlocks visible cosmetic status symbols every few levels',
        'A streak bonus ramps rewards for consecutive daily sessions',
        'A near-miss mechanic creates tense last-second comeback moments',
        'A "one more run" objective appears after each session with a clear payoff',
        'A collection log motivates completion of rare units, skins, and relics',
        'A personalized challenge feed adapts to player skill for flow-state difficulty',
        'A short-term quest loop pays out premium progression resources'
    ];

    $metaSystems = [
        'Build library that permanently records discovered synergies',
        'Season pass-style chapter map with free and premium reward tracks',
        'Account-wide talent tree that changes future runs',
        'Guild tech research where members unlock communal perks',
        'Base/home customization that grants gameplay buffs',
        'Hero relationship system unlocking unique combo abilities'
    ];

    $socialSystems = [
        'Asynchronous rival ghosts on every level and leaderboard lane',
        'Weekly clan objectives with shared reward milestones',
        'Friend referral missions that unlock co-op-only encounters',
        'Short PvP snapshots where players challenge each others builds',
        'Community world events where all players push a global boss HP bar',
        'Creator-driven map or challenge rotation with voting'
    ];

    $platformHooks = [
        'mobile' => 'Sessions target 3-8 minutes with swipe-friendly controls and frequent reward beats.',
        'pc' => 'Sessions target 10-20 minutes with deeper build complexity and precision input mastery.',
        'console' => 'Sessions target 12-25 minutes with high-impact audiovisual spectacle and controller flow.',
        'cross-platform' => 'Sessions scale from 5-minute mobile check-ins to 20-minute premium runs on larger screens.'
    ];

    $complexityLevel = [
        'simple' => 1,
        'balanced' => 2,
        'deep' => 3
    ];

    $ideaDepth = $complexityLevel[$complexity] ?? 2;
    $selectedHooks = pickMany($hooks, $ideaDepth + 1);
    $selectedMeta = pickMany($metaSystems, $ideaDepth);
    $selectedSocial = pickMany($socialSystems, $ideaDepth);
    $concept = $genreConcepts[$genre];
    $coreLoop = pickRandom($concept['core']);
    $fantasy = pickRandom($concept['fantasy']);

    $whyItWorks = [
        'The core loop gives immediate feedback in under 30 seconds, creating quick engagement.',
        'Variable rewards plus visible progression provide both surprise and long-term goals.',
        'Social comparison (friends/clans/leaderboards) adds emotional stakes beyond raw gameplay.',
        'Escalating mastery unlocks keep experienced players pursuing optimization.',
        'Frequent small wins reduce drop-off between major milestones.'
    ];

    return [
        'title' => generateTitle($genre),
        'genre' => ucfirst($genre),
        'platform' => ucfirst($platform),
        'pitch' => "A {$genre} game about {$fantasy}. Players {$coreLoop}.",
        'core_loop' => $coreLoop,
        'hooks' => $selectedHooks,
        'meta' => $selectedMeta,
        'social' => $selectedSocial,
        'platform_note' => $platformHooks[$platform] ?? $platformHooks['cross-platform'],
        'why_it_works' => pickMany($whyItWorks, 3),
        'ethics' => 'Use optional reminders, no deceptive dark patterns, and clear spending limits to keep engagement healthy.'
    ];
}

$genre = $_POST['genre'] ?? 'any';
$platform = $_POST['platform'] ?? 'cross-platform';
$complexity = $_POST['complexity'] ?? 'balanced';
$ideaCount = max(1, min(5, (int)($_POST['idea_count'] ?? 1)));
$ideas = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($i = 0; $i < $ideaCount; $i++) {
        $ideas[] = generateGameIdea($genre, $platform, $complexity);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Idea Generator</title>
    <style>
        :root {
            --bg: #0f1226;
            --card: #1a1f3a;
            --ink: #edf0ff;
            --muted: #b3bbdf;
            --accent: #6ee7ff;
            --accent-2: #8b5cf6;
            --line: #2e3561;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            color: var(--ink);
            background: radial-gradient(circle at top right, #222859, var(--bg));
        }
        .wrap {
            max-width: 1000px;
            margin: 0 auto;
            padding: 32px 20px 60px;
        }
        .hero {
            margin-bottom: 20px;
        }
        .hero h1 {
            margin: 0 0 6px;
            font-size: 2rem;
        }
        .hero p {
            margin: 0;
            color: var(--muted);
        }
        .panel {
            background: rgba(26, 31, 58, 0.92);
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 18px;
            margin-bottom: 18px;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 12px;
        }
        label {
            display: block;
            font-size: 0.9rem;
            margin-bottom: 6px;
            color: var(--muted);
        }
        select, input {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #10152f;
            color: var(--ink);
        }
        .actions {
            margin-top: 14px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        button {
            border: none;
            border-radius: 10px;
            padding: 10px 16px;
            font-weight: 700;
            cursor: pointer;
        }
        .primary {
            background: linear-gradient(120deg, var(--accent-2), var(--accent));
            color: #0c1021;
        }
        .ghost {
            background: transparent;
            color: var(--accent);
            border: 1px solid var(--accent);
        }
        .ghost-link {
            border-radius: 10px;
            padding: 9px 16px;
            font-weight: 700;
            color: var(--accent);
            border: 1px solid var(--accent);
            text-decoration: none;
            display: inline-block;
        }
        .idea-card {
            margin-top: 14px;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 16px;
            background: #111633;
        }
        h2, h3, h4 {
            margin-top: 0;
        }
        .meta-line {
            color: var(--muted);
            margin: 0 0 10px;
        }
        ul {
            margin: 8px 0 14px 18px;
            padding: 0;
        }
        .ethics {
            border-left: 3px solid #3ddc97;
            padding-left: 10px;
            color: #c8f7e2;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <section class="hero">
            <h1>Game Idea Generator</h1>
            <p>Generate game concepts inspired by modern engagement loops: fast feedback, progression, social pull, and replayability.</p>
        </section>

        <section class="panel">
            <form method="post" action="">
                <div class="grid">
                    <div>
                        <label for="genre">Genre focus</label>
                        <select id="genre" name="genre">
                            <option value="any" <?php echo $genre === 'any' ? 'selected' : ''; ?>>Any</option>
                            <option value="roguelite" <?php echo $genre === 'roguelite' ? 'selected' : ''; ?>>Roguelite</option>
                            <option value="idle" <?php echo $genre === 'idle' ? 'selected' : ''; ?>>Idle</option>
                            <option value="social" <?php echo $genre === 'social' ? 'selected' : ''; ?>>Social Party/Co-op</option>
                            <option value="strategy" <?php echo $genre === 'strategy' ? 'selected' : ''; ?>>Strategy</option>
                            <option value="simulation" <?php echo $genre === 'simulation' ? 'selected' : ''; ?>>Simulation</option>
                            <option value="action" <?php echo $genre === 'action' ? 'selected' : ''; ?>>Action</option>
                            <option value="puzzle" <?php echo $genre === 'puzzle' ? 'selected' : ''; ?>>Puzzle</option>
                        </select>
                    </div>
                    <div>
                        <label for="platform">Platform</label>
                        <select id="platform" name="platform">
                            <option value="cross-platform" <?php echo $platform === 'cross-platform' ? 'selected' : ''; ?>>Cross-platform</option>
                            <option value="mobile" <?php echo $platform === 'mobile' ? 'selected' : ''; ?>>Mobile</option>
                            <option value="pc" <?php echo $platform === 'pc' ? 'selected' : ''; ?>>PC</option>
                            <option value="console" <?php echo $platform === 'console' ? 'selected' : ''; ?>>Console</option>
                        </select>
                    </div>
                    <div>
                        <label for="complexity">Design depth</label>
                        <select id="complexity" name="complexity">
                            <option value="simple" <?php echo $complexity === 'simple' ? 'selected' : ''; ?>>Simple</option>
                            <option value="balanced" <?php echo $complexity === 'balanced' ? 'selected' : ''; ?>>Balanced</option>
                            <option value="deep" <?php echo $complexity === 'deep' ? 'selected' : ''; ?>>Deep</option>
                        </select>
                    </div>
                    <div>
                        <label for="idea_count">Ideas to generate</label>
                        <input id="idea_count" name="idea_count" type="number" value="<?php echo $ideaCount; ?>" min="1" max="5">
                    </div>
                </div>
                <div class="actions">
                    <button class="primary" type="submit">Generate Ideas</button>
                    <a class="ghost-link" href="game-idea-generator.php">Reset</a>
                </div>
            </form>
        </section>

        <?php if (!empty($ideas)): ?>
            <?php foreach ($ideas as $idea): ?>
                <article class="idea-card">
                    <h3><?php echo htmlspecialchars($idea['title']); ?></h3>
                    <p class="meta-line"><?php echo htmlspecialchars($idea['genre']); ?> | <?php echo htmlspecialchars($idea['platform']); ?></p>

                    <h4>High-Concept Pitch</h4>
                    <p><?php echo htmlspecialchars($idea['pitch']); ?></p>

                    <h4>Addictive Hooks</h4>
                    <ul>
                        <?php foreach ($idea['hooks'] as $hook): ?>
                            <li><?php echo htmlspecialchars($hook); ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <h4>Meta Progression</h4>
                    <ul>
                        <?php foreach ($idea['meta'] as $item): ?>
                            <li><?php echo htmlspecialchars($item); ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <h4>Social Engine</h4>
                    <ul>
                        <?php foreach ($idea['social'] as $item): ?>
                            <li><?php echo htmlspecialchars($item); ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <h4>Why This Sticks</h4>
                    <ul>
                        <?php foreach ($idea['why_it_works'] as $item): ?>
                            <li><?php echo htmlspecialchars($item); ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <p><strong>Platform tuning:</strong> <?php echo htmlspecialchars($idea['platform_note']); ?></p>
                    <p class="ethics"><strong>Healthy engagement guardrail:</strong> <?php echo htmlspecialchars($idea['ethics']); ?></p>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
