<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($systemSettings['website_name'] ?? 'Family Tree System'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="page-family-tree">
<div class="wrapper">
    <?php echo view('all.navbar'); ?>

    <section class="stats">
        <article class="stat-card">
            <small>Total Members</small>
            <h2>18</h2>
        </article>
        <article class="stat-card">
            <small>Generations</small>
            <h2>4</h2>
        </article>
        <article class="stat-card">
            <small>Core Families</small>
            <h2>5</h2>
        </article>
        <article class="stat-card">
            <small>Last Updated</small>
            <h2 class="last-update">06 Apr 2026</h2>
        </article>
    </section>

    <section class="panel">
        <div class="tree-container">
            <div class="tree-head">
                <div>
                    <h3>Family Tree Structure</h3>
                    <p>Click a family member to view quick details.</p>
                </div>
                <div class="legend"><span class="dot"></span> Active relationship lines</div>
            </div>

            <div class="tree">
                <ul>
                    <li>
                        <article class="member-card active" data-name="Rahmat Pratama" data-role="Grandfather" data-age="72" data-status="Active" data-generation="Generation 1" data-photo="https://i.pravatar.cc/120?img=12">
                            <img class="member-photo" src="https://i.pravatar.cc/120?img=12" alt="Rahmat">
                            <h4 class="member-name">Rahmat Pratama</h4>
                            <p class="member-role">Grandfather</p>
                            <span class="member-tag">Generation 1</span>
                        </article>
                        <ul>
                            <li>
                                <article class="member-card" data-name="Andi Rahmat" data-role="Father" data-age="45" data-status="Active" data-generation="Generation 2" data-photo="https://i.pravatar.cc/120?img=15">
                                    <img class="member-photo" src="https://i.pravatar.cc/120?img=15" alt="Andi">
                                    <h4 class="member-name">Andi Rahmat</h4>
                                    <p class="member-role">Father</p>
                                    <span class="member-tag">Generation 2</span>
                                </article>
                                <ul>
                                    <li>
                                        <article class="member-card" data-name="Lina Andini" data-role="First Child" data-age="20" data-status="College Student" data-generation="Generation 3" data-photo="https://i.pravatar.cc/120?img=31">
                                            <img class="member-photo" src="https://i.pravatar.cc/120?img=31" alt="Lina">
                                            <h4 class="member-name">Lina Andini</h4>
                                            <p class="member-role">First Child</p>
                                            <span class="member-tag">Generation 3</span>
                                        </article>
                                    </li>
                                    <li>
                                        <article class="member-card" data-name="Raka Andi" data-role="Second Child" data-age="16" data-status="Student" data-generation="Generation 3" data-photo="https://i.pravatar.cc/120?img=33">
                                            <img class="member-photo" src="https://i.pravatar.cc/120?img=33" alt="Raka">
                                            <h4 class="member-name">Raka Andi</h4>
                                            <p class="member-role">Second Child</p>
                                            <span class="member-tag">Generation 3</span>
                                        </article>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <article class="member-card" data-name="Maya Rahmat" data-role="Aunt" data-age="40" data-status="Active" data-generation="Generation 2" data-photo="https://i.pravatar.cc/120?img=45">
                                    <img class="member-photo" src="https://i.pravatar.cc/120?img=45" alt="Maya">
                                    <h4 class="member-name">Maya Rahmat</h4>
                                    <p class="member-role">Aunt</p>
                                    <span class="member-tag">Generation 2</span>
                                </article>
                                <ul>
                                    <li>
                                        <article class="member-card" data-name="Nadia Putri" data-role="Cousin" data-age="18" data-status="Student" data-generation="Generation 3" data-photo="https://i.pravatar.cc/120?img=47">
                                            <img class="member-photo" src="https://i.pravatar.cc/120?img=47" alt="Nadia">
                                            <h4 class="member-name">Nadia Putri</h4>
                                            <p class="member-role">Cousin</p>
                                            <span class="member-tag">Generation 3</span>
                                        </article>
                                    </li>
                                    <li>
                                        <article class="member-card" data-name="Fikri Hadi" data-role="Cousin" data-age="13" data-status="Student" data-generation="Generation 3" data-photo="https://i.pravatar.cc/120?img=51">
                                            <img class="member-photo" src="https://i.pravatar.cc/120?img=51" alt="Fikri">
                                            <h4 class="member-name">Fikri Hadi</h4>
                                            <p class="member-role">Cousin</p>
                                            <span class="member-tag">Generation 3</span>
                                        </article>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>

        <aside class="detail">
            <h4>Member Details</h4>
            <div class="detail-card">
                <img id="detailPhoto" class="detail-photo" src="https://i.pravatar.cc/120?img=12" alt="Detail">
                <h5 id="detailName" class="detail-name">Rahmat Pratama</h5>
                <p id="detailRole" class="detail-role">Grandfather</p>
                <ul class="detail-list">
                    <li><span>Age</span><strong id="detailAge">72</strong></li>
                    <li><span>Status</span><strong id="detailStatus">Active</strong></li>
                    <li><span>Generation</span><strong id="detailGeneration">Generation 1</strong></li>
                </ul>
            </div>
        </aside>
    </section>
</div>
<script src="/js/script.js"></script>
</body>
</html>
