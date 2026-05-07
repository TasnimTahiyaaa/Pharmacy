<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/db.php';
$pageTitle = 'Home';
include 'includes/header.php';
?>

<!-- HERO -->
<section class="hero">
  <div class="container hero-inner">
    <div>
      <div class="hero-badge">
        <span></span>
        Trusted Since 2010
      </div>
      <h1>Your Health,<br><span class="gradient-text">Our Priority</span></h1>
      <p>Noor Pharmacy brings you authentic medicines, expert guidance, and reliable home delivery — all in one place.</p>
      <div class="hero-ctas">
        <a href="medicines.php" class="btn btn-primary btn-lg">Browse Medicines →</a>
        <a href="#about" class="btn btn-outline btn-lg">Learn More</a>
      </div>
      <div class="hero-checks">
        <span class="hero-check"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg> Authentic Medicines</span>
        <span class="hero-check"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg> Licensed Pharmacists</span>
        <span class="hero-check"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg> All Over Delivery</span>
      </div>
    </div>
    <div class="hero-visual">
      <div class="hero-rx"><span>NP</span></div>
      <h3 style="font-size:18px;font-weight:800;margin-bottom:4px">Noor Pharmacy</h3>
      <p style="font-size:13px;color:var(--muted)">Professional Healthcare Solutions</p>
      <div class="hero-stats-grid">
        <div class="hero-stat"><div class="value">10,000+</div><div class="label">Happy Patients</div></div>
        <div class="hero-stat"><div class="value">5,000+</div><div class="label">Medicines</div></div>
        <div class="hero-stat"><div class="value">14+</div><div class="label">Years Serving</div></div>
        <div class="hero-stat"><div class="value">4.9 ★</div><div class="label">Rating</div></div>
      </div>
      <div class="hero-floating">
        <div class="floating-badge"><span style="width:8px;height:8px;background:#22c55e;border-radius:50%;display:inline-block"></span> Open Now</div>
        <div class="floating-badge" style="background:var(--primary);color:#fff;border:none">4.9 ★ Rated</div>
      </div>
    </div>
  </div>
</section>

<!-- STATS -->
<section class="stats-bar">
  <div class="container">
    <div class="stats-bar-inner">
      <div class="stat-item"><div class="value">10,000+</div><div class="label">Happy Patients</div></div>
      <div class="stat-item"><div class="value">5,000+</div><div class="label">Medicines</div></div>
      <div class="stat-item"><div class="value">15+</div><div class="label">Years Serving</div></div>
      <div class="stat-item"><div class="value">4.9</div><div class="label">Customer Rating</div></div>
    </div>
  </div>
</section>

<!-- SERVICES -->
<section class="section" id="about">
  <div class="container">
    <div class="section-header">
      <span class="section-tag">What We Offer</span>
      <h2>Our Services</h2>
      <p>Comprehensive pharmacy services designed to make healthcare accessible and convenient for everyone.</p>
    </div>
    <div class="grid-4">
      <?php $services = [
        ['icon'=>'M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h11a2 2 0 012 2v3m-2 9a2 2 0 002 2h4a2 2 0 002-2v-6a2 2 0 00-2-2h-4a2 2 0 00-2 2v6z','title'=>'Home Delivery','desc'=>'Fast and reliable delivery to your doorstep.'],
        ['icon'=>'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z','title'=>'24/7 Emergency','desc'=>'Emergency medicines available round the clock for urgent needs.'],
        ['icon'=>'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z','title'=>'Authentic Products','desc'=>'All medicines sourced directly from certified manufacturers.'],
        ['icon'=>'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z','title'=>'Expert Consultation','desc'=>'Speak with our licensed pharmacists for professional advice.'],
      ]; foreach ($services as $s): ?>
      <div class="card card-hover service-card">
        <div class="service-icon"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="<?= $s['icon'] ?>"/></svg></div>
        <h3><?= $s['title'] ?></h3>
        <p><?= $s['desc'] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- FEATURED MEDICINES -->
