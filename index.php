<?php
require_once __DIR__ . '/includes/catalog.php';

$home_page  = true;
$page_title = 'Fandom Universe';
$homeCategories = db_all("
    SELECT 
        cat.*,
        (
            SELECT thumbnail_url 
            FROM content c 
            WHERE c.category_id = cat.category_id 
              AND c.visibility = 'published' 
              AND c.thumbnail_url IS NOT NULL 
            ORDER BY c.view_count DESC 
            LIMIT 1
        ) AS poster,
        (
            SELECT COUNT(*) 
            FROM content c 
            WHERE c.category_id = cat.category_id 
              AND c.visibility = 'published'
        ) AS total 
    FROM categories cat 
    WHERE cat.is_active = 1 
    ORDER BY sort_order
");
$heroItems = db_all("
    SELECT * 
    FROM carousel 
    WHERE status = 'active' 
    ORDER BY sort_order ASC, carousel_id DESC 
    LIMIT 9
");
if (empty($heroItems)) {
    $heroItems = db_all("
        SELECT 
            title, 
            description, 
            thumbnail_url AS image_url, 
            'Explore content' AS button_text, 
            CONCAT('content.php?id=', content_id) AS button_url 
        FROM content 
        WHERE visibility = 'published' 
        ORDER BY featured DESC, content_id DESC 
        LIMIT 5
    ");
}
$trending = db_all("
    SELECT c.*, cat.name AS category 
    FROM content c 
    JOIN categories cat ON cat.category_id = c.category_id 
    WHERE c.visibility = 'published' AND cat.is_active = 1 
    ORDER BY c.view_count DESC, c.content_id DESC 
    LIMIT 12
");

$latest = db_all("
    SELECT c.*, cat.name AS category 
    FROM content c 
    JOIN categories cat ON cat.category_id = c.category_id 
    WHERE c.visibility = 'published' AND cat.is_active = 1 
    ORDER BY c.content_id DESC 
    LIMIT 12
");
$articles = db_all("
    SELECT * 
    FROM articles 
    WHERE status = 'published' 
    ORDER BY published_at DESC 
    LIMIT 3
");
require __DIR__ . '/includes/header.php';
?>

<style>
.carousel {
  position: relative;
  width: 100%;
  height: 100vh;
  min-height: 600px;
  max-height: 900px;
  overflow: hidden;
  background: #050508;
}

.carousel .list .item {
  position: absolute;
  width: 180px;
  height: 250px;
  top: 80%;
  left: 70%;
  transform: translateY(-70%);
  background-position: 50% 50%;
  background-size: cover;
  border: 1px solid rgba(79, 157, 255, 0.3);
  border-radius: 20px;
  box-shadow:
    0 25px 50px rgba(0, 0, 0, 0.7),
    0 0 30px rgba(79, 157, 255, 0.15);
  z-index: 100;
  transition: 0.85s cubic-bezier(0.4, 0, 0.2, 1);
  will-change: transform, top, left, width, height;
}

.carousel .list .item:nth-child(1),
.carousel .list .item:nth-child(2) {
  top: 0;
  left: 0;
  transform: translate(0, 0);
  width: 100%;
  height: 100%;
  border: 0;
  border-radius: 0;
}

.carousel .list .item:nth-child(3) { left: 67%; }
.carousel .list .item:nth-child(4) { left: calc(67% + 200px); }
.carousel .list .item:nth-child(5) { left: calc(67% + 400px); }
.carousel .list .item:nth-child(6) { left: calc(67% + 600px); }
.carousel .list .item:nth-child(n + 7) {
  left: calc(67% + 800px);
  opacity: 0;
}

.carousel .list .item:nth-child(2)::before {
  content: "";
  position: absolute;
  inset: 0;
  background: linear-gradient(
    90deg,
    rgba(5, 5, 8, 0.95) 0%,
    rgba(5, 5, 8, 0.85) 35%,
    rgba(20, 10, 50, 0.5) 65%,
    rgba(79, 157, 255, 0.15) 100%
  );
  z-index: 1;
}

.list .item .content {
  position: absolute;
  top: 50%;
  left: 6%;
  transform: translateY(-50%);
  width: 560px;
  max-width: 90%;
  color: #fff;
  text-align: left;
  display: none;
  z-index: 2;
}

.list .item:nth-child(2) .content {
  display: block;
}

.content .badge-neon {
  display: inline-block;
  padding: 6px 16px;
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 3px;
  color: #22d3ee;
  background: rgba(34, 211, 238, 0.1);
  border: 1px solid rgba(34, 211, 238, 0.4);
  border-radius: 20px;
  margin-bottom: 18px;
  box-shadow: 0 0 20px rgba(34, 211, 238, 0.3);
}

.content .title-gradient {
  font-size: clamp(2rem, 5vw, 4rem);
  font-weight: 800;
  line-height: 1.05;
  letter-spacing: -1.5px;
  margin-bottom: 20px;
  opacity: 0;
  animation: animate 0.9s ease-out 0.5s 1 forwards;
}

.content .des {
  font-size: clamp(13px, 1.3vw, 16px);
  line-height: 1.6;
  color: #c8cbe0;
  max-width: 520px;
  margin-bottom: 26px;
  opacity: 0;
  animation: animate 0.9s ease-out 0.75s 1 forwards;
}

.content .btn {
  opacity: 0;
  animation: animate 0.9s ease-out 1s 1 forwards;
}

.content .btn-neon-primary {
  display: inline-block;
  padding: 13px 30px;
  font-size: 14px;
  font-weight: 700;
  color: #fff;
  background: linear-gradient(135deg, #4f9dff 0%, #a855f7 100%);
  border-radius: 10px;
  box-shadow: 0 4px 25px rgba(79, 157, 255, 0.5);
  transition: all 0.3s ease;
}

.content .btn-neon-primary:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 35px rgba(168, 85, 247, 0.65);
  filter: brightness(1.1);
}

@keyframes animate {
  from {
    opacity: 0;
    transform: translate(0, 60px);
    filter: blur(20px);
  }
  to {
    opacity: 1;
    transform: translate(0);
    filter: blur(0);
  }
}
.arrows {
  position: absolute;
  bottom: 48px;
  left: 6%;
  z-index: 200;
  display: flex;
  gap: 12px;
}

.arrow-btn {
  display: grid;
  place-items: center;
  width: 48px;
  height: 48px;
  font-family: monospace;
  font-size: 18px;
  font-weight: bold;
  color: #fff;
  background: rgba(10, 10, 25, 0.75);
  border: 1px solid rgba(79, 157, 255, 0.4);
  border-radius: 50%;
  backdrop-filter: blur(6px);
  cursor: pointer;
  transition: all 0.25s ease;
}

.arrow-btn:hover {
  background: linear-gradient(135deg, #4f9dff 0%, #a855f7 100%);
  border-color: #a855f7;
  transform: scale(1.06);
  box-shadow: 0 0 25px rgba(168, 85, 247, 0.6);
}
.home-welcome {
  position: relative;
  min-height: 600px;
  display: flex;
  align-items: center;
  padding: 80px 7%;
  color: #fff;
  overflow: hidden;
  background:
    linear-gradient(90deg, rgba(5, 5, 8, 0.92) 0%, rgba(5, 5, 8, 0.5) 60%, rgba(5, 5, 8, 0.85) 100%),
    url('<?= e(base_url('Admin/bg.png')) ?>') center/cover no-repeat;
}

.home-welcome::before {
  content: "";
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 20% 40%, rgba(79, 157, 255, 0.2), transparent 55%),
    radial-gradient(circle at 80% 60%, rgba(168, 85, 247, 0.2), transparent 55%);
  pointer-events: none;
}

.home-welcome > div {
  position: relative;
  z-index: 2;
  max-width: 620px;
}

.home-welcome h1 {
  font-size: clamp(2.8rem, 6vw, 5.4rem);
  font-weight: 800;
  line-height: 1.05;
  letter-spacing: -2px;
  margin: 16px 0 20px;
}

.home-welcome p {
  font-size: 1.05rem;
  line-height: 1.7;
  color: #d5d9e9;
  max-width: 500px;
  margin-bottom: 28px;
}
.section-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 20px;
  flex-wrap: wrap;
  margin-bottom: 32px;
}

.section-header h2 {
  font-size: clamp(1.5rem, 2.5vw, 2rem);
  font-weight: 800;
  letter-spacing: -0.5px;
  margin: 0 0 6px;
}

.section-header p {
  font-size: 0.9rem;
  color: #c8cbe0;
  margin: 0;
}
.category-row {
  display: grid;
  grid-template-columns: repeat(8, 1fr);
  gap: 14px;
  margin: 40px 0 20px;
}

.category-pill {
  position: relative;
  display: grid;
  place-items: center;
  height: 80px;
  font-size: 0.78rem;
  font-weight: 600;
  color: #c8cbe0;
  background: linear-gradient(180deg, #0d0d1a, #0a0a14);
  border: 1px solid #232842;
  border-radius: 14px;
  text-decoration: none;
  overflow: hidden;
  transition: all 0.35s ease;
}

.category-pill::before {
  content: "";
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(79, 157, 255, 0.15), rgba(168, 85, 247, 0.15));
  opacity: 0;
  transition: opacity 0.35s ease;
}

.category-pill:hover {
  color: #fff;
  border-color: #4f9dff;
  transform: translateY(-4px);
  box-shadow:
    0 10px 30px rgba(79, 157, 255, 0.3),
    0 0 40px rgba(168, 85, 247, 0.2);
}

.category-pill:hover::before { opacity: 1; }
.home-main {
  max-width: 1440px;
  margin: 0 auto;
  padding: 0 4% 40px;
}

.home-main section {
  margin: 60px 0;
}

.home-scroller {
  display: flex;
  gap: 20px;
  padding: 6px 0 22px;
  overflow-x: auto;
  scroll-snap-type: x mandatory;
  scrollbar-width: thin;
  scrollbar-color: #232842 transparent;
}

.home-scroller::-webkit-scrollbar { height: 6px; }
.home-scroller::-webkit-scrollbar-track { background: transparent; }
.home-scroller::-webkit-scrollbar-thumb {
  background: #232842;
  border-radius: 3px;
}
.home-scroller::-webkit-scrollbar-thumb:hover { background: #4f9dff; }
.card {
  position: relative;
  flex: 0 0 215px;
  width: 215px;
  height: 300px;
  aspect-ratio: 0.78;
  background: #0a0a14;
  border: 1px solid #232842;
  border-radius: 14px;
  overflow: hidden;
  text-decoration: none;
  scroll-snap-align: start;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.card:hover {
  transform: translateY(-8px);
  border-color: #4f9dff;
  box-shadow:
    0 12px 40px rgba(79, 157, 255, 0.3),
    0 0 60px rgba(168, 85, 247, 0.2);
}

.card__media {
  position: absolute;
  inset: 0;
  overflow: hidden;
}

.card__media img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.6s ease;
}

.card:hover .card__media img { transform: scale(1.1); }

.card__overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    180deg,
    rgba(0, 0, 0, 0.2) 0%,
    rgba(20, 10, 40, 0.92) 100%
  );
  opacity: 0;
  transition: opacity 0.35s ease;
}

.card__info {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  padding: 14px;
  opacity: 0;
  transform: translateY(12px);
  transition: all 0.35s ease;
  z-index: 2;
}

.card__tag {
  display: inline-block;
  font-size: 9px;
  font-weight: 700;
  letter-spacing: 0.6px;
  text-transform: uppercase;
  color: #22d3ee;
  margin-bottom: 6px;
  text-shadow: 0 0 10px rgba(34, 211, 238, 0.5);
}

.card__title {
  display: block;
  font-size: 13px;
  font-weight: 600;
  line-height: 1.3;
  color: #fff;
  margin-bottom: 4px;
}

.card__meta {
  font-size: 10.5px;
  color: #c8cbe0;
}

.card:hover .card__overlay,
.card:hover .card__info {
  opacity: 1;
  transform: none;
}
.poster-card {
  position: relative;
  display: block;
  flex: 0 0 235px;
  width: 235px;
  aspect-ratio: 1.42;
  background: #0a0a14;
  border: 1px solid #232842;
  border-radius: 16px;
  overflow: hidden;
  text-decoration: none;
  scroll-snap-align: start;
  transition: all 0.4s ease;
}

.poster-card:hover {
  transform: translateY(-8px);
  border-color: #a855f7;
  box-shadow:
    0 12px 40px rgba(168, 85, 247, 0.35),
    0 0 60px rgba(79, 157, 255, 0.2);
}

.poster-card img {
  position: absolute;
  inset: 0 0 auto;
  width: 100%;
  height: 68%;
  object-fit: cover;
  transition: transform 0.6s ease;
}

.poster-card:hover img { transform: scale(1.05); }

.poster-card::after {
  content: "";
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(5, 5, 8, 0) 25%, #0a0812 78%);
  pointer-events: none;
}

.poster-card__text {
  position: absolute;
  left: 16px;
  right: 16px;
  bottom: 16px;
  z-index: 2;
}

.poster-card__text b {
  display: block;
  font-size: 1.05rem;
  font-weight: 800;
  color: #fff;
  margin-bottom: 4px;
}

.poster-card__text p {
  font-size: 0.75rem;
  color: #c8cbe0;
  margin-bottom: 6px;
}

.poster-card__text small {
  display: inline-block;
  font-size: 11px;
  font-weight: 600;
  color: #4f9dff;
}
.grid.three {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 22px;
}

.panel {
  padding: 26px;
  background: rgba(10, 10, 20, 0.7);
  border: 1px solid #232842;
  border-radius: 16px;
  backdrop-filter: blur(10px);
  transition: all 0.35s ease;
}

.panel:hover {
  transform: translateY(-6px);
  border-color: #4f9dff;
  box-shadow:
    0 12px 40px rgba(79, 157, 255, 0.25),
    0 0 60px rgba(168, 85, 247, 0.15);
}

.panel h3 {
  font-size: 1.15rem;
  font-weight: 700;
  margin: 14px 0 10px;
}

.panel h3 a {
  color: #f0f2ff;
  text-decoration: none;
  transition: color 0.2s ease;
}

.panel h3 a:hover { color: #4f9dff; }

.panel p {
  color: #c8cbe0;
  font-size: 0.9rem;
  line-height: 1.6;
  margin: 0;
}
.community-cta {
  position: relative;
  margin: 80px 0;
  padding: clamp(40px, 6vw, 72px);
  background:
    radial-gradient(circle at 20% 50%, rgba(79, 157, 255, 0.15), transparent 60%),
    radial-gradient(circle at 80% 50%, rgba(168, 85, 247, 0.15), transparent 60%),
    rgba(10, 10, 20, 0.8);
  border: 1px solid rgba(79, 157, 255, 0.3);
  border-radius: 24px;
  overflow: hidden;
  backdrop-filter: blur(10px);
}

.community-cta::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: linear-gradient(90deg,
    transparent,
    #22d3ee,
    #a855f7,
    #ec4899,
    transparent);
}

.community-cta h2 {
  font-size: clamp(1.6rem, 3vw, 2.5rem);
  font-weight: 800;
  margin: 0 0 12px;
  background: linear-gradient(135deg, #fff, #4f9dff);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
}

.community-cta p {
  font-size: 1rem;
  color: #c8cbe0;
  max-width: 500px;
  margin-bottom: 24px;
  line-height: 1.6;
}
.badge-neon {
  display: inline-block;
  padding: 5px 14px;
  font-size: 0.7rem;
  font-weight: 800;
  letter-spacing: 3px;
  color: #22d3ee;
  background: rgba(34, 211, 238, 0.1);
  border: 1px solid rgba(34, 211, 238, 0.4);
  border-radius: 20px;
  text-shadow: 0 0 12px rgba(34, 211, 238, 0.5);
}

.eyebrow-cyan {
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 4px;
  color: #22d3ee;
  text-transform: uppercase;
  text-shadow: 0 0 20px rgba(34, 211, 238, 0.6);
}

.title-gradient {
  background: linear-gradient(135deg, #ffffff 0%, #4f9dff 50%, #a855f7 100%);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
}

.text-soft { color: #c8cbe0; }

.link-cyan {
  color: #22d3ee;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.25s ease;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.link-cyan:hover {
  color: #ec4899;
  gap: 10px;
}

.pill-purple {
  display: inline-block;
  padding: 5px 14px;
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 1px;
  text-transform: uppercase;
  color: #a855f7;
  background: rgba(168, 85, 247, 0.12);
  border: 1px solid rgba(168, 85, 247, 0.4);
  border-radius: 20px;
  box-shadow: 0 0 15px rgba(168, 85, 247, 0.25);
}

.btn-neon-primary {
  display: inline-block;
  padding: 13px 30px;
  font-size: 0.95rem;
  font-weight: 700;
  color: #fff;
  background: linear-gradient(135deg, #4f9dff 0%, #a855f7 100%);
  border-radius: 12px;
  text-decoration: none;
  box-shadow:
    0 6px 25px rgba(79, 157, 255, 0.4),
    0 0 40px rgba(168, 85, 247, 0.2);
  transition: all 0.3s ease;
}

.btn-neon-primary:hover {
  transform: translateY(-3px);
  box-shadow:
    0 10px 40px rgba(79, 157, 255, 0.6),
    0 0 60px rgba(168, 85, 247, 0.4);
}

.empty-state {
  padding: 60px 30px;
  text-align: center;
  color: #8b90b0;
  background: rgba(10, 10, 20, 0.5);
  border: 1px dashed #232842;
  border-radius: 16px;
  width: 100%;
}
@media (max-width: 1100px) {
  .category-row { grid-template-columns: repeat(4, 1fr); }
  .grid.three { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

@media (max-width: 820px) {
  .carousel { height: 88vh; min-height: 560px; }
  .list .item .content { left: 6%; right: 6%; width: auto; }
  .arrows { bottom: 32px; left: 6%; }
  .home-welcome { padding: 60px 6%; min-height: 500px; }
  .grid.three { grid-template-columns: 1fr; }
}

@media (max-width: 600px) {
  .carousel { height: 500px; }
  .list .item .content { top: 40%; left: 7%; width: 86%; }
  .arrows { bottom: 30px; left: 7%; }
  .arrow-btn { width: 42px; height: 42px; font-size: 16px; }
  .category-row { grid-template-columns: repeat(2, 1fr); gap: 10px; }
  .category-pill { height: 65px; font-size: 0.72rem; }
  .card { flex: 0 0 170px; width: 170px; height: 245px; }
  .poster-card { flex: 0 0 190px; width: 190px; }
  .home-welcome h1 { font-size: 2.2rem; }
}

@media (prefers-reduced-motion: reduce) {
  * {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
  }
}
</style>
<?php if (!empty($heroItems)): ?>
    <section class="carousel hero-carousel" id="heroCarousel" aria-label="Featured content">
        <div class="list" id="carouselList">
            <?php foreach ($heroItems as $index => $slide): ?>
                <div class="item" style="background-image: url('<?= e(image_url($slide['image_url'])) ?>');">
                    <div class="content">
                        <span class="badge-neon">FEATURED</span>
                        <h1 class="title-gradient"><?= e($slide['title']) ?></h1>
                        <p class="des"><?= e(excerpt($slide['description'], 230)) ?></p>
                        <div class="btn">
                            <a class="btn-neon-primary" href="<?= e(safe_url($slide['button_url'], base_url('search.php'))) ?>">
                                <?= e($slide['button_text'] ?: 'Explore now') ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="arrows">
            <button id="prevBtn" class="arrow-btn" aria-label="Previous slide">&lt;</button>
            <button id="nextBtn" class="arrow-btn" aria-label="Next slide">&gt;</button>
        </div>
    </section>
<?php else: ?>
    <section class="home-welcome" data-aos="zoom-in">
        <div>
            <p class="eyebrow-cyan">FANDOM UNIVERSE</p>
            <h1 class="title-gradient">Find your world.<br>Make it yours.</h1>
            <p class="text-soft">Explore stories, characters and communities across eight fandoms.</p>
            <a class="btn-neon-primary" href="<?= e(base_url('search.php')) ?>">
                Explore content
            </a>
        </div>
    </section>
<?php endif; ?>
<main id="main" class="home-main">
    <nav class="category-row" aria-label="Fandom categories" data-aos="fade-up">
        <?php foreach ($homeCategories as $cat): ?>
            <a href="<?= e(category_url($cat['slug'])) ?>" class="category-pill">
                <?= e($cat['name']) ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <?php
    $sections = [
        ['Trending now',        $trending, 'popular', 'Popular discoveries based on recorded views.'],
        ['Latest discoveries',  $latest,   'latest',  'Recently published by the Fan Hub Plus community.'],
    ];

    foreach ($sections as [$title, $items, $sort, $subtitle]):
    ?>
        <section data-aos="fade-up" data-aos-duration="800">
            <div class="section-header">
                <div>
                    <h2 class="title-gradient"><?= e($title) ?></h2>
                    <p class="text-soft"><?= e($subtitle) ?></p>
                </div>
                <a href="<?= e(base_url('search.php?sort=' . $sort)) ?>" class="link-cyan">
                    View all &rarr;
                </a>
            </div>

            <div class="home-scroller">
                <?php foreach ($items as $delayIndex => $item): ?>
                    <a class="card"
                       href="<?= e(base_url('content.php?id=' . $item['content_id'])) ?>"
                       data-aos="fade-up"
                       data-aos-delay="<?= ($delayIndex % 4) * 100 ?>">
                        <div class="card__media">
                            <img src="<?= e(image_url($item['thumbnail_url'])) ?>"
                                 alt="<?= e($item['title']) ?>"
                                 loading="lazy">
                            <div class="card__overlay"></div>
                        </div>
                        <div class="card__info">
                            <span class="card__tag"><?= e($item['category']) ?></span>
                            <b class="card__title"><?= e($item['title']) ?></b>
                            <small class="card__meta"><?= number_format((int)$item['view_count']) ?> views</small>
                        </div>
                    </a>
                <?php endforeach; ?>

                <?php if (empty($items)): ?>
                    <div class="empty-state">
                        Published content will appear here. Explore the categories or read our getting-started guide below.
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php endforeach; ?>
    <section data-aos="fade-up">
        <div class="section-header">
            <div>
                <h2 class="title-gradient">Explore by fandom</h2>
                <p class="text-soft">Find your world, your story, your fandom.</p>
            </div>
        </div>

        <div class="home-scroller">
            <?php foreach ($homeCategories as $catIndex => $cat): ?>
                <a class="poster-card"
                   href="<?= e(category_url($cat['slug'])) ?>"
                   data-aos="zoom-in"
                   data-aos-delay="<?= ($catIndex % 4) * 100 ?>">
                    <img src="<?= e(image_url($cat['poster'] ?: $cat['image_url'])) ?>"
                         alt="<?= e($cat['name']) ?>"
                         loading="lazy">
                    <div class="poster-card__text">
                        <b><?= e($cat['name']) ?></b>
                        <p><?= (int)$cat['total'] ?> published items</p>
                        <small class="link-cyan">Explore now</small>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <section data-aos="fade-up">
        <div class="section-header">
            <div>
                <h2 class="title-gradient">Featured articles</h2>
                <p class="text-soft">Stories and guides from across the community.</p>
            </div>
            <a href="<?= e(base_url('articles.php')) ?>" class="link-cyan">
                All articles &rarr;
            </a>
        </div>

        <div class="grid three">
            <?php foreach ($articles as $artIndex => $article): ?>
                <article class="panel"
                         data-aos="fade-up"
                         data-aos-delay="<?= $artIndex * 150 ?>">
                    <span class="pill-purple">Community reading</span>
                    <h3>
                        <a href="<?= e(base_url('article.php?id=' . $article['article_id'])) ?>">
                            <?= e($article['title']) ?>
                        </a>
                    </h3>
                    <p><?= e($article['excerpt']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="community-cta" data-aos="zoom-in-up">
        <div>
            <h2>Your fandom, all in one place.</h2>
            <p>Save your discoveries, choose your favorite categories and share your original work.</p>
            <a href="<?= e(base_url(current_user() ? 'User/dashboard.php' : 'User/register.php')) ?>"
               class="btn-neon-primary">
                <?= current_user() ? 'My dashboard' : 'Join the community' ?>
            </a>
        </div>
    </section>
</main>
<script defer src="<?= e(base_url('assets/home.js')) ?>"></script>

<?php require __DIR__ . '/includes/footer.php'; ?>