<section class="section section-alt">
  <div class="container">
    <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:48px;flex-wrap:wrap;gap:16px">
      <div>
        <span class="section-tag">Popular Products</span>
        <h2 style="font-size:34px;font-weight:800;margin-top:8px">Featured Medicines</h2>
      </div>
      <a href="medicines.php" class="btn btn-outline">View All →</a>
    </div>
    <div class="grid-3">
      <?php $featured = [
        ['name'=>'Paracetamol','category'=>'Pain Relief','price'=>'৳ 12','badge'=>'Best Seller'],
        ['name'=>'Amoxicillin','category'=>'Antibiotics','price'=>'৳ 45','badge'=>'Prescription'],
        ['name'=>'Vitamin C','category'=>'Supplements','price'=>'৳ 30','badge'=>'Popular'],
        ['name'=>'Metformin','category'=>'Diabetes','price'=>'৳ 85','badge'=>'In Stock'],
        ['name'=>'Omeprazole','category'=>'Gastro','price'=>'৳ 55','badge'=>'In Stock'],
        ['name'=>'Cetirizine','category'=>'Allergy','price'=>'৳ 18','badge'=>'Popular'],
      ]; foreach ($featured as $m): ?>
      <div class="card card-hover medicine-card">
        <div class="med-icon"><svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M10.5 20H4a2 2 0 01-2-2V6a2 2 0 012-2h3.9a2 2 0 011.69.9l.81 1.2a2 2 0 001.67.9H20a2 2 0 012 2v2"/><circle cx="18" cy="18" r="3"/><path d="M18 15v3M18 21v-.5M15 18h3M21 18h-.5"/></svg></div>
        <div style="flex:1;min-width:0">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
            <h3 style="font-size:14px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= $m['name'] ?></h3>
            <span class="med-badge"><?= $m['badge'] ?></span>
          </div>
          <p style="font-size:12px;color:var(--muted);margin-bottom:8px"><?= $m['category'] ?></p>
          <div style="display:flex;align-items:center;justify-content:space-between">
            <span class="med-price"><?= $m['price'] ?></span>
            <a href="medicines.php" class="btn btn-outline btn-sm">View</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- HEALTH TIPS -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <span class="section-tag">Wellness</span>
      <h2>Health Tips</h2>
    </div>
    <div class="grid-3">
      <?php $tips = [
        ['icon'=>'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z','title'=>'Take Medicines on Time','desc'=>'Consistency is key — set alarms to never miss a dose.'],
        ['icon'=>'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z','title'=>'Store Properly','desc'=>'Keep medicines away from heat, light, and moisture.'],
        ['icon'=>'M10.5 20H4a2 2 0 01-2-2V6a2 2 0 012-2h3.9a2 2 0 011.69.9l.81 1.2a2 2 0 001.67.9H20a2 2 0 012 2v2','title'=>'Check Expiry Dates','desc'=>'Always verify expiry before consuming any medicine.'],
      ]; foreach ($tips as $t): ?>
      <div class="tip-card">
        <div class="tip-icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="<?= $t['icon'] ?>"/></svg></div>
        <div><h3><?= $t['title'] ?></h3><p><?= $t['desc'] ?></p></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- QUOTES -->
<section class="section" style="background:rgba(26,158,114,0.04)">
  <div class="container">
    <div class="section-header"><span class="section-tag">Inspiration</span><h2>Medical Wisdom</h2></div>
    <div class="grid-3">
      <?php $quotes = [
        ['text'=>'The dose makes the poison.','author'=>'Paracelsus'],
        ['text'=>'Take care of your body. It\'s the only place you have to live.','author'=>'Jim Rohn'],
        ['text'=>'Health is not valued till sickness comes.','author'=>'Thomas Fuller'],
      ]; foreach ($quotes as $q): ?>
      <div class="card quote-card card-hover">
        <div class="quote-mark">"</div>
        <p><?= htmlspecialchars($q['text']) ?></p>
        <div class="quote-author">— <?= $q['author'] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- TESTIMONIALS -->
<section class="section">
  <div class="container">
    <div class="section-header"><span class="section-tag">Reviews</span><h2>What Patients Say</h2></div>
    <div class="grid-3">
      <?php $reviews = [
        ['name'=>'Rahima Begum','text'=>'Excellent service and genuine medicines. The delivery was fast and the staff is very helpful.'],
        ['name'=>'Mohammad Ali','text'=>'I rely on Noor Pharmacy for all my monthly medicines. Always in stock and affordable prices.'],
        ['name'=>'Fatema Akter','text'=>'The online ordering system is so convenient. I no longer have to step out when I\'m unwell.'],
      ]; foreach ($reviews as $r): ?>
      <div class="card card-hover" style="padding:24px">
        <div class="review-stars">
          <?php for($i=0;$i<5;$i++): ?><svg width="16" height="16" viewBox="0 0 24 24"><path fill="#f59e0b" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg><?php endfor; ?>
        </div>
        <p style="font-size:14px;color:var(--muted);line-height:1.6;margin-bottom:12px">"<?= htmlspecialchars($r['text']) ?>"</p>
        <div class="reviewer">
          <div class="reviewer-avatar"><?= $r['name'][0] ?></div>
          <div><p><?= $r['name'] ?></p><small>Verified Customer</small></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CONTACT -->
<section class="section section-alt" id="contact">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:40px;align-items:start">
      <div>
        <span class="section-tag">Get in Touch</span>
        <h2 style="font-size:28px;font-weight:800;margin:8px 0 24px">Visit or Contact Us</h2>
        <div style="display:flex;flex-direction:column;gap:12px">
          <?php $contacts = [
            ['icon'=>'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z','label'=>'Address','val'=>' Tistar Gate, Tongi, Gazipur'],
            ['icon'=>'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z','label'=>'Phone','val'=>'+8801934331367'],
            ['icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z','label'=>'Hours','val'=>'Sat–Fri: 8AM–11PM'],
          ]; foreach ($contacts as $c): ?>
          <div class="contact-card">
            <div class="contact-icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="<?= $c['icon'] ?>"/></svg></div>
            <div><small><?= $c['label'] ?></small><p><?= htmlspecialchars($c['val']) ?></p></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-section">
  <div class="container">
    <h2>Ready to Order Your Medicines?</h2>
    <p>Browse our catalogue and get your medicines delivered to your door.</p>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
      <a href="medicines.php" class="btn btn-primary btn-lg">Shop Now →</a>
      <a href="register.php" class="btn btn-outline btn-lg">Create Account</a>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
